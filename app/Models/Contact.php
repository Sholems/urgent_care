<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    // Table name (optional if it follows Laravel convention)
    protected $table = 'contacts';

    // Fillable fields for mass assignment
    protected $fillable = [
        'name',
        'email',
        'phone',
        'description',
    ];
}
