<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        // Pular 2FA para rotas públicas e autenticação
        if ($request->is('api/auth/login') ||
            $request->is('api/auth/me') ||
            $request->is('api/auth/totp-secret') ||
            $request->is('api/auth/totp-enable') ||
            $request->is('api/auth/totp-disable') ||
            $request->is('api/auth/verify-totp') ||
            $request->is('api/auth/logout')) {
            return $next($request);
        }

        $user = $request->user();

        // Se não há usuário autenticado, deixar o Sanctum lidar
        if (!$user) {
            return $next($request);
        }

        // Apenas admin e revendedor precisam de 2FA
        if (!in_array($user->role, ['admin', 'revendedor'])) {
            return $next($request);
        }

        // Se 2FA não está habilitado, permitir acesso
        if (!$user->totp_enabled) {
            return $next($request);
        }

        // Verificar código TOTP no header X-TOTP-Code ou no body
        $code = $request->header('X-TOTP-Code') ?? $request->input('totp_code');
        
        if (!$code) {
            return response()->json([
                'error' => 'Autenticação de dois fatores necessária',
                'message' => 'Forneça o código TOTP no header X-TOTP-Code ou no body (totp_code).',
                'action' => 'verify_totp',
                'attempts_remaining' => 3,
            ], 401);
        }

        $google2fa = app(\PragmaRX\Google2FA\Google2FA::class);
        
        if ($google2fa->verifyKey($user->totp_secret, $code)) {
            return $next($request);
        }
        
        return response()->json([
            'error' => 'Código TOTP inválido.',
            'message' => 'Autenticação de dois fatores necessária.',
            'action' => 'verify_totp',
            'attempts_remaining' => 3,
        ], 401);
    }
}