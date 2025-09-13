<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;
use Orchid\Filters\Filterable;
use Orchid\Filters\Types\Like;

class ExchangeRate extends Model
{
    use HasFactory, AsSource, Filterable;

    protected $connection = 'mysql';
    protected $table = 'exchange_rates';

    protected $fillable = [
        'usd_to_sll',
    ];

    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $allowedSorts = [
        'id',
        'updated_at',
    ];

    protected $allowedFilters = [
        'usd_to_sll' => Like::class,
        'updated_at' => Like::class,
    ];
}
