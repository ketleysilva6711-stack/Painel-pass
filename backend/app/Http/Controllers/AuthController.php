<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;

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
            'planoNome'             => $user->plan?->name ?? 'Plano Cortesia 7 Dias',
            'expiraEmDias'          => $user->conta_expira_em ? max(0, (int) now()->diffInDays($user->conta_expira_em, false)) : 30,
            'totpEnabled'           => $user->totp_enabled ?? false,
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

    // 2FA TOTP Methods
    public function totpSecret(Request $request)
    {
        $user = $request->user();
        
        if ($user->totp_enabled) {
            return response()->json([
                'message' => '2FA já está habilitado. Desabilite primeiro para gerar novo secret.',
                'secret' => $user->totp_secret,
                'qr_code' => $user->totp_qr_code_url,
            ], 400);
        }

        $google2fa = app(Google2FA::class);
        $secret = $google2fa->generateSecretKey();
        
        $user->totp_secret = $secret;
        $user->save();

        return response()->json([
            'secret' => $secret,
            'qr_code' => $user->totp_qr_code_url,
        ]);
    }

    public function enableTotp(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = $request->user();
        
        if (!$user->totp_secret) {
            return response()->json(['message' => 'Gere o secret primeiro em /auth/totp-secret'], 400);
        }

        $google2fa = app(Google2FA::class);
        $valid = $google2fa->verifyKey($user->totp_secret, $request->code);

        if (!$valid) {
            return response()->json(['message' => 'Código TOTP inválido.'], 401);
        }

        $user->totp_enabled = true;
        $user->save();

        return response()->json(['message' => '2FA habilitado com sucesso!', 'totpEnabled' => true]);
    }

    public function disableTotp(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = $request->user();
        $google2fa = app(Google2FA::class);
        $valid = $google2fa->verifyKey($user->totp_secret, $request->code);

        if (!$valid) {
            return response()->json(['message' => 'Código TOTP inválido.'], 401);
        }

        $user->totp_enabled = false;
        $user->totp_secret = null;
        $user->save();

        return response()->json(['message' => '2FA desabilitado com sucesso!', 'totpEnabled' => false]);
    }

    public function verifyTOTP(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = $request->user();
        
        if (!$user->totp_enabled || !$user->totp_secret) {
            return response()->json(['message' => '2FA não configurado.'], 400);
        }

        $google2fa = app(Google2FA::class);
        $valid = $google2fa->verifyKey($user->totp_secret, $request->code);

        if (!$valid) {
            return response()->json(['success' => false, 'message' => 'Código inválido.'], 401);
        }

        return response()->json(['success' => true, 'message' => '2FA verificado']);
    }
}