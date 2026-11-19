<?php

use App\Models\SiteSetting;
use App\Support\ContactPhoneFormatter;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('site_settings')) {
            return;
        }

        $siteSetting = SiteSetting::first();

        if ($siteSetting === null) {
            return;
        }

        $resolved = ContactPhoneFormatter::resolve($siteSetting);

        $siteSetting->contact_phone = $resolved;
        $siteSetting->footer_phone = $resolved;
        $siteSetting->save();
    }

    public function down(): void
    {
        // Kept in sync intentionally.
    }
};
