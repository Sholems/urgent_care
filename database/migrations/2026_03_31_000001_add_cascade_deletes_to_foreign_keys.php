<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // appointments -> doctors
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['doctor_id']);
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade');
        });

        // reviews -> appointment/app_users/doctors
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign(['appointment_id']);
            $table->dropForeign(['user_id']);
            $table->dropForeign(['doctor_id']);

            $table->foreign('appointment_id')->references('id')->on('appointments')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('app_users')->onDelete('cascade');
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade');
        });

        // service_requests -> app_users
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('app_users')->onDelete('cascade');
        });

        // vitals_reports -> nurses
        Schema::table('vitals_reports', function (Blueprint $table) {
            $table->dropForeign(['nurse_id']);
            $table->foreign('nurse_id')->references('id')->on('nurses')->onDelete('cascade');
        });

        // referrals -> doctors
        Schema::table('referrals', function (Blueprint $table) {
            $table->dropForeign(['gp_doctor_id']);
            $table->dropForeign(['specialist_doctor_id']);

            $table->foreign('gp_doctor_id')->references('id')->on('doctors')->onDelete('cascade');
            $table->foreign('specialist_doctor_id')->references('id')->on('doctors')->onDelete('cascade');
        });

        // admissions -> doctors
        Schema::table('admissions', function (Blueprint $table) {
            $table->dropForeign(['specialist_doctor_id']);
            $table->foreign('specialist_doctor_id')->references('id')->on('doctors')->onDelete('cascade');
        });

        // daily_visits -> doctors
        Schema::table('daily_visits', function (Blueprint $table) {
            $table->dropForeign(['doctor_id']);
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['doctor_id']);
            $table->foreign('doctor_id')->references('id')->on('doctors');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign(['appointment_id']);
            $table->dropForeign(['user_id']);
            $table->dropForeign(['doctor_id']);

            $table->foreign('appointment_id')->references('id')->on('appointments');
            $table->foreign('user_id')->references('id')->on('app_users');
            $table->foreign('doctor_id')->references('id')->on('doctors');
        });

        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('app_users');
        });

        Schema::table('vitals_reports', function (Blueprint $table) {
            $table->dropForeign(['nurse_id']);
            $table->foreign('nurse_id')->references('id')->on('nurses');
        });

        Schema::table('referrals', function (Blueprint $table) {
            $table->dropForeign(['gp_doctor_id']);
            $table->dropForeign(['specialist_doctor_id']);

            $table->foreign('gp_doctor_id')->references('id')->on('doctors');
            $table->foreign('specialist_doctor_id')->references('id')->on('doctors');
        });

        Schema::table('admissions', function (Blueprint $table) {
            $table->dropForeign(['specialist_doctor_id']);
            $table->foreign('specialist_doctor_id')->references('id')->on('doctors');
        });

        Schema::table('daily_visits', function (Blueprint $table) {
            $table->dropForeign(['doctor_id']);
            $table->foreign('doctor_id')->references('id')->on('doctors');
        });
    }
};

