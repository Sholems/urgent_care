<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use App\Models\RequestPayment;
use App\Services\CartPricingService;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class RequestPaymentController extends Controller {
  public function __construct(
    private PaymentService $paymentService,
    private CartPricingService $cartPricingService
  ) {
  }

  private function stripeTemporarilyUnavailable(): JsonResponse {
    return response()->json([
      'message' => 'Stripe payments are temporarily unavailable. Please use offline or cash payment methods.',
    ], 503);
  }

  /**
   * Create payment for service request
   */
  public function createPayment(Request $request, $id) {
    $validated = $request->validate([
      'payment_type' => 'required|in:consultation,observation,meals,specialist,transport',
      'amount' => 'required|numeric|min:0',
      'user_id' => 'required|exists:app_users,id',
      'payment_method' => 'required|in:card,offline,cash',
      'payment_provider' => 'nullable|in:stripe,monime,offline,cash',
      'notes' => 'nullable|string',
    ]);

    $amount = round((float) $validated['amount'], 2);

    if ($validated['payment_type'] === 'consultation') {
      $allowedAmounts = $this->cartPricingService->allowedConsultationPaymentAmounts();
      $allowedRounded = array_map(fn ($v) => round((float) $v, 2), $allowedAmounts);
      if (!in_array($amount, $allowedRounded, true)) {
        return response()->json([
          'message' => 'Invalid consultation amount. Amount must match a configured consultation price.',
        ], 422);
      }
    }

    if ($validated['payment_type'] === 'observation') {
      $expected = round($this->cartPricingService->addonFeeAmount('observation'), 2);
      if ($amount !== $expected) {
        return response()->json([
          'message' => 'Invalid observation fee amount.',
        ], 422);
      }
    }

    if ($validated['payment_type'] === 'specialist') {
      $expected = round($this->cartPricingService->addonFeeAmount('specialist'), 2);
      if ($amount !== $expected) {
        return response()->json([
          'message' => 'Invalid specialist consultation fee amount.',
        ], 422);
      }
    }

    $serviceRequest = ServiceRequest::findOrFail($id);

    if ($validated['payment_method'] === 'card') {
      $provider = $validated['payment_provider'] ?? 'monime';
      if ($provider === 'stripe') {
        return $this->stripeTemporarilyUnavailable();
      }

      try {
        $checkout = $this->paymentService->createMonimeCheckout(
          (int) $id,
          $validated['payment_type'],
          $amount,
          (int) $validated['user_id']
        );

        return response()->json([
          'message' => 'Monime checkout session created',
          'payment' => [
            'id' => $checkout['paymentId'],
            'payment_provider' => 'monime',
            'payment_method' => 'card',
          ],
          'checkout' => $checkout,
        ]);
      } catch (RuntimeException $exception) {
        $code = $exception->getCode();
        $status = ($code >= 400 && $code < 600) ? $code : 422;

        return response()->json([
          'message' => $exception->getMessage(),
        ], $status);
      }
    } else {
      // Create offline payment
      $payment = $this->paymentService->createOfflinePayment(
        $id,
        $validated['payment_type'],
        $amount,
        $validated['payment_method'],
        $validated['notes'] ?? null
      );

      return response()->json([
        'message' => 'Offline payment created',
        'payment' => $payment,
      ]);
    }
  }

  /**
   * Get request payments
   */
  public function getPayments($id) {
    $payments = RequestPayment::where('service_request_id', $id)
      ->orderByDesc('created_at')
      ->get();

    return response()->json([
      'payments' => $payments,
    ]);
  }

  /**
   * Confirm payment
   */
  public function confirmPayment(Request $request, $id) {
    $validated = $request->validate([
      'payment_intent_id' => 'required_without:payment_id|string',
      'payment_id' => 'required_without:payment_intent_id|exists:request_payments,id',
      'payment_method' => 'nullable|in:card,offline,cash',
      'payment_provider' => 'nullable|in:stripe,monime,offline,cash',
      'monime_checkout_session_id' => 'nullable|string',
    ]);

    if (isset($validated['payment_id'])) {
      if (($validated['payment_provider'] ?? null) === 'monime' || !empty($validated['monime_checkout_session_id'])) {
        if (empty($validated['monime_checkout_session_id'])) {
          return response()->json([
            'message' => 'monime_checkout_session_id is required for Monime confirmation.',
          ], 422);
        }

        $payment = $this->paymentService->confirmMonimePayment(
          (int) $id,
          (int) $validated['payment_id'],
          $validated['monime_checkout_session_id']
        );

        return response()->json([
          'message' => $payment->payment_status === 'confirmed' ? 'Payment confirmed' : 'Payment pending',
          'payment' => $payment,
        ]);
      }

      // Confirm offline payment
      $payment = $this->paymentService->confirmOfflinePayment($validated['payment_id']);
    } else {
      if (($validated['payment_provider'] ?? null) === 'monime' || !empty($validated['monime_checkout_session_id'])) {
        if (empty($validated['monime_checkout_session_id'])) {
          return response()->json([
            'message' => 'monime_checkout_session_id is required for Monime confirmation.',
          ], 422);
        }
        $payment = $this->paymentService->confirmMonimePayment(
          (int) $id,
          null,
          $validated['monime_checkout_session_id']
        );
      } else if (($validated['payment_method'] ?? 'card') === 'card') {
        return $this->stripeTemporarilyUnavailable();
      } else {
        // Confirm card payment
        $payment = $this->paymentService->confirmPayment(
          $id,
          $validated['payment_intent_id'],
          $validated['payment_method'] ?? 'card'
        );
      }
    }

    return response()->json([
      'message' => 'Payment confirmed',
      'payment' => $payment,
    ]);
  }

  /**
   * Pay for meals
   */
  public function payForMeals(Request $request, $id) {
    $validated = $request->validate([
      'number_of_days' => 'required|integer|min:1',
      'amount_per_day' => 'required|numeric|min:0',
      'user_id' => 'required|exists:app_users,id',
      'payment_method' => 'required|in:card,offline,cash',
      'payment_provider' => 'nullable|in:stripe,monime,offline,cash',
      'notes' => 'nullable|string',
    ]);

    $totalAmount = $validated['number_of_days'] * $validated['amount_per_day'];

    if ($validated['payment_method'] === 'card') {
      $provider = $validated['payment_provider'] ?? 'monime';
      if ($provider === 'stripe') {
        return $this->stripeTemporarilyUnavailable();
      }

      try {
        $checkout = $this->paymentService->createMonimeCheckout(
          (int) $id,
          'meals',
          (float) $totalAmount,
          (int) $validated['user_id'],
          [
            'description' => "Meals for {$validated['number_of_days']} days",
          ]
        );

        return response()->json([
          'message' => 'Monime meal checkout session created',
          'checkout' => $checkout,
          'total_amount' => $totalAmount,
          'number_of_days' => $validated['number_of_days'],
        ]);
      } catch (RuntimeException $exception) {
        $code = $exception->getCode();
        $status = ($code >= 400 && $code < 600) ? $code : 422;

        return response()->json([
          'message' => $exception->getMessage(),
        ], $status);
      }
    } else {
      $payment = $this->paymentService->createOfflinePayment(
        $id,
        'meals',
        $totalAmount,
        $validated['payment_method'],
        "Meals for {$validated['number_of_days']} days. " . ($validated['notes'] ?? '')
      );

      return response()->json([
        'message' => 'Meal payment created',
        'payment' => $payment,
        'total_amount' => $totalAmount,
        'number_of_days' => $validated['number_of_days'],
      ]);
    }
  }
}




