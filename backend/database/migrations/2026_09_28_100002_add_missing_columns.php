<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'reseller_id')) {
                $table->foreignId('reseller_id')->nullable()->after('plan_id')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('users', 'plan_id')) {
                $table->foreignId('plan_id')->nullable()->after('role')->constrained('plans')->nullOnDelete();
            }
        });

        Schema::table('support_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('support_messages', 'is_admin')) {
                $table->boolean('is_admin')->default(false)->after('message');
            }
        });
    }

    public function down(): void
    {
        Schema::table('support_messages', function (Blueprint $table) {
            if (Schema::hasColumn('support_messages', 'is_admin')) {
                $table->dropColumn('is_admin');
            }
        });
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'reseller_id')) {
                $table->dropConstrainedForeignId('reseller_id');
            }
        });
    }
};
