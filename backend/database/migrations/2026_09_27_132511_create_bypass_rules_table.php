<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('bypass_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('rule_name');
            $table->string('target_package');
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('bypass_rules'); }
};