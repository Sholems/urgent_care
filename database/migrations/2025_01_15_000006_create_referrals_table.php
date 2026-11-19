<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('referrals', function (Blueprint $table) {
      $table->id();
      $table->timestamps();

      $table->foreignId('service_request_id')->constrained('service_requests')->onDelete('cascade');
      $table->foreignId('gp_doctor_id')->constrained('doctors');
      $table->foreignId('specialist_doctor_id')->constrained('doctors');
      $table->text('referral_notes')->nullable();
      $table->string('status')->default('pending'); // pending, accepted, rejected
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::dropIfExists('referrals');
  }
};




