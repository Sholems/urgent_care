<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('patient_files', function (Blueprint $table) {
      $table->id();
      $table->timestamps();

      $table->foreignId('service_request_id')->constrained('service_requests')->onDelete('cascade');
      $table->string('file_type'); // vitals, medical_report, prescription, scan_result, other
      $table->string('file_path');
      $table->string('uploaded_by_type'); // nurse, doctor
      $table->unsignedBigInteger('uploaded_by_id'); // nurse_id or doctor_id
      $table->text('description')->nullable();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::dropIfExists('patient_files');
  }
};




