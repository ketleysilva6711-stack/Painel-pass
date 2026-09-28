<?php

namespace App\Services\Consulta;

use Illuminate\Support\Facades\Http;

/**
 * Provedor real genérico via HTTP (chave em CONSULTA_API_KEY).
 *
 * Configure no .env:
 *   CONSULTA_PROVIDER=http
 *   CONSULTA_API_URL=https://provedor.exemplo.com/consulta
 *   CONSULTA_API_KEY=sua-chave
 *
 * Envia { chave_provedor, cpf_cnpj, tipo_consulta } e normaliza a resposta
 * para { status, dados, provedor, timestamp }.
 */
class HttpConsultaProvider implements ConsultaProvider
{
    public function consultar(string $documento, string $tipo = 'basica'): array
    {
        $url = config('services.consulta.url');
        $key = config('services.consulta.key');

        if (empty($url) || empty($key)) {
            throw new \RuntimeException('Provedor de consulta não configurado (CONSULTA_API_URL / CONSULTA_API_KEY).');
        }

        $response = Http::timeout(20)->post($url, [
            'chave_provedor' => $key,
            'cpf_cnpj'       => preg_replace('/\D/', '', $documento),
            'tipo_consulta'  => $tipo,
        ]);

        if (!$response->successful()) {
            return [
                'status'    => 'ERRO',
                'dados'     => ['http_status' => $response->status(), 'mensagem' => 'Falha no provedor'],
                'provedor'  => 'http',
                'timestamp' => now()->toISOString(),
            ];
        }

        $body = $response->json();

        return [
            'status'    => $body['status'] ?? 'OK',
            'dados'     => $body['dados'] ?? $body,
            'provedor'  => 'http',
            'timestamp' => now()->toISOString(),
        ];
    }
}
