<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Log;

class LogAction
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($request->user() && in_array($request->method(), ['POST', 'PUT', 'DELETE'])) {
            try {
                Log::create([
                    'user_id'   => $request->user()->id,
                    'tipo'      => $request->method(),
                    'categoria' => $request->segment(2) ?? 'api',
                    'ator'      => $request->user()->email,
                    'descricao' => 'Ação ' . $request->method() . ' executada em ' . $request->path(),
                    'metadata'  => $request->except(['password', 'password_confirmation']),
                    'ip'        => $request->ip(),
                ]);
            } catch (\Exception $e) {}
        }

        return $response;
    }
}