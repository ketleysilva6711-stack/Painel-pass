<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleRevendedor
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user || !in_array($user->role, ['revendedor', 'admin'])) {
            return response()->json(['message' => 'Acesso negado. Requer privilégios de Revendedor.'], 403);
        }
        return $next($request);
    }
}
