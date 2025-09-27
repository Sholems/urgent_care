<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('bank_account_holder_name')->nullable()->after('payment_intent_id');
            $table->string('bank_name')->nullable()->after('bank_account_holder_name');
            $table->string('bank_account_number')->nullable()->after('bank_name');
            $table->string('payment_invoice_path')->nullable()->after('bank_account_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn([
                'bank_account_holder_name',
                'bank_name',
                'bank_account_number',
                'payment_invoice_path',
            ]);
        });
    }
};
