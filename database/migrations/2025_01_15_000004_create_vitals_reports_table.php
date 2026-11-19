<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('vitals_reports', function (Blueprint $table) {
      $table->id();
      $table->timestamps();

      $table->foreignId('service_request_id')->constrained('service_requests')->onDelete('cascade');
      $table->foreignId('nurse_id')->constrained('nurses');
      $table->json('vitals_data'); // Store vitals as JSON
      $table->string('report_file_path')->nullable();
      $table->timestamp('uploaded_at')->useCurrent();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::dropIfExists('vitals_reports');
  }
};




