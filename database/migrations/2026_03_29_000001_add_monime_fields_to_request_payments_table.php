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
        Schema::table('request_payments', function (Blueprint $table) {
            $table->string('payment_provider')->nullable()->after('payment_method'); // stripe, monime, offline, cash
            $table->string('monime_checkout_session_id')->nullable()->after('payment_provider');
            $table->string('monime_payment_id')->nullable()->after('monime_checkout_session_id');
            $table->string('monime_order_number')->nullable()->after('monime_payment_id');
            $table->json('provider_payload')->nullable()->after('monime_order_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_payments', function (Blueprint $table) {
            $table->dropColumn([
                'payment_provider',
                'monime_checkout_session_id',
                'monime_payment_id',
                'monime_order_number',
                'provider_payload',
            ]);
        });
    }
};
