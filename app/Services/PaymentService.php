<?php

namespace App\Services;

use App\Models\RequestPayment;
use App\Models\ServiceRequest;
use App\Models\AppUser;
use App\Models\StripeKey;
use App\Services\NotificationService;
use RuntimeException;
use Stripe\StripeClient;
use Stripe\Exception\InvalidRequestException;

class PaymentService {
  public function __construct(
    private NotificationService $notificationService,
    private MonimeApiService $monimeApiService
  ) {
  }

  protected function stripeClient(): StripeClient {
    $key = StripeKey::first();
    $secret = $key?->secret_key;
    return new StripeClient($secret ?? '');
  }

  protected function getOrCreateCustomer(AppUser $user, StripeClient $stripe): string {
    if ($user->stripe_customer_id) {
      try {
        $stripe->customers->retrieve($user->stripe_customer_id, []);
        return $user->stripe_customer_id;
      } catch (InvalidRequestException $exception) {
        if ($exception->getStripeCode() !== 'resource_missing') {
          throw $exception;
        }
        $user->stripe_customer_id = null;
        $user->save();
      }
    }

    $customer = $stripe->customers->create([
      'metadata' => ['app_user_id' => $user->id],
    ]);

    $user->stripe_customer_id = $customer->id;
    $user->save();

    return $customer->id;
  }

  /**
   * Create payment intent for service request payment
   */
  public function createPaymentIntent(int $requestId, string $paymentType, float $amount, int $userId): array {
    $request = ServiceRequest::findOrFail($requestId);
    $user = AppUser::findOrFail($userId);
    $stripe = $this->stripeClient();
    $customerId = $this->getOrCreateCustomer($user, $stripe);

    $ephemeralKey = $stripe->ephemeralKeys->create(
      ['customer' => $customerId],
      ['stripe_version' => '2023-10-16']
    );

    $paymentIntent = $stripe->paymentIntents->create([
      'amount' => (int) ($amount * 100), // Convert to cents
      'currency' => 'usd',
      'customer' => $customerId,
      'metadata' => [
        'service_request_id' => $requestId,
        'payment_type' => $paymentType,
      ],
      'automatic_payment_methods' => ['enabled' => true],
    ]);

    // Create payment record
    RequestPayment::create([
      'service_request_id' => $requestId,
      'payment_type' => $paymentType,
      'amount' => $amount,
      'payment_status' => 'pending',
      'payment_intent_id' => $paymentIntent->id,
      'payment_method' => 'card',
      'payment_provider' => 'stripe',
    ]);

    return [
      'paymentIntent' => $paymentIntent->client_secret,
      'ephemeralKey' => $ephemeralKey->secret,
      'customer' => $customerId,
    ];
  }

  /**
   * Confirm payment
   */
  public function confirmPayment(int $requestId, string $paymentIntentId, string $paymentMethod = 'card'): RequestPayment {
    $payment = RequestPayment::where('service_request_id', $requestId)
      ->where('payment_intent_id', $paymentIntentId)
      ->firstOrFail();

    $payment->update([
      'payment_status' => 'confirmed',
      'payment_method' => $paymentMethod,
    ]);

    $request = ServiceRequest::findOrFail($requestId);
    
    // Update request status if first payment
    if ($request->status === 'pending' || $request->status === 'payment_pending') {
      $request->update(['status' => 'payment_confirmed']);
    }

    // Notify relevant parties
    $this->notificationService->notifyPaymentConfirmed($request, $payment);

    return $payment;
  }

  /**
   * Create offline payment record
   */
  public function createOfflinePayment(int $requestId, string $paymentType, float $amount, string $paymentMethod = 'offline', ?string $notes = null): RequestPayment {
    $payment = RequestPayment::create([
      'service_request_id' => $requestId,
      'payment_type' => $paymentType,
      'amount' => $amount,
      'payment_status' => 'pending',
      'payment_method' => $paymentMethod,
      'payment_provider' => $paymentMethod,
      'notes' => $notes,
    ]);

    $request = ServiceRequest::findOrFail($requestId);
    $request->update(['status' => 'payment_pending']);

    return $payment;
  }

  /**
   * Confirm offline payment (by admin/nurse)
   */
  public function confirmOfflinePayment(int $paymentId): RequestPayment {
    $payment = RequestPayment::findOrFail($paymentId);
    $payment->update(['payment_status' => 'confirmed']);

    $request = ServiceRequest::findOrFail($payment->service_request_id);
    
    if ($request->status === 'pending' || $request->status === 'payment_pending') {
      $request->update(['status' => 'payment_confirmed']);
    }

    $this->notificationService->notifyPaymentConfirmed($request, $payment);

    return $payment;
  }

