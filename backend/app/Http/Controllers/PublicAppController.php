<?php
namespace App\Http\Controllers;

use App\Models\PlayFakeApp;
use Illuminate\Http\Request;

class PublicAppController extends Controller
{
    public function show(Request $request, $slug)
    {
        $app = PlayFakeApp::where('slug', $slug)->first();

        if (!$app) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Aplicativo não encontrado.'], 404);
            }
            abort(404, 'Aplicativo não encontrado.');
        }

        $app->increment('downloads');
        $app->refresh();

        $downloadUrl = $app->file_path
            ? url('storage/' . $app->file_path)
            : $app->apk_url;

        $iconUrl = $app->icon_path
            ? url('storage/' . $app->icon_path)
            : null;

        $qrUrl = url('/api/q/' . $app->id . '.png');

        $permissions = [];
        if (!empty($app->permissions)) {
            $decoded = json_decode($app->permissions, true);
            if (is_array($decoded)) {
                $permissions = $decoded;
            }
        }

        $payload = [
            'id'           => $app->id,
            'name'         => $app->name,
            'developer'    => $app->developer ?? 'Google LLC',
            'slug'         => $app->slug,
            'package_name' => $app->package_name,
            'version_name' => $app->version_name,
            'version_code' => $app->version_code,
            'apk_url'      => $app->apk_url,
            'download_url' => $downloadUrl,
            'icon_url'     => $iconUrl,
            'qr_url'       => $qrUrl,
            'permissions'  => $permissions,
            'rating'       => 4.8,
            'reviews'      => '125 mil avaliações',
            'downloads'    => $app->downloads . ' downloads',
            'size'         => $app->file_size ? round($app->file_size / 1024 / 1024, 1) . ' MB' : '45 MB',
            'created_at'   => $app->created_at->toISOString(),
        ];

        // Clientes API (Accept: application/json) continuam recebendo JSON.
        if ($request->wantsJson() && !$request->query('html')) {
            return response()->json($payload);
        }

        return response()->view('public.app', [
            'app' => $app,
            'downloadUrl' => $downloadUrl,
            'iconUrl' => $iconUrl,
            'qrUrl' => $qrUrl,
            'permissions' => $permissions,
        ]);
    }
}
