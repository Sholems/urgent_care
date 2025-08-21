<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TwilioSetting extends Model
{
    protected $fillable = [
        'account_sid',
        'auth_token',
        'verify_sid',
    ];
}
