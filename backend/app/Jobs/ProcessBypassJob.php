<?php

namespace App\Jobs;

use App\Models\BypassRule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessBypassJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $bypassRule;
    protected $keystorePath;

    public function __construct(BypassRule $rule, $keystorePath = null)
    {
        $this->bypassRule = $rule;
        // Keystore configurável: parâmetro > env > padrão (Fase 2, item 5)
        $this->keystorePath = $keystorePath
            ?? env('KEYSTORE_PATH', '/app/keystore/thevault.keystore');
    }

    protected function apktoolJar(): string
    {
        return env('APKTOOL_JAR', '/usr/local/bin/apktool.jar');
    }

    protected function buildToolsDir(): string
    {
        $version = env('BUILD_TOOLS_VERSION', '34.0.0');
        $root = env('ANDROID_SDK_ROOT', '/opt/android-sdk');
        return rtrim($root, '/') . '/build-tools/' . $version;
    }

    public function handle(): void
    {
        $rule = $this->bypassRule->fresh() ?? $this->bypassRule;
        $rule->update(['status' => 'processing']);

        // Resolve APK original: relação apk (file_path) > apk_path salvo na regra
        $apk = $rule->apk;
        $relativeApk = $apk?->file_path ?: $rule->apk_path;
        $apkFullPath = $relativeApk ? storage_path('app/public/' . ltrim($relativeApk, '/')) : null;

        if (!$relativeApk || !$apkFullPath || !is_file($apkFullPath)) {
            $rule->update([
                'status' => 'failed',
                'log' => ($rule->log ?? '') . $this->stamp('APK original não encontrado: ' . ($apkFullPath ?? 'null')),
                'phase' => 'decompile',
                'progress' => 0,
            ]);
            return;
        }

        $apkBase = basename($relativeApk);
        $targetPackage = $rule->target_package;
        $decompiledDir = storage_path('app/tmp/decompiled_' . $rule->id . '_' . uniqid());
        $unsignedApk = $decompiledDir . '/dist_unsigned.apk';
        $alignedApk = $decompiledDir . '/dist_aligned.apk';

        try {
            // ETAPA 0: Registrar assinatura ORIGINAL (auditoria).
            // Recompilar quebra o selo original: o APK final sai re-assinado
            // com o keystore Vault. Play Protect trata signer desconhecido
            // como risco e pode bloquear a instalação — comportamento esperado
            // e documentado, nunca "silenciado".
            $this->updateProgress($rule, 'decompile', 5, 'Registrando assinatura original do APK...');
            $this->recordOriginalCerts($rule, $apkFullPath);

            // ETAPA 1: Descompilar (apktool d -f)
            $this->updateProgress($rule, 'decompile', 10, 'Iniciando descompilação do APK...');
            if (!is_dir($decompiledDir)) {
                mkdir($decompiledDir, 0755, true);
            }

            $decodeOut = $this->run([
                'java', '-jar', $this->apktoolJar(),
                'd', '-f', $apkFullPath, '-o', $decompiledDir,
            ]);
            $this->log($rule, 'Resultado descompilação: ' . mb_substr($decodeOut, 0, 800));

            if (!is_file($decompiledDir . '/AndroidManifest.xml')) {
                throw new \Exception('Falha ao descompilar APK — AndroidManifest.xml não encontrado. Saída: ' . mb_substr($decodeOut, 0, 300));
            }

            // ETAPA 2: Patch real (1 mudança auditável)
            $this->updateProgress($rule, 'patch', 30, "Aplicando patch para {$targetPackage}...");
            $patchDesc = $this->applyPatches($decompiledDir, $targetPackage);
            $rule->update(['patch_applied' => $patchDesc]);
            $this->log($rule, 'Patch aplicado: ' . $patchDesc);

            // ETAPA 3: Recompilar (apktool b -o <arquivo>)
            $this->updateProgress($rule, 'recompile', 55, 'Recompilando APK...');
            $buildOut = $this->run([
                'java', '-jar', $this->apktoolJar(),
                'b', $decompiledDir, '-o', $unsignedApk,
            ]);
            $this->log($rule, 'Resultado recompilação: ' . mb_substr($buildOut, 0, 800));

            if (!is_file($unsignedApk)) {
                // Fallback: procurar qualquer APK gerado
                $found = glob($decompiledDir . '/dist/*.apk') ?: [];
                if (empty($found)) {
                    throw new \Exception('Falha ao recompilar APK — arquivo de saída não gerado.');
                }
                $unsignedApk = $found[0];
                $this->log($rule, 'APK recompilado encontrado em: ' . $unsignedApk);
            }

            // ETAPA 4: Alinhar (zipalign -f 4)
            $this->updateProgress($rule, 'align', 70, 'Alinhando APK com zipalign...');
            $zipalign = $this->buildToolsDir() . '/zipalign';
            if (!is_file($zipalign)) {
                throw new \Exception('zipalign não encontrado em: ' . $zipalign . ' (verifique BUILD_TOOLS_VERSION)');
            }
            $alignOut = $this->run([$zipalign, '-f', '4', $unsignedApk, $alignedApk]);
            $this->log($rule, 'Resultado zipalign: ' . mb_substr($alignOut, 0, 500));
            if (!is_file($alignedApk)) {
                throw new \Exception('Falha no zipalign — saída não gerada.');
            }

            // ETAPA 5: Assinar (apksigner, keystore configurável)
            $this->updateProgress($rule, 'sign', 85, 'Assinando APK com apksigner...');
            $keystore = $this->keystorePath;
            if (!is_file($keystore)) {
                throw new \Exception('Keystore não encontrado em: ' . $keystore . ' (configure KEYSTORE_PATH)');
            }
            $alias = env('KEYSTORE_ALIAS', 'thevault');
            $ksPass = env('KEYSTORE_PASS', 'vault2024');
            $keyPass = env('KEY_PASS', 'vault2024');
            $apksigner = $this->buildToolsDir() . '/apksigner';

            $signedTmp = $decompiledDir . '/dist_signed.apk';
            $signOut = $this->run([
                $apksigner, 'sign',
                '--out', $signedTmp,
                '--ks', $keystore,
                '--ks-key-alias', $alias,
                '--ks-pass', 'pass:' . $ksPass,
                '--key-pass', 'pass:' . $keyPass,
                $alignedApk,
            ]);
            $this->log($rule, 'Resultado assinatura: ' . mb_substr($signOut, 0, 800));
            if (!is_file($signedTmp)) {
                throw new \Exception('Falha na assinatura: ' . mb_substr($signOut, 0, 500));
            }

            // ETAPA 6: Verificar (apksigner verify --print-certs)
            $verifyOut = $this->run([$apksigner, 'verify', '--print-certs', $signedTmp]);
            $this->log($rule, 'Verificação assinatura: ' . mb_substr($verifyOut, 0, 1000));
            if (stripos($verifyOut, 'ERROR') !== false || stripos($verifyOut, 'FAILED') !== false) {
                throw new \Exception('Assinatura inválida: ' . mb_substr($verifyOut, 0, 500));
            }

            // Persistir artefato final (caminho RELATIVO ao disco public)
            $finalName = $rule->id . '_' . pathinfo($apkBase, PATHINFO_FILENAME) . '.signed.apk';
            $finalRelative = 'bypass_output/' . $finalName;
            $finalFull = storage_path('app/public/' . $finalRelative);
            if (!is_dir(dirname($finalFull))) {
                mkdir(dirname($finalFull), 0755, true);
            }
            if (!@rename($signedTmp, $finalFull)) {
                copy($signedTmp, $finalFull);
                @unlink($signedTmp);
            }

            $this->cleanupDirectory($decompiledDir);

            $rule->update([
                'status' => 'completed',
                'output_path' => $finalRelative,
                'phase' => 'completed',
                'progress' => 100,
            ]);
            $this->log($rule, '✅ Job concluído! APK em: ' . $finalRelative);

        } catch (\Exception $e) {
            $this->log($rule, '❌ ERRO: ' . $e->getMessage());
            $rule->update([
                'status' => 'failed',
                'phase' => $rule->phase ?? 'decompile',
                'progress' => 0,
            ]);
            if (isset($decompiledDir) && is_dir($decompiledDir)) {
                $this->cleanupDirectory($decompiledDir);
            }
        }
    }

    /**
     * Captura os certificados da assinatura ORIGINAL (antes de qualquer
     * modificação) e guarda em bypass_rules.original_certs.
     * Best-effort: nunca falha o job (APK pode não estar assinado).
     */
    protected function recordOriginalCerts(BypassRule $rule, string $apkFullPath): ?string
    {
        try {
            $apksigner = $this->buildToolsDir() . '/apksigner';
            if (!is_file($apksigner) || !is_file($apkFullPath)) {
                $this->log($rule, 'Assinatura original: apksigner ou APK indisponível para leitura.');
                return null;
            }
            $out = $this->run([$apksigner, 'verify', '--print-certs', $apkFullPath]);
            $summary = mb_substr(trim($out), 0, 1500);
            if ($summary === '') {
                $this->log($rule, 'Assinatura original: APK sem assinatura detectável (verify vazio).');
                return null;
            }
            $rule->update(['original_certs' => $summary]);
            $this->log($rule, 'Assinatura ORIGINAL registrada (será SUBSTITUÍDA pela re-assinatura Vault): ' . mb_substr($summary, 0, 400));
            return $summary;
        } catch (\Throwable $e) {
            $this->log($rule, 'Assinatura original: leitura falhou (' . $e->getMessage() . '). Seguindo sem registro.');
            return null;
        }
    }

    /**
     * Patches reais e auditáveis (perfil laboratório de teste):
     *  1. android:debuggable="true" no <application>;
     *  2. android:networkSecurityConfig="@xml/network_security_config" com
     *     cleartext permitido + CAs de usuário (para MITM em lab autorizado).
     * Retorna descrição curta verificável do que foi alterado.
     */
    protected function applyPatches(string $decompiledDir, string $targetPackage): string
    {
        $done = [];
        $done[] = $this->patchDebuggable($decompiledDir);
        $done[] = $this->patchNetworkSecurityConfig($decompiledDir);
        return implode('; ', $done) . " (target: {$targetPackage})";
    }

    protected function patchDebuggable(string $decompiledDir): string
    {
        $manifestPath = $decompiledDir . '/AndroidManifest.xml';

        if (!is_file($manifestPath)) {
            throw new \Exception('AndroidManifest.xml não encontrado para patch.');
        }

        $content = file_get_contents($manifestPath);
        if ($content === false) {
            throw new \Exception('Não foi possível ler AndroidManifest.xml.');
        }

        $before = $content;

        // 1) Se já existe android:debuggable, força para true
        if (preg_match('/android:debuggable\s*=\s*"(true|false)"/i', $content)) {
            $content = preg_replace(
                '/android:debuggable\s*=\s*"(true|false)"/i',
                'android:debuggable="true"',
                $content
            );
            $desc = 'android:debuggable forçado para "true"';
        } else {
            // 2) Injeta no <application ...>
            if (!preg_match('/<application\b[^>]*>/', $content)) {
                throw new \Exception('Tag <application> não encontrada no manifest.');
            }
            $content = preg_replace(
                '/<application\b([^>]*)>/',
                '<application$1 android:debuggable="true">',
                $content,
                1
            );
            $desc = 'android:debuggable="true" injetado em <application>';
        }

        if ($content === $before) {
            throw new \Exception('Patch debuggable não alterou o manifest (conteúdo idêntico).');
        }

        file_put_contents($manifestPath, $content);

        // Verificação pós-patch
        $check = file_get_contents($manifestPath);
        if (strpos($check, 'android:debuggable="true"') === false) {
            throw new \Exception('Verificação do patch falhou: debuggable não encontrado após escrita.');
        }

        return $desc;
    }

    protected function patchNetworkSecurityConfig(string $decompiledDir): string
    {
        $manifestPath = $decompiledDir . '/AndroidManifest.xml';

        if (!is_file($manifestPath)) {
            throw new \Exception('AndroidManifest.xml não encontrado para patch NSC.');
        }

        // Arquivo res/xml/network_security_config.xml (lab: cleartext + user CAs)
        $xmlDir = $decompiledDir . '/res/xml';
        if (!is_dir($xmlDir)) {
            mkdir($xmlDir, 0755, true);
        }
        $config = '<?xml version="1.0" encoding="utf-8"?>' . "\n"
            . '<network-security-config>' . "\n"
            . '  <base-config cleartextTrafficPermitted="true">' . "\n"
            . '    <trust-anchors>' . "\n"
            . '      <certificates src="system" />' . "\n"
            . '      <certificates src="user" />' . "\n"
            . '    </trust-anchors>' . "\n"
            . '  </base-config>' . "\n"
            . '</network-security-config>' . "\n";
        file_put_contents($xmlDir . '/network_security_config.xml', $config);

        $content = file_get_contents($manifestPath);
        if ($content === false) {
            throw new \Exception('Não foi possível reler AndroidManifest.xml para patch NSC.');
        }

        if (preg_match('/android:networkSecurityConfig\s*=\s*"[^"]*"/', $content)) {
            $content = preg_replace(
                '/android:networkSecurityConfig\s*=\s*"[^"]*"/',
                'android:networkSecurityConfig="@xml/network_security_config"',
                $content
            );
            $desc = 'networkSecurityConfig apontado p/ @xml/network_security_config (lab)';
        } else {
            if (!preg_match('/<application\b[^>]*>/', $content)) {
                throw new \Exception('Tag <application> não encontrada no manifest (NSC).');
            }
            $content = preg_replace(
                '/<application\b([^>]*)>/',
                '<application$1 android:networkSecurityConfig="@xml/network_security_config">',
                $content,
                1
            );
            $desc = 'networkSecurityConfig="@xml/network_security_config" injetado (lab)';
        }

        file_put_contents($manifestPath, $content);

        $check = file_get_contents($manifestPath);
        if (strpos($check, 'android:networkSecurityConfig="@xml/network_security_config"') === false) {
            throw new \Exception('Verificação do patch NSC falhou após escrita.');
        }
        if (!is_file($xmlDir . '/network_security_config.xml')) {
            throw new \Exception('Arquivo res/xml/network_security_config.xml não persistiu.');
        }

        return $desc;
    }

    /**
     * Executa comando com argumentos escapados (sem interpolação shell).
     */
    protected function run(array $args): string
    {
        $escaped = array_map(fn ($a) => escapeshellarg((string) $a), $args);
        $cmd = implode(' ', $escaped) . ' 2>&1';
        $out = shell_exec($cmd);
        return (string) ($out ?? '');
    }

    protected function stamp(string $message): string
    {
        return '[' . now()->format('Y-m-d H:i:s') . '] ' . $message . "\n";
    }

    protected function updateProgress(BypassRule $rule, string $phase, int $progress, string $message): void
    {
        $rule->update(['phase' => $phase, 'progress' => $progress]);
        $this->log($rule, $message);
    }

    protected function log(BypassRule $rule, string $message): void
    {
        $fresh = $rule->fresh() ?? $rule;
        $fresh->update(['log' => ($fresh->log ?? '') . $this->stamp($message)]);
        Log::info("ProcessBypassJob [{$rule->id}]: {$message}");
    }

    protected function cleanupDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $file) {
            $files[] = $file;
        }
        foreach ($files as $file) {
            $path = $file->getRealPath() ?: $file->getPathname();
            $file->isDir() ? @rmdir($path) : @unlink($path);
        }
        @rmdir($dir);
    }
}
