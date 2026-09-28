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

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'nullable|in:admin,revendedor,user',
            'conta_status' => 'nullable|string',
            'dias_expiracao' => 'nullable|integer',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'], // cast 'hashed' no model
            'role' => $data['role'] ?? 'user',
            'conta_status' => $data['conta_status'] ?? 'active',
            'conta_expira_em' => isset($data['dias_expiracao'])
                ? now()->addDays($data['dias_expiracao'])
                : null,
        ]);

        return response()->json(['message' => 'Usuário criado com sucesso.', 'user' => $user], 201);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'role' => 'nullable|in:admin,revendedor,user',
            'conta_status' => 'nullable|string',
            'dias_expiracao' => 'nullable|integer',
        ]);

        if (isset($data['name'])) $user->name = $data['name'];
        if (isset($data['email'])) $user->email = $data['email'];
        if (!empty($data['password'])) $user->password = $data['password'];
        if (isset($data['role'])) $user->role = $data['role'];
        if (isset($data['conta_status'])) $user->conta_status = $data['conta_status'];
        if (isset($data['dias_expiracao'])) {
            $user->conta_expira_em = now()->addDays($data['dias_expiracao']);
        }
        $user->save();

        return response()->json(['message' => 'Usuário atualizado com sucesso.', 'user' => $user]);
    }
}