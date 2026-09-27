<?php

namespace App\Http\Controllers;

use App\Models\PlayFakeApp;
use Illuminate\Http\Request;

class PlayFakeController extends Controller
{
    public function index(Request $request)
    {
        $apps = PlayFakeApp::where('user_id', $request->user()->id)->get();
        return response()->json(['apps' => $apps]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'package_name' => 'required|string|max:255',
        ]);

        $app = PlayFakeApp::create([
            'user_id' => $request->user()->id,
            'name' => $validated['name'],
            'package_name' => $validated['package_name'],
            'status' => 'active',
            'slug' => \Illuminate\Support\Str::slug($validated['name']) . '-' . \Illuminate\Support\Str::random(6),
        ]);

        return response()->json(['message' => 'App Play Fake criado com sucesso', 'app' => $app], 201);
    }
}