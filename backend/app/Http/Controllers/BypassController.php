<?php

namespace App\Http\Controllers;

use App\Models\BypassRule;
use Illuminate\Http\Request;

class BypassController extends Controller
{
    public function index(Request $request)
    {
        $items = BypassRule::where('user_id', $request->user()->id)
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => $items, 'items' => $items]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'nullable|string|max:255',
            'rule_name'      => 'nullable|string|max:255',
            'target_package' => 'required|string|max:255',
        ]);

        $name = $validated['name'] ?? $validated['rule_name'] ?? null;

        $item = BypassRule::create([
            'user_id'        => $request->user()->id,
            'name'           => $name,
            'rule_name'      => $name,
            'target_package' => $validated['target_package'],
            'status'         => 'active',
        ]);

        return response()->json(['message' => 'Regra de Bypass criada', 'item' => $item], 201);
    }

    public function update(Request $request, BypassRule $bypassRule)
    {
        $this->authorizeOwner($request, $bypassRule);

        $validated = $request->validate([
            'name'           => 'sometimes|string|max:255',
            'rule_name'      => 'sometimes|string|max:255',
            'target_package' => 'sometimes|string|max:255',
            'status'         => 'sometimes|string|max:50',
        ]);

        if (array_key_exists('name', $validated)) {
            $validated['rule_name'] = $validated['name'];
        } elseif (array_key_exists('rule_name', $validated)) {
            $validated['name'] = $validated['rule_name'];
        }

        $bypassRule->update($validated);

        return response()->json(['message' => 'Regra atualizada', 'item' => $bypassRule]);
    }

    public function destroy(Request $request, BypassRule $bypassRule)
    {
        $this->authorizeOwner($request, $bypassRule);
        $bypassRule->delete();

        return response()->json(['message' => 'Regra excluída']);
    }

    private function authorizeOwner(Request $request, BypassRule $rule): void
    {
        if ($rule->user_id !== $request->user()->id) {
            abort(403, 'Acesso negado.');
        }
    }
}