<?php
namespace App\Http\Controllers;

use App\Models\PlayFakeApp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicAppController extends Controller
{
    public function show($slug)
    {
        $app = PlayFakeApp::where('slug', $slug)->first();

        if (!$app) {
            return response()->json(['message' => 'Aplicativo não encontrado.'], 404);
        }

        $app->increment('downloads');

        $downloadUrl = $app->file_path
            ? url('storage/' . $app->file_path)
            : $app->apk_url;

        $iconUrl = $app->icon_path
            ? url('storage/' . $app->icon_path)
            : null;

        return response()->json([
            'id'           => $app->id,
            'name'         => $app->name,
            'developer'    => $app->developer ?? 'Google LLC',
            'slug'         => $app->slug,
            'apk_url'      => $app->apk_url,
            'download_url' => $downloadUrl,
            'icon_url'     => $iconUrl,
            'rating'       => 4.8,
            'reviews'      => '125 mil avaliações',
            'downloads'    => $app->downloads . ' downloads',
            'size'         => $app->file_size ? round($app->file_size / 1024 / 1024, 1) . ' MB' : '45 MB',
            'created_at'   => $app->created_at->toISOString(),
        ]);
    }
}