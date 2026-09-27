<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Modificar enum phase para incluir 'completed'
        DB::statement("ALTER TABLE bypass_rules MODIFY COLUMN phase ENUM('decompile', 'patch', 'recompile', 'sign', 'completed') NULL DEFAULT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE bypass_rules MODIFY COLUMN phase ENUM('decompile', 'patch', 'recompile', 'sign') NULL DEFAULT NULL");
    }
};