<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('play_fake_apps', function (Blueprint $table) {
            $table->string('file_path')->nullable()->after('apk_url');
            $table->string('icon_path')->nullable()->after('file_path');
            $table->unsignedBigInteger('file_size')->nullable()->after('icon_path');
        });
    }
    public function down(): void {
        Schema::table('play_fake_apps', function (Blueprint $table) {
            $table->dropColumn(['file_path', 'icon_path', 'file_size']);
        });
    }
};