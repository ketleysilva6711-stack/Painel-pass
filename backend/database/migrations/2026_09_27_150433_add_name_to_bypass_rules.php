<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('bypass_rules', function (Blueprint $table) {
            $table->string('name')->nullable()->after('id');
            $table->string('rule_name')->nullable()->change();
        });
    }
    public function down(): void {
        Schema::table('bypass_rules', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
};