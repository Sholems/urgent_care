<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('request_assignments', function (Blueprint $table) {
      $table->id();
      $table->timestamps();

      $table->foreignId('service_request_id')->constrained('service_requests')->onDelete('cascade');
      $table->string('assigned_to_type'); // nurse, gp, specialist
      $table->unsignedBigInteger('assigned_to_id'); // nurse_id, doctor_id
      $table->timestamp('assigned_at')->useCurrent();
      $table->string('status')->default('active'); // active, completed, cancelled
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::dropIfExists('request_assignments');
  }
};




