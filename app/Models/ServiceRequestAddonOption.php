<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Filters\Types\Like;
use Orchid\Screen\AsSource;

class ServiceRequestAddonOption extends Model
{
    use AsSource;
    use HasFactory;
    use Filterable;

    protected $fillable = [
        'code',
        'label',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    protected $allowedSorts = [
        'id',
        'label',
        'amount',
        'updated_at',
    ];

    protected $allowedFilters = [
        'id' => Like::class,
        'code' => Like::class,
        'label' => Like::class,
    ];
}
