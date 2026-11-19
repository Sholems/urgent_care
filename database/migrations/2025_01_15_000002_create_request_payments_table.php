<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('request_payments', function (Blueprint $table) {
      $table->id();
      $table->timestamps();

      $table->foreignId('service_request_id')->constrained('service_requests')->onDelete('cascade');
      $table->string('payment_type'); // consultation, observation, meals, specialist, transport
      $table->decimal('amount', 10, 2);
      $table->string('payment_status')->default('pending'); // pending, confirmed, failed
      $table->string('payment_intent_id')->nullable();
      $table->string('payment_method')->nullable(); // card, offline, cash
      $table->text('notes')->nullable();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::dropIfExists('request_payments');
  }
};




