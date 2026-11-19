<?php

use App\Models\SiteSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const NEW_PHONE = '+232 72 735 530 / +232 78 100 017 / +232 30 439 975';

    /** @var list<string> */
    private const OLD_PHONES = [
        '+1 (240) 476-9840',
        '+12404769840',
        '12404769840',
        '+232 72 73 5530/+232 78 10 0017/+232 88 43 9975',
        '+232 72 73 5530 / +232 78 10 0017 / +232 88 43 9975',
    ];

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
            $current = $siteSetting->{$column};

            if ($current === null || $current === '' || $this->isLegacyPhone($current)) {
                $siteSetting->{$column} = self::NEW_PHONE;
            }
        }

        $siteSetting->save();
    }

    public function down(): void
    {
        // Phone numbers are site-specific; no automatic rollback.
    }

    private function isLegacyPhone(string $phone): bool
    {
        if (in_array($phone, self::OLD_PHONES, true)) {
            return true;
        }

        $normalized = preg_replace('/\D+/', '', $phone) ?? '';

        return str_contains($phone, '476-9840')
            || str_contains($phone, '4769840')
            || $normalized === '12404769840';
    }
};
