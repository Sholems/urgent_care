<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Filters\Types\Like;
use Orchid\Screen\AsSource;

class MonimeCredential extends Model
{
    use HasFactory;
    use AsSource;
    use Filterable;

    protected $connection = 'mysql';
    protected $table = 'monime_credentials';

    protected $fillable = [
        'name',
        'space_id',
        'api_token',
        'api_version',
        'webhook_secret',
        'enabled',
    ];

    protected $casts = [
        'api_token' => 'encrypted',
        'webhook_secret' => 'encrypted',
        'enabled' => 'boolean',
    ];

    protected $hidden = [
        'api_token',
        'webhook_secret',
    ];

    protected $allowedSorts = [
        'id',
        'updated_at',
    ];

    protected $allowedFilters = [
        'name' => Like::class,
        'space_id' => Like::class,
        'api_version' => Like::class,
        'updated_at' => Like::class,
    ];
}
