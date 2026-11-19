<?php

namespace App\Http\Controllers;

use App\Services\CartCheckoutService;
use App\Services\CartService;
use Illuminate\Http\Request;
use RuntimeException;

class CartController extends Controller {
  public function __construct(
    private CartService $cartService,
    private CartCheckoutService $cartCheckoutService
  ) {
  }

  public function show(Request $request) {
    $validated = $request->validate([
      'user_id' => 'required|integer|exists:app_users,id',
    ]);

    $cart = $this->cartService->getUserCart((int) $validated['user_id']);

    return response()->json($this->cartResponse($cart));
  }

  public function addItem(Request $request) {
    $validated = $request->validate([
      'user_id' => 'required|integer|exists:app_users,id',
      'item_type' => 'required|in:appointment,service_request',
      'payload' => 'required|array',
    ]);

    if ($validated['item_type'] === 'appointment') {
      $request->validate([
        'payload.date' => 'required|date',
        'payload.time' => 'required|string',
        'payload.doctor_id' => 'required|integer|exists:doctors,id',
      ]);
    } else {
      $request->validate([
        'payload.transport_mode' => 'required|in:ambulance,private_car,taxi,keke,motor_bike',
        'payload.patient_name' => 'required|string|max:255',
        'payload.patient_phone' => 'required|string|max:20',
        'payload.patient_address' => 'required|string',
        'payload.patient_gender' => 'required|in:male,female,other',
      ]);
    }

    $cart = $this->cartService->addItem(
      (int) $validated['user_id'],
      (string) $validated['item_type'],
      (array) $validated['payload']
    );

    return response()->json([
      'message' => 'Item added to cart',
      'cart' => $this->cartResponse($cart)['cart'],
    ], 201);
  }

  public function updateItem(Request $request, int $id) {
    $validated = $request->validate([
      'user_id' => 'required|integer|exists:app_users,id',
      'item_type' => 'nullable|in:appointment,service_request',
      'payload' => 'nullable|array',
      'status' => 'nullable|in:active,saved,removed',
      'sort_order' => 'nullable|integer|min:0',
    ]);

    $cart = $this->cartService->updateItem(
      (int) $validated['user_id'],
      $id,
      $validated
    );

    return response()->json([
      'message' => 'Cart item updated',
      'cart' => $this->cartResponse($cart)['cart'],
    ]);
  }

  public function removeItem(Request $request, int $id) {
    $validated = $request->validate([
      'user_id' => 'required|integer|exists:app_users,id',
    ]);

    $cart = $this->cartService->removeItem((int) $validated['user_id'], $id);

    return response()->json([
      'message' => 'Cart item removed',
      'cart' => $this->cartResponse($cart)['cart'],
    ]);
  }

  public function checkout(Request $request) {
    $validated = $request->validate([
      'user_id' => 'required|integer|exists:app_users,id',
      'payment_method' => 'required|in:card,offline,cash',
      'payment_provider' => 'nullable|in:monime,stripe,offline,cash',
      'payment_meta' => 'nullable|array',
      'payment_meta.bank_account_holder_name' => 'nullable|string|max:255',
      'payment_meta.bank_name' => 'nullable|string|max:255',
      'payment_meta.bank_account_number' => 'nullable|string|max:255',
      'payment_meta.notes' => 'nullable|string',
    ]);

    $provider = (string) ($validated['payment_provider'] ?? $validated['payment_method']);

    try {
      $result = $this->cartCheckoutService->checkout(
        (int) $validated['user_id'],
        (string) $validated['payment_method'],
        $provider,
        (array) ($validated['payment_meta'] ?? [])
      );
    } catch (RuntimeException $exception) {
      $code = $exception->getCode();
      $status = ($code >= 400 && $code < 600) ? $code : 422;

      return response()->json([
        'message' => $exception->getMessage(),
      ], $status);
    }

    return response()->json([
      'message' => 'Cart checked out successfully',
      'checkout' => $result['checkout'],
      'created' => $result['created'],
      'nextRoute' => $result['nextRoute'],
      'nextParams' => $result['nextParams'],
    ]);
  }

  private function cartResponse($cart): array {
    return [
      'cart' => [
        'id' => $cart->id,
        'user_id' => $cart->user_id,
        'currency' => $cart->currency,
        'subtotal' => (float) $cart->subtotal,
        'total' => (float) $cart->total,
        'totals_breakdown' => $cart->totals_breakdown ?? [],
        'item_count' => $cart->items->count(),
        'items' => $cart->items->map(function ($item) {
          return [
            'id' => $item->id,
            'item_type' => $item->item_type,
            'status' => $item->status,
            'sort_order' => $item->sort_order,
            'line_total' => (float) $item->line_total,
            'payload' => $item->payload ?? [],
            'pricing_breakdown' => $item->pricing_breakdown ?? [],
            'created_at' => $item->created_at,
            'updated_at' => $item->updated_at,
          ];
        }),
      ],
    ];
  }
}
