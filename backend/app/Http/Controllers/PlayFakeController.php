<?php

namespace App\Http\Controllers;

use App\Models\PlayFakeApp;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PlayFakeController extends Controller
{
    public function index(Request $request)
    {
        $apps = PlayFakeApp::where('user_id', $request->user()->id)
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => $apps, 'apps' => $apps]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'developer'    => 'nullable|string|max:255',
            'package_name' => 'nullable|string|max:255',
            'apk_url'      => 'nullable|url|max:2048',
        ]);

        $app = PlayFakeApp::create([
            'user_id'      => $request->user()->id,
            'name'         => $validated['name'],
            'developer'    => $validated['developer'] ?? null,
            'package_name' => $validated['package_name'] ?? null,
            'apk_url'      => $validated['apk_url'] ?? null,
            'status'       => 'active',
            'slug'         => Str::slug($validated['name']) . '-' . Str::random(6),
        ]);

        return response()->json(['message' => 'App Play Fake criado com sucesso', 'app' => $app], 201);
    }

    public function update(Request $request, PlayFakeApp $playFakeApp)
    {
        $this->authorizeOwner($request, $playFakeApp);

        $validated = $request->validate([
            'name'         => 'sometimes|string|max:255',
            'developer'    => 'nullable|string|max:255',
            'package_name' => 'nullable|string|max:255',
            'apk_url'      => 'nullable|url|max:2048',
            'status'       => 'nullable|string|max:50',
        ]);

        $playFakeApp->update($validated);

        return response()->json(['message' => 'App atualizado', 'app' => $playFakeApp]);
    }

    public function destroy(Request $request, PlayFakeApp $playFakeApp)
    {
        $this->authorizeOwner($request, $playFakeApp);
        $playFakeApp->delete();

        return response()->json(['message' => 'App excluído']);
    }

    private function authorizeOwner(Request $request, PlayFakeApp $app): void
    {
        if ($app->user_id !== $request->user()->id) {
            abort(403, 'Acesso negado.');
        }
    }
}