  /**
   * Create Monime checkout session and persist a pending request payment.
   */
  public function createMonimeCheckout(
    int $requestId,
    string $paymentType,
    float $amount,
    int $userId,
    array $context = []
  ): array {
    $request = ServiceRequest::findOrFail($requestId);
    $user = AppUser::findOrFail($userId);

    $amountMinor = max((int) round($amount * 100), 1);
    $reference = $context['reference'] ?? sprintf('req-%d-%s', $requestId, uniqid());
    $baseUrl = rtrim(config('app.url', 'https://lizelsundria.com'), '/');
    $successUrl = $context['success_url'] ?? "{$baseUrl}/monime/success";
    $cancelUrl = $context['cancel_url'] ?? "{$baseUrl}/monime/cancel";

    $payload = [
      'name' => $context['name'] ?? 'Lizelsundria Payment',
      'description' => $context['description'] ?? "Service request {$requestId} payment ({$paymentType})",
      'reference' => $reference,
      'successUrl' => $successUrl,
      'cancelUrl' => $cancelUrl,
      'callbackState' => (string) ($context['callback_state'] ?? "request-{$requestId}-user-{$user->id}"),
      'lineItems' => [[
        'type' => 'custom',
        'name' => $context['line_item_name'] ?? ucfirst($paymentType).' payment',
        'reference' => (string) ($context['line_item_reference'] ?? $reference),
        'price' => [
          'currency' => strtoupper((string) ($context['currency'] ?? 'SLE')),
          'value' => $amountMinor,
        ],
        'quantity' => 1,
      ]],
      'paymentOptions' => $this->monimeApiService->buildPaymentOptions(),
      'metadata' => [
        'service_request_id' => (string) $requestId,
        'payment_type' => $paymentType,
        'app_user_id' => (string) $user->id,
      ],
    ];

    $response = $this->monimeApiService->createCheckoutSession($payload);
    $result = data_get($response, 'result', []);
    $sessionId = data_get($result, 'id');
    $redirectUrl = data_get($result, 'redirectUrl');
    $orderNumber = data_get($result, 'orderNumber');

    if (!$sessionId || !$redirectUrl) {
      throw new RuntimeException('Monime checkout session did not return required redirect details.');
    }

    $payment = RequestPayment::create([
      'service_request_id' => $requestId,
      'payment_type' => $paymentType,
      'amount' => $amount,
      'payment_status' => 'pending',
      'payment_method' => 'card',
      'payment_provider' => 'monime',
      'monime_checkout_session_id' => $sessionId,
      'monime_order_number' => $orderNumber,
      'provider_payload' => $response,
    ]);

    return [
      'checkoutSessionId' => $sessionId,
      'redirectUrl' => $redirectUrl,
      'orderNumber' => $orderNumber,
      'paymentId' => $payment->id,
    ];
  }

  public function confirmMonimePayment(
    int $requestId,
    ?int $paymentId,
    string $checkoutSessionId
  ): RequestPayment {
    $payment = $paymentId
      ? RequestPayment::where('service_request_id', $requestId)->findOrFail($paymentId)
      : RequestPayment::where('service_request_id', $requestId)
        ->where('monime_checkout_session_id', $checkoutSessionId)
        ->firstOrFail();

    $session = $this->monimeApiService->getCheckoutSession($checkoutSessionId);
    $status = data_get($session, 'result.status');
    $orderNumber = data_get($session, 'result.orderNumber');

    if ($status === 'completed') {
      $payment->update([
        'payment_status' => 'confirmed',
        'payment_method' => 'card',
        'payment_provider' => 'monime',
        'monime_order_number' => $orderNumber ?? $payment->monime_order_number,
        'provider_payload' => $session,
      ]);

      $request = ServiceRequest::findOrFail($requestId);
      if ($request->status === 'pending' || $request->status === 'payment_pending') {
        $request->update(['status' => 'payment_confirmed']);
      }
      $this->notificationService->notifyPaymentConfirmed($request, $payment);

      return $payment->fresh();
    }

    if (in_array($status, ['cancelled', 'expired'], true)) {
      $payment->update([
        'payment_status' => 'failed',
        'payment_provider' => 'monime',
        'provider_payload' => $session,
      ]);
      return $payment->fresh();
    }

    return $payment->fresh();
  }
}




