<?php

namespace App\Models;

use App\Support\ContactPhoneFormatter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SiteSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'header_logo',
        'footer_logo',
        'footer_phone',
        'footer_email',
        'contact_phone',
        'contact_email',
        'opening_hours',
        'transport_pricing',
        'meal_pricing',
        'meals_per_day',
    ];

    protected $casts = [
        'opening_hours' => 'array',
        'transport_pricing' => 'array',
        'meal_pricing' => 'array',
        'meals_per_day' => 'decimal:2',
    ];

    public function getHeaderLogoUrlAttribute(): ?string
    {
        return $this->resolveLogoUrl($this->header_logo);
    }

    public function getFooterLogoUrlAttribute(): ?string
    {
        return $this->resolveLogoUrl($this->footer_logo);
    }

    public function getResolvedHeaderLogoUrlAttribute(): string
    {
        return $this->header_logo_url ?? self::defaultLogoUrl();
    }

    public function getResolvedFooterLogoUrlAttribute(): string
    {
        return $this->footer_logo_url ?? self::defaultLogoUrl();
    }

    public static function defaultLogoUrl(): string
    {
        return asset(config('site.default_logo', 'img/logo.png'));
    }

    public function resolvedContactPhone(): string
    {
        return ContactPhoneFormatter::resolve($this);
    }

    protected function resolveLogoUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        $normalizedPath = ltrim($path, '\\/');

        if (Str::startsWith($normalizedPath, 'public/')) {
            $normalizedPath = Str::replaceFirst('public/', 'storage/', $normalizedPath);
        }

        if (!Str::startsWith($normalizedPath, 'storage/')) {
            $normalizedPath = 'storage/' . $normalizedPath;
        }

        $relativePath = Str::after($normalizedPath, 'storage/');

        if (!Storage::disk('public')->exists($relativePath)) {
            return null;
        }

        return asset($normalizedPath);
    }
}
