<?php

namespace App\Jobs;

use App\Models\BypassRule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessBypassJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $bypassRule;
    protected $keystorePath;

    public function __construct(BypassRule $rule, $keystorePath = null)
    {
        $this->bypassRule = $rule;
        $this->keystorePath = $keystorePath ?? '/app/keystore/thevault.keystore';
    }

    public function handle(): void
    {
        $rule = $this->bypassRule;
        $apk = $rule->apk;
        
        $apkFullPath = storage_path('app/public/' . $apk->file_path);

        if (!$apk || !file_exists($apkFullPath)) {
            $rule->update([
                'status' => 'failed',
                'log' => 'APK original não encontrado: ' . ($apkFullPath ?? 'null'),
                'phase' => 'decompile',
                'progress' => 0
            ]);
            return;
        }

        $targetPackage = $rule->target_package;
        $decompiledDir = storage_path("app/tmp/decompiled_{$rule->id}");
        $outputApk = "{$decompiledDir}/dist/{$apk->filename}";
        
        // Use o caminho completo do APK
        $apk->path = $apkFullPath;
        
        try {
            // ETAPA 1: Descompilar com apktool
            $this->updateProgress($rule, 'decompile', 10, "Iniciando descompilação do APK...");
            $this->log($rule, "ETAPA 1: Descompilando APK com apktool...");
            
            if (!is_dir($decompiledDir)) {
                mkdir($decompiledDir, 0755, true);
            }
            
            $result = shell_exec("java -jar /usr/local/bin/apktool.jar d -f \"{$apk->path}\" -o \"{$decompiledDir}\" 2>&1");
            $this->log($rule, "Resultado descompilação: " . substr($result, 0, 500));
            
            if (!file_exists("{$decompiledDir}/AndroidManifest.xml")) {
                throw new \Exception("Falha ao descompilar APK - AndroidManifest.xml não encontrado");
            }
            
            // ETAPA 2: Aplicar patches baseados no target_package
            $this->updateProgress($rule, 'patch', 30, "Aplicando patches para {$targetPackage}...");
            $this->log($rule, "ETAPA 2: Aplicando patches para {$targetPackage}...");
            
            $this->applyPatches($decompiledDir, $targetPackage);
            
            // ETAPA 3: Recompilar com apktool
            $this->updateProgress($rule, 'recompile', 60, "Recompilando APK...");
            $this->log($rule, "ETAPA 3: Recompilando APK...");
            
            $result = shell_exec("java -jar /usr/local/bin/apktool.jar b \"{$decompiledDir}\" -o \"{$outputApk}\" 2>&1");
            $this->log($rule, "Resultado recompilação: " . substr($result, 0, 500));
            
            if (!file_exists($outputApk)) {
                throw new \Exception("Falha ao recompilar APK - arquivo de saída não gerado");
            }
            
            // ETAPA 4: Assinar com apksigner
            $this->updateProgress($rule, 'sign', 80, "Assinando APK com apksigner...");
            $this->log($rule, "ETAPA 4: Assinando APK com apksigner...");
            
            $keystore = $this->keystorePath;
            
            if (!file_exists($keystore)) {
                throw new \Exception("Keystore não encontrado em: {$keystore}");
            }
            
            $signedApk = $outputApk . '.signed';
            $signResult = shell_exec("/opt/android-sdk/build-tools/34.0.0/apksigner sign --out \"{$signedApk}\" --ks \"{$keystore}\" --ks-key-alias thevault --ks-pass pass:vault2024 --key-pass pass:vault2024 \"{$outputApk}\" 2>&1");
            $this->log($rule, "Resultado assinatura: " . substr($signResult, 0, 500));
            
            if (!file_exists($signedApk) || str_contains($signResult, 'ERROR') || str_contains($signResult, 'failed')) {
                throw new \Exception("Falha na assinatura: " . $signResult);
            }
            
            // Mover arquivo assinado para lugar definitivo
            $finalPath = storage_path("app/public/bypass_output/{$rule->id}_{$apk->filename}.signed");
            $finalDir = dirname($finalPath);
            if (!is_dir($finalDir)) {
                mkdir($finalDir, 0755, true);
            }
            rename($signedApk, $finalPath);
            
            // Verificar assinatura
            $verifyResult = shell_exec("/opt/android-sdk/build-tools/34.0.0/apksigner verify \"{$finalPath}\" 2>&1");
            $this->log($rule, "Verificação assinatura: " . $verifyResult);
            
            // Limpar diretório temporário
            $this->cleanupDirectory($decompiledDir);
            
            // Atualizar registro no banco - SUCESSO
            $rule->update([
                'status' => 'completed',
                'output_path' => $finalPath,
                'phase' => 'completed',
                'progress' => 100,
                'log' => 'Bypass processado com sucesso. APK assinado e pronto para download.'
            ]);
            
            $this->log($rule, "✅ Job concluído com sucesso! APK disponível em: {$finalPath}");
            
        } catch (\Exception $e) {
            $this->log($rule, "❌ ERRO: " . $e->getMessage());
            
            $rule->update([
                'status' => 'failed',
                'phase' => $rule->phase ?? 'decompile',
                'progress' => 0,
                'log' => 'Erro: ' . $e->getMessage()
            ]);
            
            // Limpar em caso de erro
            if (is_dir($decompiledDir)) {
                $this->cleanupDirectory($decompiledDir);
            }
        }
    }
    
    protected function applyPatches(string $decompiledDir, string $targetPackage): void
    {
        $manifestPath = "{$decompiledDir}/AndroidManifest.xml";
        
        if (!file_exists($manifestPath)) {
            return;
        }
        
        $content = file_get_contents($manifestPath);
        
        // Exemplo de patch: alterar package name no manifest se necessário
        // Lógica real de patches seria implementada aqui baseada no $targetPackage
        // Por enquanto, apenas log
        $this->log($this->bypassRule, "Patches aplicados para {$targetPackage} (placeholder)");
        
        // Exemplo: injetar permissões específicas
        // $content = str_replace('<manifest', '<manifest xmlns:android="http://schemas.android.com/apk/res/android"', $content);
        // file_put_contents($manifestPath, $content);
    }
    
    protected function updateProgress(BypassRule $rule, string $phase, int $progress, string $message): void
    {
        $rule->update([
            'phase' => $phase,
            'progress' => $progress,
        ]);
        $this->log($rule, $message);
    }
    
    protected function log(BypassRule $rule, string $message): void
    {
        $timestamp = now()->format('Y-m-d H:i:s');
        $logEntry = "[{$timestamp}] {$message}\n";
        
        $rule->update([
            'log' => ($rule->log ?? '') . $logEntry
        ]);
        
        Log::info("ProcessBypassJob [{$rule->id}]: {$message}");
    }
    
    protected function cleanupDirectory(string $dir): void
    {
        if (!is_dir($dir)) return;
        
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        
        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        
        rmdir($dir);
    }
}