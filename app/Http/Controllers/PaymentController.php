<?php

namespace App\Http\Controllers;

use App\Models\AppUser;
use App\Models\StripeKey;
use App\Services\MonimeApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Stripe\Exception\InvalidRequestException;
use Stripe\StripeClient;

class PaymentController extends Controller
{
    public function __construct(
        private MonimeApiService $monimeApiService
    ) {
    }

    protected function stripeTemporarilyUnavailable(): JsonResponse
    {
        return response()->json([
            'message' => 'Stripe payments are temporarily unavailable. Please use offline or cash payment methods.',
        ], 503);
    }

    protected function stripeClient(): StripeClient
    {
        $key = StripeKey::first();
        $secret = $key?->secret_key;
        return new StripeClient($secret ?? '');
    }

    protected function getOrCreateCustomer(AppUser $user, StripeClient $stripe): string
    {
        if ($user->stripe_customer_id) {
            try {
                $stripe->customers->retrieve($user->stripe_customer_id, []);

                return $user->stripe_customer_id;
            } catch (InvalidRequestException $exception) {
                if ($exception->getStripeCode() !== 'resource_missing') {
                    throw $exception;
                }

                // Stored customer was deleted in Stripe; fall through to create a fresh one
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

    public function createPaymentIntent(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|integer',
            'userId' => 'required|integer',
            'paymentProvider' => 'nullable|in:stripe,monime',
            'description' => 'nullable|string|max:1000',
            'reference' => 'nullable|string|max:255',
        ]);

        $provider = $validated['paymentProvider'] ?? 'stripe';
        if ($provider === 'stripe') {
            return $this->stripeTemporarilyUnavailable();
        }

        try {
            $reference = $validated['reference'] ?? sprintf('appointment-%d-%s', $validated['userId'], uniqid());
            $baseUrl = rtrim(config('app.url', 'https://lizelsundria.com'), '/');
            $payload = [
                'name' => 'Appointment Payment',
                'description' => $validated['description'] ?? 'Lizelsundria appointment payment',
                'reference' => $reference,
                'successUrl' => "{$baseUrl}/monime/success",
                'cancelUrl' => "{$baseUrl}/monime/cancel",
                'callbackState' => "appointment-user-{$validated['userId']}",
                'lineItems' => [[
                    'type' => 'custom',
                    'name' => 'Appointment fee',
                    'reference' => $reference,
                    'price' => [
                        'currency' => 'SLE',
                        'value' => (int) $validated['amount'],
                    ],
                    'quantity' => 1,
                ]],
                'paymentOptions' => $this->monimeApiService->buildPaymentOptions(),
            ];

            $result = $this->monimeApiService->createCheckoutSession($payload);
            return response()->json([
                'paymentProvider' => 'monime',
                'checkoutSessionId' => data_get($result, 'result.id'),
                'redirectUrl' => data_get($result, 'result.redirectUrl'),
                'orderNumber' => data_get($result, 'result.orderNumber'),
                'raw' => $result,
            ]);
        } catch (RuntimeException $exception) {
            $status = $exception->getCode() >= 400 ? $exception->getCode() : 422;
            return response()->json([
                'message' => $exception->getMessage(),
            ], $status);
        }

        $user = AppUser::findOrFail($validated['userId']);
        $stripe = $this->stripeClient();
        $customerId = $this->getOrCreateCustomer($user, $stripe);

        $ephemeralKey = $stripe->ephemeralKeys->create(
            ['customer' => $customerId],
            ['stripe_version' => '2023-10-16']
        );

        $paymentIntent = $stripe->paymentIntents->create([
            'amount' => $validated['amount'],
            'currency' => 'usd',
            'customer' => $customerId,
            'automatic_payment_methods' => ['enabled' => true],
        ]);

        return response()->json([
            'paymentIntent' => $paymentIntent->client_secret,
            'ephemeralKey' => $ephemeralKey->secret,
            'customer' => $customerId,
        ]);
    }

    public function createSetupIntent($userId)
    {
        return $this->stripeTemporarilyUnavailable();

        $user = AppUser::findOrFail($userId);
        $stripe = $this->stripeClient();
        $customerId = $this->getOrCreateCustomer($user, $stripe);

        $ephemeralKey = $stripe->ephemeralKeys->create(
            ['customer' => $customerId],
            ['stripe_version' => '2023-10-16']
        );

        $setupIntent = $stripe->setupIntents->create([
            'customer' => $customerId,
            'automatic_payment_methods' => ['enabled' => true],
        ]);

        return response()->json([
            'setupIntent' => $setupIntent->client_secret,
            'ephemeralKey' => $ephemeralKey->secret,
            'customer' => $customerId,
        ]);
    }

    public function listPaymentMethods($userId)
    {
        $user = AppUser::findOrFail($userId);
        $stripe = $this->stripeClient();
        $customerId = $user->stripe_customer_id;

        if (!$customerId) {
            return response()->json(['paymentMethods' => []]);
        }

        $methods = $stripe->paymentMethods->all([
            'customer' => $customerId,
            'type' => 'card',
        ]);

        $paymentMethods = collect($methods->data)->map(function ($pm) {
            return [
                'id' => $pm->id,
                'brand' => $pm->card->brand,
                'last4' => $pm->card->last4,
                'exp_month' => $pm->card->exp_month,
                'exp_year' => $pm->card->exp_year,
            ];
        })->all();

        return response()->json(['paymentMethods' => $paymentMethods]);
    }

    public function deletePaymentMethod($paymentMethodId)
    {
        $stripe = $this->stripeClient();
        $stripe->paymentMethods->detach($paymentMethodId);

        return response()->json(['success' => true]);
    }
}
