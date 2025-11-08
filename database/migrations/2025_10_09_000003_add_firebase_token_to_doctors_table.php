<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::table('doctors', function (Blueprint $table) {
      if (!Schema::hasColumn('doctors', 'firebase_token')) {
        $table->text('firebase_token')->nullable()->after('longitude');
      }
    });
  }

  public function down(): void {
    Schema::table('doctors', function (Blueprint $table) {
      if (Schema::hasColumn('doctors', 'firebase_token')) {
        $table->dropColumn('firebase_token');
      }
    });
  }
};
