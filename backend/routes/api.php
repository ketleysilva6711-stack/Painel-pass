<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PlayFakeController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\BypassController;
use App\Http\Controllers\BankInjectController;

Route::get('/health', function () { return response()->json(['status' => 'online', 'service' => 'The Vault API', 'timestamp' => now()]); });
Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::get('/tools/play-fake', [PlayFakeController::class, 'index']);
    Route::post('/tools/play-fake', [PlayFakeController::class, 'store']);

    Route::get('/play-fake-apps', [PlayFakeController::class, 'index']);
    Route::post('/play-fake-apps', [PlayFakeController::class, 'store']);
    Route::put('/play-fake-apps/{playFakeApp}', [PlayFakeController::class, 'update']);
    Route::delete('/play-fake-apps/{playFakeApp}', [PlayFakeController::class, 'destroy']);

    Route::get('/tools/qr-code', [QrCodeController::class, 'index']);
    Route::post('/tools/qr-code', [QrCodeController::class, 'store']);

    Route::get('/tools/bypass', [BypassController::class, 'index']);
    Route::post('/tools/bypass', [BypassController::class, 'store']);

    Route::get('/bypass-rules', [BypassController::class, 'index']);
    Route::post('/bypass-rules', [BypassController::class, 'store']);
    Route::put('/bypass-rules/{bypassRule}', [BypassController::class, 'update']);
    Route::delete('/bypass-rules/{bypassRule}', [BypassController::class, 'destroy']);

    Route::get('/tools/inject-bancos', [BankInjectController::class, 'index']);
    Route::post('/tools/inject-bancos', [BankInjectController::class, 'store']);
});