<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bypass_rules', function (Blueprint $table) {
            // apk_path, output_path, status já existem
            if (!Schema::hasColumn('bypass_rules', 'progress')) {
                $table->integer('progress')->default(0)->after('status');
            }
            if (!Schema::hasColumn('bypass_rules', 'phase')) {
                $table->enum('phase', ['decompile', 'patch', 'recompile', 'sign'])->nullable()->after('progress');
            }
            if (!Schema::hasColumn('bypass_rules', 'log')) {
                $table->text('log')->nullable()->after('phase');
            }
            if (!Schema::hasColumn('bypass_rules', 'apk_filename')) {
                $table->string('apk_filename')->nullable()->after('log');
            }
            if (!Schema::hasColumn('bypass_rules', 'apk_id')) {
                $table->foreignId('apk_id')->nullable()->constrained('play_fake_apps')->onDelete('set null')->after('apk_filename');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bypass_rules', function (Blueprint $table) {
            if (Schema::hasColumn('bypass_rules', 'apk_id')) {
                $table->dropForeign(['apk_id']);
            }
            $table->dropColumn(['progress', 'phase', 'log', 'apk_filename', 'apk_id']);
        });
    }
};