<?php

namespace App\Http\Controllers;

use App\Models\Consulta;
use Illuminate\Http\Request;

class ConsultaController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(Consulta::where('user_id', $request->user()->id)->orderBy('id', 'desc')->get());
    }

    public function store(Request $request)
    {
        $request->validate(['documento' => 'required|string']);
        $consulta = Consulta::create([
            'user_id' => $request->user()->id,
            'tipo' => 'CPF/CNPJ',
            'documento' => $request->documento,
            'resultado' => ['status' => 'REGULAR', 'nome' => 'CONSULTA REALIZADA COM SUCESSO', 'data' => now()->toDateString()],
            'ip' => $request->ip(),
        ]);
        return response()->json($consulta, 201);
    }
}