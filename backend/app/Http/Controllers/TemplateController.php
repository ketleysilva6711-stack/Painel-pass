<?php
namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\Http\Request;
use ZipArchive;

class TemplateController extends Controller
{
    public function index(Request $request)
    {
        $query = Template::where('active', true);
        if ($request->has('galeria')) {
            $query->where('galeria', $request->galeria);
        }
        return response()->json($query->get());
    }

    public function show($id)
    {
        return response()->json(Template::findOrFail($id));
    }

    public function download($id)
    {
        $template = Template::findOrFail($id);

        $zipPath = storage_path('app/temp_template_' . $template->id . '.zip');
        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $zip->addFromString('index.html', $template->html);
            $zip->addFromString('README.txt', 'Template gerado pelo The Vault Panel v2.0 - ' . $template->title);
            $zip->close();
        }

        return response()->download($zipPath, 'template_' . $template->id . '.zip')->deleteFileAfterSend(true);
    }
}