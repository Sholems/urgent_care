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

        foreach (['contact_phone', 'footer_phone'] as $column) {
            $formatted = ContactPhoneFormatter::format($siteSetting->{$column});
            if ($formatted !== null && $formatted !== $siteSetting->{$column}) {
                $siteSetting->{$column} = $formatted;
            }
        }

        $siteSetting->save();
    }

    public function down(): void
    {
        // Display formatting is not reverted automatically.
    }
};
