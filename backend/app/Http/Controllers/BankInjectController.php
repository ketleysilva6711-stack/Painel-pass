<?php

namespace App\Http\Controllers;

use App\Models\BankInject;
use Illuminate\Http\Request;

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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'bank_name'   => 'required|string|max:255',
            'name'        => 'nullable|string|max:255',
            'inject_type' => 'nullable|string|max:255',
        ]);

        $injectType = $validated['inject_type'] ?? $validated['name'] ?? 'default';

        $item = BankInject::create([
            'user_id'     => $request->user()->id,
            'bank_name'   => $validated['bank_name'],
            'inject_type' => $injectType,
        ]);

        return response()->json(['message' => 'Inject configurado', 'item' => $item], 201);
    }

    public function update(Request $request, BankInject $bankInject)
    {
        $this->authorizeOwner($request, $bankInject);

        $validated = $request->validate([
            'bank_name'   => 'sometimes|string|max:255',
            'name'        => 'sometimes|string|max:255',
            'inject_type' => 'sometimes|string|max:255',
            'status'      => 'sometimes|string|max:50',
        ]);

        if (array_key_exists('name', $validated)) {
            if (!array_key_exists('inject_type', $validated)) {
                $validated['inject_type'] = $validated['name'];
            }
            unset($validated['name']);
        }

        $bankInject->update($validated);

        return response()->json(['message' => 'Inject atualizado', 'item' => $bankInject]);
    }

    public function destroy(Request $request, BankInject $bankInject)
    {
        $this->authorizeOwner($request, $bankInject);
        $bankInject->delete();

        return response()->json(['message' => 'Inject excluÃ­do']);
    }

    private function authorizeOwner(Request $request, BankInject $item): void
    {
        if ($item->user_id !== $request->user()->id) {
            abort(403, 'Acesso negado.');
        }
    }
}