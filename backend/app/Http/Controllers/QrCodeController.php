<?php

namespace App\Http\Controllers;

use App\Models\QrCodeLink;
use Illuminate\Http\Request;

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

        return response()->json(['message' => 'QR Code atualizado', 'item' => $qrCodeLink]);
    }

    public function destroy(Request $request, QrCodeLink $qrCodeLink)
    {
        $this->authorizeOwner($request, $qrCodeLink);
        $qrCodeLink->delete();

        return response()->json(['message' => 'QR Code excluído']);
    }

    public function redirect($id)
    {
        $qr = QrCodeLink::findOrFail($id);
        $qr->increment('scans');

        return redirect()->away($qr->target_url);
    }

    private function authorizeOwner(Request $request, QrCodeLink $item): void
    {
        if ($item->user_id !== $request->user()->id) {
            abort(403, 'Acesso negado.');
        }
    }
}