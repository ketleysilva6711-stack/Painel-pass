<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bypass_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('apk_path');
            $table->string('output_path')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->integer('progress')->default(0);
            $table->enum('phase', ['decompile', 'patch', 'recompile', 'sign'])->nullable();
            $table->text('log')->nullable();
            $table->string('target_package');
            $table->string('apk_filename');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bypass_jobs');
    }
};