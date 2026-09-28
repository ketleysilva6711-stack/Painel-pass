<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('servicos')) {
            Schema::create('servicos', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description');
                $table->decimal('price', 10, 2)->default(0);
                $table->integer('duration_days')->nullable();
                $table->string('icon', 255)->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
            return;
        }
        Schema::table('servicos', function (Blueprint $table) {
            if (!Schema::hasColumn('servicos', 'name')) {
                $table->string('name')->after('id');
            }
            if (!Schema::hasColumn('servicos', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
            if (!Schema::hasColumn('servicos', 'price')) {
                $table->decimal('price', 10, 2)->default(0)->after('description');
            }
            if (!Schema::hasColumn('servicos', 'duration_days')) {
                $table->integer('duration_days')->nullable()->after('price');
            }
            if (!Schema::hasColumn('servicos', 'icon')) {
                $table->string('icon', 255)->nullable()->after('duration_days');
            }
            if (!Schema::hasColumn('servicos', 'active')) {
                $table->boolean('active')->default(true)->after('icon');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servicos');
    }
};
