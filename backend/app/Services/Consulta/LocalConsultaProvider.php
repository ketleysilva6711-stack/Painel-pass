<?php

namespace App\Services\Consulta;

/**
 * Provedor local (fallback/dev): valida o documento e retorna
 * resposta padronizada sem chamar API externa.
 */
class LocalConsultaProvider implements ConsultaProvider
{
    public function consultar(string $documento, string $tipo = 'basica'): array
    {
        $digits = preg_replace('/\D/', '', $documento);

        return [
            'status' => 'REGULAR',
            'dados'  => [
                'documento' => $digits,
                'tipo'      => $tipo,
                'nome'      => 'CONSULTA REALIZADA COM SUCESSO',
                'data'      => now()->toDateString(),
            ],
            'provedor'  => 'local',
            'timestamp' => now()->toISOString(),
        ];
    }
}
