<?php

namespace App\Http\Controllers;

use App\Models\BypassRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BypassJobController extends Controller
{
    public function show($jobId)
    {
        $job = BypassRule::findOrFail($jobId);
        
        return response()->json([
            'id' => $job->id,
            'status' => $job->status,
            'progress' => $job->progress,
            'phase' => $job->phase,
            'log' => $job->log,
            'output_path' => $job->output_path,
            'apk_filename' => $job->apk_filename,
            'target_package' => $job->target_package,
            'created_at' => $job->created_at,
            'updated_at' => $job->updated_at,
        ]);
    }
    
    public function download($jobId)
    {
        $job = BypassRule::findOrFail($jobId);
        
        if ($job->status !== 'completed') {
            return response()->json(['error' => 'Job ainda não finalizado'], 400);
        }
        
        if (!$job->output_path || !file_exists($job->output_path)) {
            return response()->json(['error' => 'Arquivo assinado não encontrado'], 404);
        }
        
        return response()->download($job->output_path, "{$job->apk_filename}.signed")->deleteFileAfterSend(true);
    }
}