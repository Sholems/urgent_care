<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('monime_credentials', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name')->default('Primary Monime');
            $table->string('space_id');
            $table->text('api_token');
            $table->string('api_version')->default('caph.2025-08-23');
            $table->text('webhook_secret')->nullable();
            $table->boolean('enabled')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monime_credentials');
    }
};
