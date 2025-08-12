<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;
use Orchid\Filters\Filterable;
use Orchid\Filters\Types\Like;

class StripeKey extends Model
{
    use HasFactory, AsSource, Filterable;

    protected $connection = 'mysql';
    protected $table = 'stripe_keys';
    protected $fillable = [
        'publishable_key',
        'secret_key',
    ];

    protected $hidden = ['created_at', 'updated_at', 'secret_key'];

    protected $allowedSorts = [
        'id',
        'updated_at',
    ];

    protected $allowedFilters = [
        'publishable_key' => Like::class,
        'updated_at' => Like::class,
    ];
}
