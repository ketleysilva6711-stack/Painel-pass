<?php

namespace App\Http\Controllers;

use App\Models\BypassRule;
use Illuminate\Http\Request;

class BypassJobController extends Controller
{
    public function show($jobId)
    {
        $job = BypassRule::with('apk')->findOrFail($jobId);

        return response()->json([
            'id' => $job->id,
            'status' => $job->status,
            'progress' => $job->progress,
            'phase' => $job->phase,
            'log' => $job->log,
            'patch_applied' => $job->patch_applied,
            'output_path' => $job->output_path,
            'apk_filename' => $job->apk_filename,
            'target_package' => $job->target_package,
            'created_at' => $job->created_at,
            'updated_at' => $job->updated_at,
        ]);
    }

    public function download(Request $request, $jobId)
    {
        $job = BypassRule::findOrFail($jobId);

        if ($job->status !== 'completed') {
            return response()->json(['error' => 'Job ainda não finalizado'], 400);
        }

        if (!$job->output_path) {
            return response()->json(['error' => 'Arquivo assinado não encontrado'], 404);
        }

        // output_path é relativo ao disco public (ex: bypass_output/1_app.signed.apk).
        // Compat: se for absoluto legado, usa direto.
        $full = str_starts_with($job->output_path, '/') || preg_match('/^[A-Za-z]:\\\\/', $job->output_path)
            ? $job->output_path
            : storage_path('app/public/' . ltrim($job->output_path, '/'));

        if (!is_file($full)) {
            return response()->json(['error' => 'Arquivo assinado não encontrado'], 404);
        }

        $downloadName = $job->apk_filename
            ? pathinfo($job->apk_filename, PATHINFO_FILENAME) . '.signed.apk'
            : basename($full);

        // NÃO apagar após o 1º download: o artefato deve permanecer
        // disponível para re-downloads/auditoria. Limpeza via rotina própria.
        // Parâmetro ?delete=1 permite remoção explícita (ex: admin).
        $deleteAfterSend = $request->boolean('delete', false);

        return response()->download($full, $downloadName)->deleteFileAfterSend($deleteAfterSend);
    }
}
