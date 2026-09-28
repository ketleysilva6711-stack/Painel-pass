<?php

namespace App\Http\Controllers;

use App\Models\PlayFakeApp;
use App\Models\QrCodeLink;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class QrCodeController extends Controller
{
    public function index(Request $request)
    {
        $items = QrCodeLink::where('user_id', $request->user()->id)
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => $items, 'items' => $items]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'      => 'required|string|max:255',
            'target_url' => 'required|url|max:2048',
        ]);

        $item = QrCodeLink::create([
            'user_id'    => $request->user()->id,
            'title'      => $validated['title'],
            'target_url' => $validated['target_url'],
        ]);

        // Gerar PNG em storage/app/public/qr/{id}.png (Fase 4)
        try {
            $rel = QrCodeService::pathFor($item->id);
            QrCodeService::generate($validated['target_url'], $rel);
            $item->update(['qr_path' => $rel]);
            $item->refresh();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('QR generate falhou: ' . $e->getMessage());
        }

        $item->qr_url = $item->qr_path ? url('storage/' . $item->qr_path) : url('/api/q/' . $item->id . '.png');

        return response()->json(['message' => 'QR Code gerado', 'item' => $item], 201);
    }

    public function update(Request $request, QrCodeLink $qrCodeLink)
    {
        $this->authorizeOwner($request, $qrCodeLink);

        $validated = $request->validate([
            'title'      => 'sometimes|string|max:255',
            'target_url' => 'sometimes|url|max:2048',
        ]);

        $qrCodeLink->update($validated);

        // Regenerar PNG se a URL mudou
        if (array_key_exists('target_url', $validated)) {
            try {
                $rel = QrCodeService::pathFor($qrCodeLink->id);
                QrCodeService::generate($qrCodeLink->target_url, $rel);
                $qrCodeLink->update(['qr_path' => $rel]);
                $qrCodeLink->refresh();
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('QR regenerate falhou: ' . $e->getMessage());
            }
        }

        return response()->json(['message' => 'QR Code atualizado', 'item' => $qrCodeLink]);
    }

    public function destroy(Request $request, QrCodeLink $qrCodeLink)
    {
        $this->authorizeOwner($request, $qrCodeLink);
        if ($qrCodeLink->qr_path) {
            Storage::disk('public')->delete($qrCodeLink->qr_path);
        }
        $qrCodeLink->delete();

        return response()->json(['message' => 'QR Code excluído']);
    }

    public function redirect($id)
    {
        $qr = QrCodeLink::findOrFail($id);
        $qr->increment('scans');

        return redirect()->away($qr->target_url);
    }

    /**
     * GET /q/{id}.png — serve o PNG gerado (gera on-demand se ausente).
     * Log de scans opcional via ?log=1.
     *
     * Fallback Fase 1: se não houver QrCodeLink com esse id, tenta um
     * PlayFakeApp (a página /c/{slug} referencia /api/q/{app_id}.png).
     * Nesse caso o QR codifica a URL pública de instalação /c/{slug}
     * e o PNG fica em qr/playfake_{id}.png (sem colidir com qr/{id}.png).
     */
    public function png(Request $request, $id)
    {
        // Aceita "123" ou "123.png"
        $cleanId = (int) preg_replace('/\D/', '', (string) $id);
        $qr = QrCodeLink::find($cleanId);

        if (!$qr) {
            return $this->playFakePng($cleanId);
        }

        if ($request->boolean('log', false)) {
            $qr->increment('scans');
        }

        $rel = $qr->qr_path ?: QrCodeService::pathFor($qr->id);
        $full = storage_path('app/public/' . $rel);

        if (!is_file($full)) {
            try {
                QrCodeService::generate($qr->target_url, $rel);
                if (empty($qr->qr_path)) {
                    $qr->update(['qr_path' => $rel]);
                }
            } catch (\Throwable $e) {
                abort(500, 'Falha ao gerar QR Code.');
            }
        }

        return response()->file($full, ['Content-Type' => 'image/png']);
    }

    /**
     * QR on-demand da página pública de um PlayFakeApp.
     */
    protected function playFakePng(int $appId)
    {
        $app = PlayFakeApp::find($appId);
        if (!$app) {
            abort(404, 'QR Code não encontrado.');
        }

        $rel = 'qr/playfake_' . $app->id . '.png';
        $full = storage_path('app/public/' . $rel);

        if (!is_file($full)) {
            try {
                QrCodeService::generate(url('/c/' . $app->slug), $rel);
            } catch (\Throwable $e) {
                abort(500, 'Falha ao gerar QR Code.');
            }
        }

        return response()->file($full, ['Content-Type' => 'image/png']);
    }

    private function authorizeOwner(Request $request, QrCodeLink $item): void
    {
        if ($item->user_id !== $request->user()->id) {
            abort(403, 'Acesso negado.');
        }
    }
}
