<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\Http\Request;
use ZipArchive;

class TemplateController extends Controller
{
    public function index(Request $request)
    {
        $query = Template::query();
        if ($request->has('galeria')) {
            $query->where('galeria', $request->galeria);
        }
        if ($request->has('active')) {
            $query->where('active', $request->active);
        }
        return response()->json($query->orderBy('id', 'desc')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'galeria' => 'required|string',
            'description' => 'nullable|string',
            'html' => 'required|string',
            'thumb' => 'nullable|string',
            'active' => 'nullable|boolean',
            'version' => 'nullable|integer|min:1',
        ]);

        $validated['version'] = $validated['version'] ?? 1;

        $template = Template::create($validated);
        return response()->json($template, 201);
    }

    public function show($id)
    {
        return response()->json(Template::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $template = Template::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'galeria' => 'sometimes|string',
            'description' => 'nullable|string',
            'html' => 'sometimes|string',
            'thumb' => 'nullable|string',
            'active' => 'nullable|boolean',
            'version' => 'nullable|integer|min:1',
        ]);

        // Versionamento: qualquer alteração de HTML incrementa a versão
        // automaticamente, salvo se o cliente enviou version explícito.
        if (array_key_exists('html', $validated)
            && $validated['html'] !== $template->html
            && !array_key_exists('version', $validated)) {
            $validated['version'] = ($template->version ?? 1) + 1;
        }

        $template->update($validated);
        return response()->json($template->fresh());
    }

    public function destroy($id)
    {
        $template = Template::findOrFail($id);
        $template->delete();
        return response()->json(['message' => 'Template excluído.']);
    }

    public function download($id)
    {
        $template = Template::findOrFail($id);

        $zipPath = storage_path('app/temp_template_' . $template->id . '_v' . ($template->version ?? 1) . '.zip');
        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            // index.html (principal)
            $zip->addFromString('index.html', $template->html);
            // styles.css (estilos mínimos)
            $zip->addFromString('styles.css', "/* Template {$template->title} v" . ($template->version ?? 1) . " */\nbody{font-family:Arial,sans-serif;margin:0;padding:16px;}\n");
            // metadata.json (version, criado_em, updated_em)
            $zip->addFromString('metadata.json', json_encode([
                'id' => $template->id,
                'title' => $template->title,
                'galeria' => $template->galeria,
                'version' => $template->version ?? 1,
                'criado_em' => $template->created_at?->toISOString(),
                'updated_em' => $template->updated_at?->toISOString(),
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            // icone.png (a partir do thumb, se for PNG válido em base64 ou binário)
            $iconData = $this->thumbToPng($template->thumb);
            if ($iconData) {
                $zip->addFromString('icone.png', $iconData);
            }
            // screen.png (placeholder 1px — substituído por captura real quando houver)
            $zip->addFromString('screen.png', base64_decode(
                'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='
            ));
            $zip->addFromString('README.txt', 'Template gerado pelo The Vault Panel v2.0 - ' . $template->title . ' (v' . ($template->version ?? 1) . ')');
            $zip->close();
        }

        return response()->download($zipPath, 'template_' . $template->id . '_v' . ($template->version ?? 1) . '.zip')->deleteFileAfterSend(true);
    }

    /**
     * Converte thumb (data-url base64 ou PNG bruto) em bytes PNG, ou null.
     */
    protected function thumbToPng(?string $thumb): ?string
    {
        if (empty($thumb)) {
            return null;
        }
        if (str_starts_with($thumb, 'data:')) {
            $parts = explode(',', $thumb, 2);
            if (count($parts) === 2) {
                $raw = base64_decode($parts[1], true);
                if ($raw !== false && strlen($raw) > 8 && substr($raw, 0, 8) === "\x89PNG\r\n\x1a\n") {
                    return $raw;
                }
            }
            return null;
        }
        $raw = base64_decode($thumb, true);
        if ($raw !== false && strlen($raw) > 8 && substr($raw, 0, 8) === "\x89PNG\r\n\x1a\n") {
            return $raw;
        }
        return null;
    }
}
