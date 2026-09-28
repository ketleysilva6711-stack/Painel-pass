<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fase 2 — o ProcessBypassJob usa a fase "align" (zipalign) e pode usar
     * "verify" (apksigner verify), mas o ENUM original só tinha
     * decompile/patch/recompile/sign/completed. Sem 'align', o update de
     * progresso a 70% quebra com SQL error e o job falha.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return; // SQLite (testes) não impõe ENUM
        }

        DB::statement(
            "ALTER TABLE `bypass_rules` MODIFY `phase` " .
            "ENUM('decompile','patch','recompile','align','sign','completed') NULL"
        );
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // Volta ao ENUM anterior apenas se nenhuma regra usa 'align'/'verify'
        $inUse = DB::table('bypass_rules')
            ->whereIn('phase', ['align', 'verify'])
            ->exists();

        if (!$inUse) {
            DB::statement(
                "ALTER TABLE `bypass_rules` MODIFY `phase` " .
                "ENUM('decompile','patch','recompile','sign','completed') NULL"
            );
        }
    }
};
