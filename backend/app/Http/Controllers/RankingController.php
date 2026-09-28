<?php

namespace App\Http\Controllers;

use App\Models\Ranking;
use Illuminate\Http\Request;

class RankingController extends Controller
{
    public function podio()
    {
        return response()->json(Ranking::with('user')->orderBy('points', 'desc')->take(10)->get());
    }

    public function index(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Não autorizado.'], 403);
        }
        return response()->json(Ranking::with('user')->orderBy('points', 'desc')->paginate(25));
    }

    public function store(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Não autorizado.'], 403);
        }
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'points' => 'required|integer|min:0',
            'position' => 'nullable|integer',
        ]);

        $ranking = Ranking::create($request->all());
        return response()->json($ranking, 201);
    }

    public function update(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Não autorizado.'], 403);
        }
        $ranking = Ranking::findOrFail($id);
        $ranking->update($request->all());
        return response()->json($ranking);
    }

    public function destroy($id)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'Não autorizado.'], 403);
        }
        $ranking = Ranking::findOrFail($id);
        $ranking->delete();
        return response()->json(['message' => 'Ranking removido.']);
    }
}