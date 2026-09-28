<?php

namespace App\Http\Controllers;

use App\Models\Tutorial;
use Illuminate\Http\Request;

class TutorialController extends Controller
{
    public function index()
    {
        return response()->json(Tutorial::orderBy('created_at', 'desc')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'nullable|string',
            'video_url' => 'nullable|url',
            'thumbnail_url' => 'nullable|url',
            'content' => 'nullable|string',
        ]);
        $tutorial = Tutorial::create(array_merge($request->all(), ['created_by' => $request->user()->id]));
        return response()->json($tutorial, 201);
    }

    public function show($id)
    {
        return response()->json(Tutorial::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $tutorial = Tutorial::findOrFail($id);
        $tutorial->update($request->all());
        return response()->json($tutorial);
    }

    public function destroy($id)
    {
        Tutorial::findOrFail($id)->delete();
        return response()->json(['message' => 'Tutorial removido.']);
    }

    public function revendedorIndex()
    {
        return response()->json(Tutorial::where('created_by', auth()->id())->orWhere('category', 'public')->get());
    }

    public function revendedorStore(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'nullable|string',
            'video_url' => 'nullable|url',
            'content' => 'nullable|string',
        ]);
        $tutorial = Tutorial::create(array_merge($request->all(), ['created_by' => $request->user()->id]));
        return response()->json($tutorial, 201);
    }

    public function revendedorShow($id)
    {
        return response()->json(Tutorial::findOrFail($id));
    }

    public function revendedorUpdate(Request $request, $id)
    {
        $tutorial = Tutorial::findOrFail($id);
        $tutorial->update($request->all());
        return response()->json($tutorial);
    }

    public function revendedorDestroy($id)
    {
        Tutorial::findOrFail($id)->delete();
        return response()->json(['message' => 'Tutorial removido.']);
    }
}
