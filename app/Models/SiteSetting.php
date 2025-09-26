<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
    ];

    protected $casts = [
        'opening_hours' => 'array',
    ];

    public function getHeaderLogoUrlAttribute(): ?string
    {
        return $this->resolveLogoUrl($this->header_logo);
    }

    public function getFooterLogoUrlAttribute(): ?string
    {
        return $this->resolveLogoUrl($this->footer_logo);
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

        return asset($normalizedPath);
    }
}
