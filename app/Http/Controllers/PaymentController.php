<?php

namespace App\Http\Controllers;

use App\Models\AppUser;
use App\Models\StripeKey;
use Illuminate\Http\Request;
use Stripe\StripeClient;

class PaymentController extends Controller
{
    protected function stripeClient(): StripeClient
    {
        $key = StripeKey::first();
        $secret = $key?->secret_key;
        return new StripeClient($secret ?? '');
    }

    protected function getOrCreateCustomer(AppUser $user, StripeClient $stripe): string
    {
        if ($user->stripe_customer_id) {
            return $user->stripe_customer_id;
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
        ]);

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
