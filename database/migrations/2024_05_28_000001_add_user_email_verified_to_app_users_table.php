<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('app_users', function (Blueprint $table) {
            if (!Schema::hasColumn('app_users', 'user_email_verified')) {
                $table->string('user_email_verified')->default('false')->after('user_phone_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_users', function (Blueprint $table) {
            if (Schema::hasColumn('app_users', 'user_email_verified')) {
                $table->dropColumn('user_email_verified');
            }
        });
    }
};
