<?php

namespace App\Http\Controllers;

use App\Models\Consulta;
use App\Services\Consulta\HttpConsultaProvider;
use App\Services\Consulta\LocalConsultaProvider;
use Illuminate\Http\Request;

class ConsultaController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(Consulta::where('user_id', $request->user()->id)->orderBy('id', 'desc')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'documento' => 'required|string|max:20',
            'tipo'      => 'nullable|string|max:50',
        ]);

        $tipo = $validated['tipo'] ?? 'CPF/CNPJ';
        $providerName = config('services.consulta.provider', 'local');

        try {
            $provider = $providerName === 'http'
                ? new HttpConsultaProvider()
                : new LocalConsultaProvider();

            $resultado = $provider->consultar($validated['documento'], $tipo);
        } catch (\Throwable $e) {
            $resultado = [
                'status'    => 'ERRO',
                'dados'     => ['mensagem' => $e->getMessage()],
                'provedor'  => $providerName,
                'timestamp' => now()->toISOString(),
            ];
        }

        $consulta = Consulta::create([
            'user_id' => $request->user()->id,
            'tipo' => $tipo,
            'documento' => $validated['documento'],
            'resultado' => $resultado,
            'ip' => $request->ip(),
        ]);

        return response()->json($consulta, 201);
    }
}
