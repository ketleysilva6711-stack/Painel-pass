<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tutorials')) {
            Schema::create('tutorials', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('description');
                $table->string('category')->nullable();
                $table->string('video_url', 500)->nullable();
                $table->string('thumbnail_url', 500)->nullable();
                $table->text('content')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
            return;
        }
        Schema::table('tutorials', function (Blueprint $table) {
            if (!Schema::hasColumn('tutorials', 'title')) {
                $table->string('title')->after('id');
            }
            if (!Schema::hasColumn('tutorials', 'description')) {
                $table->text('description')->nullable()->after('title');
            }
            if (!Schema::hasColumn('tutorials', 'category')) {
                $table->string('category')->nullable()->after('description');
            }
            if (!Schema::hasColumn('tutorials', 'video_url')) {
                $table->string('video_url', 500)->nullable()->after('category');
            }
            if (!Schema::hasColumn('tutorials', 'thumbnail_url')) {
                $table->string('thumbnail_url', 500)->nullable()->after('video_url');
            }
            if (!Schema::hasColumn('tutorials', 'content')) {
                $table->text('content')->nullable()->after('thumbnail_url');
            }
            if (!Schema::hasColumn('tutorials', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('content')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tutorials');
    }
};
