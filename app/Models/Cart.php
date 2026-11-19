<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model {
  use HasFactory;

  protected $connection = 'mysql';
  protected $table = 'carts';

  protected $fillable = [
    'user_id',
    'currency',
    'subtotal',
    'total',
    'totals_breakdown',
  ];

  protected $casts = [
    'totals_breakdown' => 'array',
    'subtotal' => 'float',
    'total' => 'float',
  ];

  public function user() {
    return $this->belongsTo(AppUser::class, 'user_id');
  }

  public function items() {
    return $this->hasMany(CartItem::class)->orderBy('sort_order')->orderBy('id');
  }
}
