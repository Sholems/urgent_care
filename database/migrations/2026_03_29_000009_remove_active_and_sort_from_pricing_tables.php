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
        if (Schema::hasTable('transport_options')) {
            Schema::table('transport_options', function (Blueprint $table) {
                if (Schema::hasColumn('transport_options', 'is_active')) {
                    $table->dropColumn('is_active');
                }
                if (Schema::hasColumn('transport_options', 'sort_order')) {
                    $table->dropColumn('sort_order');
                }
            });
        }

        if (Schema::hasTable('meal_options')) {
            Schema::table('meal_options', function (Blueprint $table) {
                if (Schema::hasColumn('meal_options', 'is_active')) {
                    $table->dropColumn('is_active');
                }
                if (Schema::hasColumn('meal_options', 'sort_order')) {
                    $table->dropColumn('sort_order');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('transport_options')) {
            Schema::table('transport_options', function (Blueprint $table) {
                if (!Schema::hasColumn('transport_options', 'is_active')) {
                    $table->boolean('is_active')->default(true);
                }
                if (!Schema::hasColumn('transport_options', 'sort_order')) {
                    $table->unsignedInteger('sort_order')->default(0);
                }
            });
        }

        if (Schema::hasTable('meal_options')) {
            Schema::table('meal_options', function (Blueprint $table) {
                if (!Schema::hasColumn('meal_options', 'is_active')) {
                    $table->boolean('is_active')->default(true);
                }
                if (!Schema::hasColumn('meal_options', 'sort_order')) {
                    $table->unsignedInteger('sort_order')->default(0);
                }
            });
        }
    }
};
