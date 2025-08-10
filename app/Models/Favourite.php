<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Orchid\Screen\AsSource;
use Orchid\Filters\Filterable;
use Orchid\Filters\Types\Like;

class Favourite extends Model {
  use HasFactory;
  use AsSource;
  use Filterable;

  protected $connection = 'mysql';
  protected $table = 'favourites';

  protected $fillable = [
    'user_id',
    'doctor_id',
  ];

  protected $allowedSorts = [
    'id',
    'user_id',
    'doctor_id',
  ];

  protected $allowedFilters = [
    'id'        => Like::class,
    'user_id'   => Like::class,
    'doctor_id' => Like::class,
  ];

  public function doctor() {
    return $this->belongsTo(Doctor::class);
  }

  public function user() {
    return $this->belongsTo(AppUser::class, 'user_id');
  }
}
