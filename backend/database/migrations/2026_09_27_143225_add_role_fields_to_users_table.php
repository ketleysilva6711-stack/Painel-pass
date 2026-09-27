<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'revendedor', 'user'])->default('user')->after('email');
            $table->json('funcionalidades')->nullable()->after('role');
            $table->string('conta_status')->default('active')->after('funcionalidades');
            $table->boolean('reseller_disponivel')->nullable()->after('conta_status');
            $table->boolean('pode_servicos_externos')->default(false)->after('reseller_disponivel');
            $table->timestamp('conta_expira_em')->nullable()->after('pode_servicos_externos');
        });
    }
    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role','funcionalidades','conta_status','reseller_disponivel','pode_servicos_externos','conta_expira_em']);
        });
    }
};