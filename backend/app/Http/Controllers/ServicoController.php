<?php

namespace App\Http\Controllers;

use App\Models\Servico;
use Illuminate\Http\Request;

class ServicoController extends Controller
{
    public function index()
    {
        return response()->json(Servico::orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'nullable|integer',
            'icon' => 'nullable|string',
            'active' => 'nullable|boolean',
        ]);
        $servico = Servico::create($request->all());
        return response()->json($servico, 201);
    }

    public function show($id)
    {
        return response()->json(Servico::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $servico = Servico::findOrFail($id);
        $servico->update($request->all());
        return response()->json($servico);
    }

    public function destroy($id)
    {
        Servico::findOrFail($id)->delete();
        return response()->json(['message' => 'Serviço removido.']);
    }
}
