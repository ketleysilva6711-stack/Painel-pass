<?php
namespace App\Http\Controllers;

use App\Models\PlayFakeApp;
use Illuminate\Http\Request;

class PublicAppController extends Controller
{
    public function show($slug)
    {
        $app = PlayFakeApp::where('slug', $slug)->first();

        if (!$app) {
            return response()->json(['message' => 'Aplicativo não encontrado.'], 404);
        }

        $app->increment('downloads');

        return response()->json([
            'id'          => $app->id,
            'name'        => $app->name,
            'developer'   => $app->developer ?? 'Google LLC',
            'slug'        => $app->slug,
            'apk_url'     => $app->apk_url,
            'icon'        => $app->icon ?? null,
            'rating'      => 4.8,
            'reviews'     => '125 mil avaliações',
            'downloads'   => '10 mi+',
            'size'        => '45 MB',
            'created_at'  => $app->created_at->toISOString(),
        ]);
    }
}