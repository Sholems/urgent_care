<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::table('app_users', function (Blueprint $table) {
      if (!Schema::hasColumn('app_users', 'firebase_token')) {
        $table->text('firebase_token')->nullable()->after('insurance_details');
      }
    });
  }

  public function down(): void {
    Schema::table('app_users', function (Blueprint $table) {
      if (Schema::hasColumn('app_users', 'firebase_token')) {
        $table->dropColumn('firebase_token');
      }
    });
  }
};
