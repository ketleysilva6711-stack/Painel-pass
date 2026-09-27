<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index()
    {
        return response()->json(User::with('plan')->orderBy('id', 'desc')->get());
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $data = $request->validate([
            'role' => 'nullable|in:admin,revendedor,user',
            'conta_status' => 'nullable|string',
            'dias_expiracao' => 'nullable|integer',
        ]);

        if (isset($data['role'])) $user->role = $data['role'];
        if (isset($data['conta_status'])) $user->conta_status = $data['conta_status'];
        if (isset($data['dias_expiracao'])) {
            $user->conta_expira_em = now()->addDays($data['dias_expiracao']);
        }
        $user->save();

        return response()->json(['message' => 'Usuário atualizado com sucesso.', 'user' => $user]);
    }
}