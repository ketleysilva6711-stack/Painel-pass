<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Tutorial;
use App\Models\Plan;

class RevendedorController extends Controller
{
    public function usuarios(Request $request)
    {
        $query = User::with('plan')->where('role', 'user')->orderBy('id', 'desc');
        if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'reseller_id')) {
            $revId = $request->user()->id;
            $query->where(function ($q) use ($revId) {
                $q->where('reseller_id', $revId)->orWhereNull('reseller_id');
            });
        }
        return response()->json($query->get());
    }

    public function storeUsuario(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'expiration_days' => 'nullable|integer',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'role' => 'user',
            'plan_id' => Plan::where('active', true)->first()?->id,
            'conta_expira_em' => $request->expiration_days
                ? now()->addDays($request->expiration_days)
                : null,
        ];
        if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'reseller_id')) {
            $data['reseller_id'] = $request->user()->id;
        }
        $user = User::create($data);

        return response()->json($user, 201);
    }

    public function showUsuario($id)
    {
        return response()->json(User::findOrFail($id));
    }

    public function updateUsuario(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'password' => 'sometimes|string|min:8|confirmed',
            'expiration_days' => 'sometimes|nullable|integer',
        ]);

        if (isset($data['expiration_days'])) {
            $user->conta_expira_em = now()->addDays((int) $data['expiration_days']);
        }
        unset($data['expiration_days']);
        // 'password' => 'hashed' no cast do model já faz o hash
        $user->fill($data);
        $user->save();
        return response()->json($user);
    }

    public function suporteShow($conversationId)
    {
        return response()->json([
            'id' => $conversationId,
            'subject' => 'Suporte - Conversa #' . $conversationId,
            'status' => 'open',
            'messages' => [],
        ]);
    }

    public function suporteAddMessage(Request $request, $conversationId)
    {
        $request->validate(['message' => 'required|string']);
        return response()->json([
            'id' => $conversationId,
            'message' => $request->message,
            'sender' => auth()->user()->name,
            'created_at' => now(),
        ], 201);
    }
}
