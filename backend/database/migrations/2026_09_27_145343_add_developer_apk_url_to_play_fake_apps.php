<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('play_fake_apps', function (Blueprint $table) {
            $table->string('developer')->nullable()->after('name');
            $table->string('apk_url', 2048)->nullable()->after('package_name');
        });
    }
    public function down(): void {
        Schema::table('play_fake_apps', function (Blueprint $table) {
            $table->dropColumn(['developer', 'apk_url']);
        });
    }
};