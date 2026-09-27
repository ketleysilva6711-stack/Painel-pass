<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('plan_id')->nullable()->after('role')->constrained('plans')->nullOnDelete();
        });
        Schema::table('play_fake_apps', function (Blueprint $table) {
            $table->unsignedBigInteger('downloads')->default(0)->after('slug');
        });
    }
    public function down(): void {
        Schema::table('play_fake_apps', function (Blueprint $table) {
            $table->dropColumn('downloads');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['plan_id']);
            $table->dropColumn('plan_id');
        });
    }
};