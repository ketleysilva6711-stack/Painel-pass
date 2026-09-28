<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RunBackupCommand extends Command
{
    protected $signature = 'backup:run';
    protected $description = 'Executar backup automático do banco de dados';

    public function handle(): int
    {
        $this->info('Iniciando backup automático do banco de dados...');
        
        $date = date('Ymd_His');
        $backupDir = '/var/www/html/storage/backups';
        $fileName = "the_vault_{$date}.sql";
        $filePath = "{$backupDir}/{$fileName}";
        
        // Criar diretório de backup se não existir
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        
        // Executar mysqldump via mysql client (disponível no container PHP)
        $cmd = "mysqldump --skip-ssl -h mysql -u root -proot_secret the_vault 2>&1";
        $output = shell_exec($cmd);
        
        if ($output === null || $output === false) {
            $this->error('Erro ao executar backup: comando não executado');
            Log::error('Backup automático falhou: shell_exec retornou null');
            return self::FAILURE;
        }
        
        // Escrever o arquivo
        file_put_contents($filePath, $output);
        
        // Verificar se o arquivo foi criado
        if (!file_exists($filePath)) {
            $this->error('Arquivo de backup não foi criado');
            Log::error('Backup automático falhou: arquivo não criado', ['output' => substr($output, 0, 200)]);
            return self::FAILURE;
        }
        
        // Comprimir o backup (caminho escapado — evita quebra/injeção)
        $gzPath = "{$filePath}.gz";
        $gzipOutput = shell_exec('gzip ' . escapeshellarg($filePath) . ' 2>&1');
        
        if (!file_exists($gzPath)) {
            $this->error('Falha ao comprimir backup');
            Log::error('Backup automático falhou: compressão falhou', ['output' => $gzipOutput]);
            return self::FAILURE;
        }
        
        $this->info("Backup criado: the_vault_{$date}.sql.gz");
        
        // Remover backups antigos (30 dias)
        $files = glob("{$backupDir}/the_vault_*.sql.gz");
        $deleted = 0;
        foreach ($files as $file) {
            if (filemtime($file) < time() - (30 * 24 * 60 * 60)) {
                unlink($file);
                $deleted++;
            }
        }
        
        if ($deleted > 0) {
            $this->info("Backups antigos removidos: {$deleted} (retenção: 30 dias)");
        }
        
        $this->info('Backup concluído com sucesso.');
        Log::info('Backup automático executado com sucesso', ['file' => $gzPath]);
        
        return self::SUCCESS;
    }
}