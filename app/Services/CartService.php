<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartService {
  public function __construct(
    private CartPricingService $cartPricingService
  ) {
  }

  public function getUserCart(int $userId): Cart {
    $cart = Cart::firstOrCreate(
      ['user_id' => $userId],
      ['currency' => (string) config('cart_pricing.currency', 'SLE')]
    );

    $this->recalculateTotals($cart);

    return $cart->fresh(['items']);
  }

  public function addItem(int $userId, string $itemType, array $payload): Cart {
    $cart = $this->getUserCart($userId);
    $pricing = $this->cartPricingService->calculate($itemType, $payload);

    $maxOrder = (int) $cart->items()->max('sort_order');

    $cart->items()->create([
      'item_type' => $itemType,
      'status' => 'active',
      'sort_order' => $maxOrder + 1,
      'line_total' => $pricing['line_total'],
      'payload' => $pricing['payload'],
      'pricing_breakdown' => $pricing['pricing_breakdown'],
    ]);

    $this->recalculateTotals($cart);

    return $cart->fresh(['items']);
  }

  public function updateItem(int $userId, int $itemId, array $attributes): Cart {
    $cart = $this->getUserCart($userId);
    /** @var CartItem|null $item */
    $item = $cart->items()->where('id', $itemId)->first();

    if (!$item) {
      throw ValidationException::withMessages([
        'item_id' => ['Cart item was not found for this user.'],
      ]);
    }

    $itemType = (string) ($attributes['item_type'] ?? $item->item_type);
    $payload = (array) ($attributes['payload'] ?? $item->payload ?? []);
    $status = (string) ($attributes['status'] ?? $item->status);

    $pricing = $this->cartPricingService->calculate($itemType, $payload);

    $item->update([
      'item_type' => $itemType,
      'status' => $status,
      'line_total' => $pricing['line_total'],
      'payload' => $pricing['payload'],
      'pricing_breakdown' => $pricing['pricing_breakdown'],
      'sort_order' => (int) ($attributes['sort_order'] ?? $item->sort_order),
    ]);

    $this->recalculateTotals($cart);

    return $cart->fresh(['items']);
  }

  public function removeItem(int $userId, int $itemId): Cart {
    $cart = $this->getUserCart($userId);
    $deleted = $cart->items()->where('id', $itemId)->delete();

    if (!$deleted) {
      throw ValidationException::withMessages([
        'item_id' => ['Cart item was not found for this user.'],
      ]);
    }

    $this->recalculateTotals($cart);

    return $cart->fresh(['items']);
  }

  public function clearCart(Cart $cart): void {
    DB::transaction(function () use ($cart) {
      $cart->items()->delete();
      $cart->update([
        'subtotal' => 0,
        'total' => 0,
        'totals_breakdown' => [],
      ]);
    });
  }

  public function recalculateTotals(Cart $cart): Cart {
    $activeItems = $cart->items()->where('status', 'active')->get();
    $subtotal = (float) $activeItems->sum('line_total');

    $cart->update([
      'subtotal' => $subtotal,
      'total' => $subtotal,
      'totals_breakdown' => [
        [
          'code' => 'subtotal',
          'label' => 'Subtotal',
          'amount' => $subtotal,
        ],
      ],
    ]);

    return $cart->fresh(['items']);
  }
}
