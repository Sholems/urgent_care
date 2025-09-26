<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
