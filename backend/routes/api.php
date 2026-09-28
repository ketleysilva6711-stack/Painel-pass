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
use App\Http\Controllers\FinanceiroController;
use App\Http\Controllers\EncerramentoController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\TutorialController;
use App\Http\Controllers\ServicoController;
use App\Http\Controllers\RevendedorController;
use App\Http\Controllers\AdminSupportController;

Route::get('/health', function () { return response()->json(['status' => 'online', 'service' => 'The Vault API', 'timestamp' => now()]); });
Route::post('/auth/login', [AuthController::class, 'login']);
Route::get('/c/{slug}', [PublicAppController::class, 'show']);
// PNG antes do redirect genérico: /q/{id} capturaria "10.png" como id.
Route::get('/q/{id}.png', [QrCodeController::class, 'png'])->where('id', '[0-9]+');
Route::get('/q/{id}', [QrCodeController::class, 'redirect'])->where('id', '[0-9]+');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // 2FA TOTP routes
    Route::get('/auth/totp-secret', [AuthController::class, 'totpSecret']);
    Route::post('/auth/totp-enable', [AuthController::class, 'enableTotp']);
    Route::post('/auth/totp-disable', [AuthController::class, 'disableTotp']);
    Route::post('/auth/verify-totp', [AuthController::class, 'verifyTOTP']);

    // Play Fake
    Route::get('/tools/play-fake', [PlayFakeController::class, 'index']);
    Route::post('/tools/play-fake', [PlayFakeController::class, 'store']);
    Route::get('/play-fake-apps', [PlayFakeController::class, 'index']);
    Route::post('/play-fake-apps', [PlayFakeController::class, 'store']);
    Route::put('/play-fake-apps/{playFakeApp}', [PlayFakeController::class, 'update']);
    Route::delete('/play-fake-apps/{playFakeApp}', [PlayFakeController::class, 'destroy']);

    // QR Code
    Route::get('/tools/qr-code', [QrCodeController::class, 'index']);
    Route::post('/tools/qr-code', [QrCodeController::class, 'store']);
    Route::get('/qr-code-links', [QrCodeController::class, 'index']);
    Route::post('/qr-code-links', [QrCodeController::class, 'store']);
    Route::put('/qr-code-links/{qrCodeLink}', [QrCodeController::class, 'update']);
    Route::delete('/qr-code-links/{qrCodeLink}', [QrCodeController::class, 'destroy']);

    // Bypass
    Route::get('/tools/bypass', [BypassController::class, 'index']);
    Route::post('/tools/bypass', [BypassController::class, 'store']);
    Route::get('/bypass-rules', [BypassController::class, 'index']);
    Route::post('/bypass-rules', [BypassController::class, 'store']);
    Route::put('/bypass-rules/{bypassRule}', [BypassController::class, 'update']);
    Route::delete('/bypass-rules/{bypassRule}', [BypassController::class, 'destroy']);
    Route::get('/bypass/jobs/{job}', [BypassJobController::class, 'show']);
    Route::get('/bypass/jobs/{job}/download', [BypassJobController::class, 'download']);

    // Bank Injects
    Route::get('/tools/inject-bancos', [BankInjectController::class, 'index']);
    Route::post('/tools/inject-bancos', [BankInjectController::class, 'store']);
    Route::get('/tools/inject-bancos/{id}/download', [BankInjectController::class, 'download']);
    Route::get('/bank-injects', [BankInjectController::class, 'index']);
    Route::post('/bank-injects', [BankInjectController::class, 'store']);
    Route::put('/bank-injects/{bankInject}', [BankInjectController::class, 'update']);
    Route::delete('/bank-injects/{bankInject}', [BankInjectController::class, 'destroy']);
    Route::post('/bank-injects/{bankInject}/build-pack', [BankInjectController::class, 'buildPack']);

    // Templates (Full CRUD)
    Route::get('/templates', [TemplateController::class, 'index']);
    Route::post('/templates', [TemplateController::class, 'store']);
    Route::get('/templates/{id}', [TemplateController::class, 'show']);
    Route::put('/templates/{id}', [TemplateController::class, 'update']);
    Route::delete('/templates/{id}', [TemplateController::class, 'destroy']);
    Route::get('/templates/{id}/download', [TemplateController::class, 'download']);

    // Ranking (Public podio + Admin CRUD)
    Route::get('/ranking/podio', [RankingController::class, 'podio']);
    Route::middleware(['role.admin', 'twofactorauth'])->group(function () {
        Route::get('/ranking', [RankingController::class, 'index']);
        Route::post('/ranking', [RankingController::class, 'store']);
        Route::put('/ranking/{id}', [RankingController::class, 'update']);
        Route::delete('/ranking/{id}', [RankingController::class, 'destroy']);
    });

    // Plans CRUD
    Route::middleware(['role.admin', 'twofactorauth'])->group(function () {
        Route::get('/planos', [PlanController::class, 'index']);
        Route::post('/planos', [PlanController::class, 'store']);
        Route::get('/planos/{id}', [PlanController::class, 'show']);
        Route::put('/planos/{id}', [PlanController::class, 'update']);
        Route::delete('/planos/{id}', [PlanController::class, 'destroy']);
    });

    // Tutorials CRUD (Admin)
    Route::middleware(['role.admin', 'twofactorauth'])->group(function () {
        Route::get('/tutoriais', [TutorialController::class, 'index']);
        Route::post('/tutoriais', [TutorialController::class, 'store']);
        Route::get('/tutoriais/{id}', [TutorialController::class, 'show']);
        Route::put('/tutoriais/{id}', [TutorialController::class, 'update']);
        Route::delete('/tutoriais/{id}', [TutorialController::class, 'destroy']);
    });
    Route::middleware(['role.revendedor', 'twofactorauth'])->group(function () {
        Route::get('/revendedor/tutoriais', [TutorialController::class, 'revendedorIndex']);
        Route::post('/revendedor/tutoriais', [TutorialController::class, 'revendedorStore']);
        Route::get('/revendedor/tutoriais/{id}', [TutorialController::class, 'revendedorShow']);
        Route::put('/revendedor/tutoriais/{id}', [TutorialController::class, 'revendedorUpdate']);
        Route::delete('/revendedor/tutoriais/{id}', [TutorialController::class, 'revendedorDestroy']);
    });

    // Servicos CRUD
    Route::middleware(['role.admin', 'twofactorauth'])->group(function () {
        Route::get('/servicos', [ServicoController::class, 'index']);
        Route::post('/servicos', [ServicoController::class, 'store']);
        Route::get('/servicos/{id}', [ServicoController::class, 'show']);
        Route::put('/servicos/{id}', [ServicoController::class, 'update']);
        Route::delete('/servicos/{id}', [ServicoController::class, 'destroy']);
    });

    // Admin middleware group
    Route::middleware(['role.admin', 'twofactorauth'])->group(function () {
        Route::get('/admin/users-list', [AdminUserController::class, 'index']);
        Route::put('/admin/users-list/{id}', [AdminUserController::class, 'update']);
        Route::get('/admin/logs', [LogController::class, 'index']);

        // Financeiro
        Route::get('/financeiro', [FinanceiroController::class, 'index']);

        // Encerramento
        Route::get('/encerramento', [EncerramentoController::class, 'index']);
        Route::post('/encerramento', [EncerramentoController::class, 'store']);
        Route::put('/encerramento/{id}', [EncerramentoController::class, 'update']);
        Route::post('/encerramento/warning', [EncerramentoController::class, 'warning']);
        Route::post('/encerramento/shutdown', [EncerramentoController::class, 'shutdown']);

        // Admin Support conversations
        Route::get('/admin/suporte/{id}', [AdminSupportController::class, 'show']);
        Route::post('/admin/suporte/{id}/messages', [AdminSupportController::class, 'addMessage']);
    });

    // Revendedor routes
    Route::middleware(['role.revendedor', 'twofactorauth'])->group(function () {
        Route::get('/revendedor/usuarios', [RevendedorController::class, 'usuarios']);
        Route::post('/revendedor/usuarios', [RevendedorController::class, 'storeUsuario']);
        Route::get('/revendedor/usuarios/{id}', [RevendedorController::class, 'showUsuario']);
        Route::put('/revendedor/usuarios/{id}', [RevendedorController::class, 'updateUsuario']);

        Route::get('/revendedor/suporte/{conversationId}', [RevendedorController::class, 'suporteShow']);
        Route::post('/revendedor/suporte/{conversationId}/messages', [RevendedorController::class, 'suporteAddMessage']);
    });

    // Public Support
    Route::get('/support/tickets', [SupportController::class, 'index']);
    Route::post('/support/tickets', [SupportController::class, 'store']);

    // Consultas
    Route::get('/consultas', [ConsultaController::class, 'index']);
    Route::post('/consultas', [ConsultaController::class, 'store']);
});
