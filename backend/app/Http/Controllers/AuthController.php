<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    private function perfil(User $user): array {
        return [
            'id'                    => $user->id,
            'name'                  => $user->name,
            'email'                 => $user->email,
            'role'                  => $user->role,
            'roleLabel'             => $user->role_label,
            'eyebrow'               => $user->eyebrow,
            'initials'              => $user->initials,
            'funcionalidades'       => $user->funcionalidades ?? [],
            'contaStatus'           => $user->conta_status,
            'contaStatusLabel'      => $user->conta_status_label,
            'contaStatusKind'       => $user->conta_status_kind,
            'contaExpiraEm'         => $user->conta_expira_em?->toISOString(),
            'resellerDisponivel'    => $user->reseller_disponivel,
            'podeServicosExternos'  => $user->pode_servicos_externos,
            'versao'                => 'v2.0',
        ];
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $data['email'])
                    ->orWhere('name', $data['email'])
                    ->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Usuário ou senha inválidos.'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user'         => $this->perfil($user),
        ]);
    }

    public function me(Request $request)
    {
        return response()->json(['user' => $this->perfil($request->user())]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sessão encerrada.']);
    }
}