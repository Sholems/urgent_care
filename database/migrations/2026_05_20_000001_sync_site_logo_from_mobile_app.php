<?php

use App\Models\SiteSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('site_settings')) {
            return;
        }

        $sourceLogo = public_path('img/logo.png');

        if (!is_readable($sourceLogo)) {
            return;
        }

        Storage::disk('public')->put(
            'lizelsundria-logo.png',
            file_get_contents($sourceLogo)
        );

        $siteSetting = SiteSetting::first();

        if ($siteSetting === null) {
            return;
        }

        $siteSetting->header_logo = 'storage/lizelsundria-logo.png';
        $siteSetting->footer_logo = 'storage/lizelsundria-logo.png';
        $siteSetting->save();
    }

    public function down(): void
    {
        Storage::disk('public')->delete('lizelsundria-logo.png');
    }
};
