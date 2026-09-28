<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicAppController;

Route::get('/', function () {
    return view('welcome');
});

// Página pública estilo Play Store (HTML). A versão JSON segue em /api/c/{slug}.
Route::get('/c/{slug}', [PublicAppController::class, 'show']);
