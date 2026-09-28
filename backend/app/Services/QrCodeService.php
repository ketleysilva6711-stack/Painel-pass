<?php

namespace App\Services;

use BaconQrCode\Renderer\GDLibRenderer;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Storage;

/**
 * Geração de QR Code PNG (bacon/bacon-qr-code + GD).
 * Salva em storage/app/public/qr/{id}.png
 */
class QrCodeService
{
    public static function generate(string $content, string $relativePath, int $size = 400): string
    {
        $renderer = new GDLibRenderer($size);
        $writer = new Writer($renderer);
        $png = $writer->writeString($content);

        Storage::disk('public')->put($relativePath, $png);

        return $relativePath;
    }

    public static function pathFor(int|string $id): string
    {
        return 'qr/' . $id . '.png';
    }
}
