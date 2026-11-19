<?php

namespace App\Support;

use App\Models\SiteSetting;

class ContactPhoneFormatter
{
    /**
     * Resolve the public contact phone from site settings (same source everywhere).
     */
    public static function resolve(?SiteSetting $siteSetting = null): string
    {
        $raw = $siteSetting?->contact_phone
            ?: $siteSetting?->footer_phone
            ?: config('site.default_phone');

        return self::format($raw) ?? (string) $raw;
    }

    /**
     * Resolve phone from admin form input; contact field takes priority over footer field.
     */
    public static function resolveFromInput(?string $contactPhone, ?string $footerPhone): ?string
    {
        $raw = null;

        if ($contactPhone !== null && trim($contactPhone) !== '') {
            $raw = trim($contactPhone);
        } elseif ($footerPhone !== null && trim($footerPhone) !== '') {
            $raw = trim($footerPhone);
        }

        if ($raw === null) {
            return null;
        }

        return self::format($raw);
    }

    /**
     * Format one or more contact numbers for display (e.g. "+232 72 735 530 / +232 78 100 017").
     */
    public static function format(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return $value;
        }

        $parts = preg_split('#\s*/\s*#', trim($value)) ?: [];
        $formatted = [];

        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }

            $single = self::formatSingle($part);
            if ($single !== null) {
                $formatted[] = $single;
            }
        }

        return $formatted === [] ? $value : implode(' / ', $formatted);
    }

    private static function formatSingle(string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if ($digits === '') {
            return null;
        }

        // Sierra Leone: country code 232 + 8-digit national number.
        if (str_starts_with($digits, '232') && strlen($digits) === 11) {
            $national = substr($digits, 3);

            return sprintf(
                '+232 %s %s %s',
                substr($national, 0, 2),
                substr($national, 2, 3),
                substr($national, 5, 3)
            );
        }

        return trim($phone);
    }
}
