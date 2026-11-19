<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('cart_items', function (Blueprint $table) {
      $table->id();
      $table->timestamps();

      $table->foreignId('cart_id')
        ->constrained('carts')
        ->onDelete('cascade');
      $table->string('item_type', 32); // appointment | service_request
      $table->string('status', 32)->default('active');
      $table->unsignedInteger('sort_order')->default(0);
      $table->decimal('line_total', 12, 2)->default(0);
      $table->json('payload')->nullable();
      $table->json('pricing_breakdown')->nullable();

      $table->index(['cart_id', 'status']);
      $table->index(['cart_id', 'item_type']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::dropIfExists('cart_items');
  }
};
