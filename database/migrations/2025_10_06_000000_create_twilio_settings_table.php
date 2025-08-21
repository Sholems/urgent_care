<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('twilio_settings', function (Blueprint $table) {
            $table->id();
            $table->string('account_sid')->nullable();
            $table->string('auth_token')->nullable();
            $table->string('verify_sid')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('twilio_settings');
    }
};
