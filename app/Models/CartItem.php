<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model {
  use HasFactory;

  protected $connection = 'mysql';
  protected $table = 'cart_items';

  protected $fillable = [
    'cart_id',
    'item_type',
    'status',
    'sort_order',
    'line_total',
    'payload',
    'pricing_breakdown',
  ];

  protected $casts = [
    'payload' => 'array',
    'pricing_breakdown' => 'array',
    'line_total' => 'float',
  ];

  public function cart() {
    return $this->belongsTo(Cart::class, 'cart_id');
  }
}
