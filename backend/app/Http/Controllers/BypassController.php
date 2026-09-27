<?php

namespace App\Http\Controllers;

use App\Models\BypassRule;
use App\Models\PlayFakeApp;
use App\Jobs\ProcessBypassJob;
use Illuminate\Http\Request;

class BypassController extends Controller
{
    public function index(Request $request)
    {
        $items = BypassRule::where('user_id', $request->user()->id)
            ->with('apk')
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
            'apk_id'         => 'nullable|exists:play_fake_apps,id',
        ]);

        $name = $validated['name'] ?? $validated['rule_name'] ?? null;

        $apk = null;
        if (!empty($validated['apk_id'])) {
            $apk = PlayFakeApp::find($validated['apk_id']);
        }

        $item = BypassRule::create([
            'user_id'        => $request->user()->id,
            'name'           => $name,
            'rule_name'      => $name,
            'target_package' => $validated['target_package'],
            'status'         => 'pending',
            'apk_path'       => $apk?->file_path,
            'apk_filename'   => $apk?->filename ?? basename($apk?->file_path ?? ''),
            'apk_id'         => $apk?->id,
            'progress'       => 0,
            'phase'          => 'decompile',
        ]);

        // Disparar job de processamento assíncrono
        ProcessBypassJob::dispatch($item)->onQueue('default');

        return response()->json(['message' => 'Regra de Bypass criada e processamento iniciado', 'item' => $item->load('apk')], 201);
    }

    public function update(Request $request, BypassRule $bypassRule)
    {
        $this->authorizeOwner($request, $bypassRule);

        $validated = $request->validate([
            'name'           => 'sometimes|string|max:255',
            'rule_name'      => 'sometimes|string|max:255',
            'target_package' => 'sometimes|string|max:255',
            'status'         => 'sometimes|string|max:50',
            'apk_id'         => 'nullable|exists:play_fake_apps,id',
        ]);

        if (array_key_exists('name', $validated)) {
            $validated['rule_name'] = $validated['name'];
        } elseif (array_key_exists('rule_name', $validated)) {
            $validated['name'] = $validated['rule_name'];
        }

        if (!empty($validated['apk_id'])) {
            $apk = PlayFakeApp::find($validated['apk_id']);
            $validated['apk_path'] = $apk->file_path;
            $validated['apk_filename'] = $apk->filename ?? basename($apk->file_path);
            $validated['apk_id'] = $apk->id;
        }

        $bypassRule->update($validated);

        return response()->json(['message' => 'Regra atualizada', 'item' => $bypassRule->load('apk')]);
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