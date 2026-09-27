<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('tipo');
            $table->string('categoria')->nullable();
            $table->string('ator')->nullable();
            $table->text('descricao');
            $table->json('metadata')->nullable();
            $table->string('ip')->nullable();
            $table->timestamps();
            $table->index(['tipo', 'created_at']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('logs');
    }
};