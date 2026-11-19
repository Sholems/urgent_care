<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Cart;
use App\Models\RequestPayment;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CartCheckoutService {
  public function __construct(
    private CartService $cartService,
    private ServiceRequestService $serviceRequestService,
    private MonimeApiService $monimeApiService,
    private AppointmentMailer $appointmentMailer,
    private AppointmentNotifier $appointmentNotifier
  ) {
  }

  public function checkout(
    int $userId,
    string $paymentMethod,
    string $paymentProvider,
    ?array $paymentMeta = null
  ): array {
    $cart = $this->cartService->getUserCart($userId);
    $activeItems = $cart->items()->where('status', 'active')->get();

    if ($activeItems->isEmpty()) {
      throw new RuntimeException('Your cart is empty.');
    }

    if ($paymentMethod === 'card' && $paymentProvider === 'stripe') {
      throw new RuntimeException('Stripe payments are temporarily unavailable. Please choose Monime.');
    }

    $checkout = null;
    if ($paymentMethod === 'card') {
      $checkout = $this->createMonimeCheckoutForCart($cart);
    }

    $createdAppointments = [];
    $createdServiceRequests = [];

    DB::transaction(function () use (
      $activeItems,
      $userId,
      $paymentMethod,
      $paymentProvider,
      $paymentMeta,
      $checkout,
      &$createdAppointments,
      &$createdServiceRequests,
      $cart
    ) {
      foreach ($activeItems as $item) {
        $payload = (array) ($item->payload ?? []);
        $breakdown = (array) ($item->pricing_breakdown ?? []);

        if ($item->item_type === 'appointment') {
          $appointment = Appointment::create([
            'date' => $payload['date'],
            'time' => $payload['time'],
            'user_id' => $userId,
            'doctor_id' => (int) $payload['doctor_id'],
            'status' => 'pending',
            'payment_provider' => $paymentProvider,
            'monime_checkout_session_id' => $checkout['checkoutSessionId'] ?? null,
            'monime_order_number' => $checkout['orderNumber'] ?? null,
            'bank_account_holder_name' => $paymentMeta['bank_account_holder_name'] ?? null,
            'bank_name' => $paymentMeta['bank_name'] ?? null,
            'bank_account_number' => $paymentMeta['bank_account_number'] ?? null,
          ]);

          $this->appointmentNotifier->status($appointment, 'pending');
          $this->appointmentMailer->sendCreated($appointment);

          $createdAppointments[] = $appointment->toArray();
          continue;
        }

        if ($item->item_type === 'service_request') {
          $request = $this->serviceRequestService->create([
            'user_id' => $userId,
            'transport_mode' => (string) $payload['transport_mode'],
            'patient_name' => (string) $payload['patient_name'],
            'patient_phone' => (string) $payload['patient_phone'],
            'patient_address' => (string) $payload['patient_address'],
            'patient_gender' => (string) $payload['patient_gender'],
            'symptoms' => $payload['symptoms'] ?? null,
          ]);

          foreach ($breakdown as $line) {
            $paymentType = $this->mapBreakdownCodeToPaymentType((string) ($line['code'] ?? ''));
            if (!$paymentType) {
              continue;
            }

            RequestPayment::create([
              'service_request_id' => $request->id,
              'payment_type' => $paymentType,
              'amount' => (float) ($line['amount'] ?? 0),
              'payment_status' => 'pending',
              'payment_method' => $paymentMethod,
              'payment_provider' => $paymentProvider,
              'monime_checkout_session_id' => $checkout['checkoutSessionId'] ?? null,
              'monime_order_number' => $checkout['orderNumber'] ?? null,
              'notes' => $paymentMeta['notes'] ?? null,
            ]);
          }

          $request->update([
            'status' => 'payment_pending',
          ]);

          $createdServiceRequests[] = $request->toArray();
        }
      }

      $this->cartService->clearCart($cart);
    });

    $nextRoute = 'AppointmentSuccess';
    $nextParams = [];
    if (empty($createdAppointments) && !empty($createdServiceRequests)) {
      $nextRoute = 'RequestDetails';
      $nextParams = ['requestId' => (int) $createdServiceRequests[0]['id']];
    }

    return [
      'checkout' => $checkout,
      'created' => [
        'appointments' => $createdAppointments,
        'service_requests' => $createdServiceRequests,
      ],
      'nextRoute' => $nextRoute,
      'nextParams' => $nextParams,
    ];
  }

  private function createMonimeCheckoutForCart(Cart $cart): array {
    $amountMinor = max((int) round(((float) $cart->total) * 100), 1);
    $reference = sprintf('cart-%d-%s', $cart->id, uniqid());
    $baseUrl = rtrim(config('app.url', 'https://lizelsundria.com'), '/');
    $payload = [
      'name' => 'Cart Checkout',
      'description' => "Cart payment #{$cart->id}",
      'reference' => $reference,
      'successUrl' => "{$baseUrl}/monime/success",
      'cancelUrl' => "{$baseUrl}/monime/cancel",
      'callbackState' => "cart-{$cart->id}-user-{$cart->user_id}",
      'lineItems' => [[
        'type' => 'custom',
        'name' => 'Cart total',
        'reference' => $reference,
        'price' => [
          'currency' => strtoupper((string) config('cart_pricing.currency', 'SLE')),
          'value' => $amountMinor,
        ],
        'quantity' => 1,
      ]],
      'paymentOptions' => $this->monimeApiService->buildPaymentOptions(),
      'metadata' => [
        'cart_id' => (string) $cart->id,
        'app_user_id' => (string) $cart->user_id,
      ],
    ];

    $response = $this->monimeApiService->createCheckoutSession($payload);
    $result = data_get($response, 'result', []);

    return [
      'checkoutSessionId' => data_get($result, 'id'),
      'redirectUrl' => data_get($result, 'redirectUrl'),
      'orderNumber' => data_get($result, 'orderNumber'),
    ];
  }

  private function mapBreakdownCodeToPaymentType(string $code): ?string {
    return match ($code) {
      'consultation' => 'consultation',
      'transport' => 'transport',
      'observation' => 'observation',
      'specialist' => 'specialist',
      'meals' => 'meals',
      default => null,
    };
  }
}
