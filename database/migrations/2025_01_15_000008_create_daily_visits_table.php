<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('daily_visits', function (Blueprint $table) {
      $table->id();
      $table->timestamps();

      $table->foreignId('admission_id')->constrained('admissions')->onDelete('cascade');
      $table->foreignId('doctor_id')->constrained('doctors');
      $table->date('visit_date');
      $table->text('visit_notes')->nullable();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::dropIfExists('daily_visits');
  }
};




