<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('service_requests', function (Blueprint $table) {
      $table->id();
      $table->timestamps();

      $table->string('reference_number')->unique();
      $table->foreignId('user_id')->constrained('app_users');
      $table->string('status')->default('pending');
      $table->string('transport_mode'); // ambulance, private_car, taxi, keke, motor_bike
      
      // Patient details
      $table->string('patient_name');
      $table->string('patient_phone');
      $table->text('patient_address');
      $table->string('patient_gender');
      $table->text('symptoms')->nullable();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::dropIfExists('service_requests');
  }
};




