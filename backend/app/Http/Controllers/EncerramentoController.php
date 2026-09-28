<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EncerramentoController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([
            'status' => 'active',
            'scheduled_at' => now()->addHours(24)->toISOString(),
            'reason' => 'Manutenção programada',
            'warned' => true,
            'users_notified' => true,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'reason' => 'required|string',
            'scheduled_at' => 'required|date',
            'notify_users' => 'nullable|boolean',
        ]);

        return response()->json([
            'message' => 'Encerramento agendado com sucesso.',
            'data' => $request->all(),
            'scheduled_at' => $request->scheduled_at,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'reason' => 'nullable|string',
            'scheduled_at' => 'nullable|date',
            'status' => 'nullable|string',
        ]);

        return response()->json([
            'message' => 'Encerramento atualizado.',
            'data' => $request->all(),
        ]);
    }

    public function warning(Request $request)
    {
        return response()->json([
            'message' => 'Aviso de encerramento emitido.',
            'status' => 'warning',
        ]);
    }

    public function shutdown(Request $request)
    {
        return response()->json([
            'message' => 'Sessão encerrada.',
            'status' => 'closed',
        ]);
    }
}