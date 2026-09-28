<?php

namespace App\Services\Consulta;

/**
 * Resposta padronizada: { status, dados, provedor, timestamp }
 */
interface ConsultaProvider
{
    /**
     * @param string $documento CPF/CNPJ (somente dígitos ou formatado)
     * @param string $tipo      ex: cpf, cnpj, basica, completa
     * @return array{status: string, dados: array, provedor: string, timestamp: string}
     */
    public function consultar(string $documento, string $tipo = 'basica'): array;
}
