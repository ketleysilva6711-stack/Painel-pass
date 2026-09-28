<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use ZipArchive;

/**
 * Extração real de metadados de APK.
 *
 * Estratégia em 2 níveis:
 *  1. apktool d (preferido, quando java+apktool disponíveis) -> AndroidManifest.xml decodificado (XML texto)
 *  2. Fallback puro-PHP via ZipArchive (heurísticas: apktool.yml, nome de ícone, permissões desconhecidas)
 */
class ApkParser
{
    public static function extract(string $apkFullPath): array
    {
        $defaults = [
            'package_name' => null,
            'version_name' => null,
            'version_code' => null,
            'permissions'  => [],
            'debuggable'   => false,
            'icon_path'    => null, // caminho relativo em storage (public/icons/...) quando extraído
            'manifest_excerpt' => null,
        ];

        if (!is_file($apkFullPath)) {
            return $defaults;
        }

        // Nível 1: apktool
        try {
            $parsed = self::viaApktool($apkFullPath);
            if (!empty($parsed['package_name']) || !empty($parsed['version_name'])) {
                return array_merge($defaults, $parsed);
            }
            // mesmo sem package, aproveita o que veio (permissões/ícone)
            $defaults = array_merge($defaults, array_filter($parsed, fn ($v) => $v !== null && $v !== []));
        } catch (\Throwable $e) {
            Log::warning('ApkParser apktool falhou, usando fallback zip: ' . $e->getMessage());
        }

        // Nível 2: fallback zip
        try {
            $fallback = self::viaZip($apkFullPath);
            foreach ($fallback as $k => $v) {
                if ($v !== null && $v !== [] && empty($defaults[$k])) {
                    $defaults[$k] = $v;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('ApkParser zip falhou: ' . $e->getMessage());
        }

        return $defaults;
    }

    protected static function apktoolJar(): string
    {
        return env('APKTOOL_JAR', '/usr/local/bin/apktool.jar');
    }

    protected static function viaApktool(string $apkFullPath): array
    {
        $jar = static::apktoolJar();
        if (!is_file($jar)) {
            // tenta localizar java -jar apktool no PATH
            $which = trim((string) shell_exec('command -v apktool 2>/dev/null'));
            if ($which === '') {
                return [];
            }
        }

        $tmp = sys_get_temp_dir() . '/apkmeta_' . uniqid();
        @mkdir($tmp, 0755, true);

        try {
            if (is_file($jar)) {
                $cmd = 'java -jar ' . escapeshellarg($jar)
                    . ' d -f ' . escapeshellarg($apkFullPath)
                    . ' -o ' . escapeshellarg($tmp) . ' 2>&1';
            } else {
                $cmd = 'apktool d -f ' . escapeshellarg($apkFullPath)
                    . ' -o ' . escapeshellarg($tmp) . ' 2>&1';
            }
            shell_exec($cmd);

            $manifest = $tmp . '/AndroidManifest.xml';
            if (!is_file($manifest)) {
                return [];
            }

            $xml = @file_get_contents($manifest);
            if ($xml === false) {
                return [];
            }

            $result = [
                'package_name' => null,
                'version_name' => null,
                'version_code' => null,
                'permissions'  => [],
                'debuggable'   => false,
                'icon_file'    => null, // interno: referência android:icon
                'manifest_excerpt' => mb_substr($xml, 0, 4000),
            ];

            // Parse robusto via regex (manifest decodificado pode ter namespaces variados)
            if (preg_match('/package\s*=\s*"([^"]+)"/', $xml, $m)) {
                $result['package_name'] = $m[1];
            }
            if (preg_match('/(?:android:)?versionName\s*=\s*"([^"]+)"/', $xml, $m)) {
                $result['version_name'] = $m[1];
            }
            if (preg_match('/(?:android:)?versionCode\s*=\s*"([^"]+)"/', $xml, $m)) {
                $result['version_code'] = $m[1];
            }
            if (preg_match_all('/<uses-permission[^>]*android:name\s*=\s*"([^"]+)"/', $xml, $m)) {
                $result['permissions'] = array_values(array_unique($m[1]));
            }
            if (preg_match('/android:debuggable\s*=\s*"true"/i', $xml)) {
                $result['debuggable'] = true;
            }
            if (preg_match('/android:icon\s*=\s*"@([^"]+)"/', $xml, $m)) {
                $result['icon_file'] = $m[1]; // ex: mipmap/ic_launcher ou drawable/icon
            }

            // apktool.yml também carrega versionName/versionCode confiáveis
            $yml = $tmp . '/apktool.yml';
            if (is_file($yml)) {
                $y = @file_get_contents($yml);
                if ($y !== false) {
                    if (empty($result['version_name']) && preg_match('/versionName:\s*([^\s]+)/', $y, $m)) {
                        $result['version_name'] = trim($m[1], "'\"");
                    }
                    if (empty($result['version_code']) && preg_match('/versionCode:\s*([^\s]+)/', $y, $m)) {
                        $result['version_code'] = trim($m[1], "'\"");
                    }
                }
            }

            // Localizar ícone real no diretório decodificado
            $iconPublicPath = self::findAndStoreIcon($tmp, $result['icon_file'] ?? null);
            if ($iconPublicPath) {
                $result['icon_path'] = $iconPublicPath;
            }
            unset($result['icon_file']);

            return $result;
        } finally {
            self::rmdir($tmp);
        }
    }

    protected static function viaZip(string $apkFullPath): array
    {
        $result = ['permissions' => []];
        $zip = new ZipArchive();
        if ($zip->open($apkFullPath) !== true) {
            return $result;
        }

        try {
            // apktool.yml embutido? (APKs já processados podem conter)
            // Procurar primeiro ícone PNG plausível
            $candidates = [];
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                if ($name === false) {
                    continue;
                }
                $lower = strtolower($name);
                if (str_ends_with($lower, '.png')
                    && (str_contains($lower, 'ic_launcher') || str_contains($lower, 'icon') || str_contains($lower, 'mipmap'))) {
                    $candidates[] = $name;
                }
            }
            if (!empty($candidates)) {
                usort($candidates, function ($a, $b) {
                    // prefere xxhdpi/xhdpi e nomes sem "-round"
                    $score = fn ($n) => (str_contains($n, 'xxxhdpi') ? 0 : 0)
                        + (str_contains($n, 'xxhdpi') ? 10 : 0)
                        + (str_contains($n, 'xhdpi') ? 5 : 0)
                        - (str_contains($n, 'round') ? 3 : 0);
                    return $score($b) <=> $score($a);
                });
                $entry = $candidates[0];
                $stream = $zip->getStream($entry);
                if ($stream) {
                    $data = stream_get_contents($stream);
                    fclose($stream);
                    if ($data && strlen($data) > 100) {
                        $stored = 'icons/' . uniqid('apk_') . '.png';
                        \Illuminate\Support\Facades\Storage::disk('public')->put($stored, $data);
                        $result['icon_path'] = $stored;
                    }
                }
            }
        } finally {
            $zip->close();
        }

        return $result;
    }

    /**
     * @param string $decodedDir diretório do apktool d
     * @param string|null $iconRef ex: mipmap/ic_launcher
     */
    protected static function findAndStoreIcon(string $decodedDir, ?string $iconRef): ?string
    {
        $search = [];
        if ($iconRef) {
            // @mipmap/ic_launcher -> res/mipmap-*/ic_launcher.png
            $parts = explode('/', $iconRef, 2);
            if (count($parts) === 2) {
                [$type, $name] = $parts;
                foreach (glob($decodedDir . '/res/' . $type . '-*/' . $name . '.png') ?: [] as $f) {
                    $search[] = $f;
                }
                foreach (glob($decodedDir . '/res/' . $type . '/' . $name . '.png') ?: [] as $f) {
                    $search[] = $f;
                }
            }
        }
        // fallback: qualquer ic_launcher/icon em res
        if (empty($search)) {
            foreach (['ic_launcher.png', 'icon.png'] as $n) {
                foreach (glob($decodedDir . '/res/*/' . $n) ?: [] as $f) {
                    $search[] = $f;
                }
            }
        }
        if (empty($search)) {
            return null;
        }
        // prefere maior densidade
        usort($search, function ($a, $b) {
            $score = fn ($p) => (str_contains($p, 'xxxhdpi') ? 30 : 0)
                + (str_contains($p, 'xxhdpi') ? 20 : 0)
                + (str_contains($p, 'xhdpi') ? 10 : 0)
                + (str_contains($p, 'hdpi') ? 5 : 0)
                + filesize($p) / 100000;
            return $score($b) <=> $score($a);
        });

        $best = $search[0];
        $data = @file_get_contents($best);
        if ($data === false || strlen($data) < 100) {
            return null;
        }
        $stored = 'icons/' . uniqid('apk_') . '.png';
        \Illuminate\Support\Facades\Storage::disk('public')->put($stored, $data);
        return $stored;
    }

    protected static function rmdir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($it as $file) {
            $path = $file->getRealPath() ?: $file->getPathname();
            $file->isDir() ? @rmdir($path) : @unlink($path);
        }
        @rmdir($dir);
    }
}
