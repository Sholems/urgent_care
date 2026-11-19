<?php

namespace App\Http\Controllers;

use App\Services\MonimeApiService;
use Illuminate\Http\Request;
use RuntimeException;

class MonimeController extends Controller
{
    public function __construct(private MonimeApiService $monimeApiService)
    {
    }

    public function createCheckoutSession(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:1000',
            'reference' => 'nullable|string|max:255',
            'success_url' => 'required|url|max:255',
            'cancel_url' => 'required|url|max:255',
            'callback_state' => 'nullable|string|max:255',
            'currency' => 'required|string|size:3',
            'amount' => 'required|integer|min:1',
            'line_item_name' => 'required|string|max:100',
            'line_item_reference' => 'nullable|string|max:100',
            'enable_card' => 'nullable|boolean',
            'enable_bank' => 'nullable|boolean',
            'enable_momo' => 'nullable|boolean',
            'enable_wallet' => 'nullable|boolean',
        ]);

        $payload = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'reference' => $validated['reference'] ?? null,
            'successUrl' => $validated['success_url'],
            'cancelUrl' => $validated['cancel_url'],
            'callbackState' => $validated['callback_state'] ?? null,
            'lineItems' => [[
                'type' => 'custom',
                'name' => $validated['line_item_name'],
                'reference' => $validated['line_item_reference'] ?? null,
                'price' => [
                    'currency' => strtoupper($validated['currency']),
                    'value' => (int) $validated['amount'],
                ],
                'quantity' => 1,
            ]],
            'paymentOptions' => $this->monimeApiService->buildPaymentOptions([
                'enable_card' => $validated['enable_card'] ?? null,
                'enable_bank' => $validated['enable_bank'] ?? null,
                'enable_momo' => $validated['enable_momo'] ?? null,
                'enable_wallet' => $validated['enable_wallet'] ?? null,
            ]),
        ];

        try {
            $response = $this->monimeApiService->createCheckoutSession($payload);

            return response()->json($response);
        } catch (RuntimeException $exception) {
            $status = $exception->getCode() >= 400 ? $exception->getCode() : 422;

            return response()->json([
                'message' => $exception->getMessage(),
            ], $status);
        }
    }

    public function getCheckoutSession(string $id)
    {
        try {
            $response = $this->monimeApiService->getCheckoutSession($id);

            return response()->json($response);
        } catch (RuntimeException $exception) {
            $status = $exception->getCode() >= 400 ? $exception->getCode() : 422;

            return response()->json([
                'message' => $exception->getMessage(),
            ], $status);
        }
    }

    public function getPayment(string $id)
    {
        try {
            $response = $this->monimeApiService->getPayment($id);

            return response()->json($response);
        } catch (RuntimeException $exception) {
            $status = $exception->getCode() >= 400 ? $exception->getCode() : 422;

            return response()->json([
                'message' => $exception->getMessage(),
            ], $status);
        }
    }

    public function listProviders(Request $request)
    {
        $validated = $request->validate([
            'country' => 'nullable|string|size:2',
        ]);

        $country = strtoupper($validated['country'] ?? 'SL');

        try {
            $momos = $this->monimeApiService->listMomos($country);
            $banks = $this->monimeApiService->listBanks($country);

            return response()->json([
                'momos' => data_get($momos, 'result', []),
                'banks' => data_get($banks, 'result', []),
            ]);
        } catch (RuntimeException $exception) {
            $status = $exception->getCode() >= 400 ? $exception->getCode() : 422;

            return response()->json([
                'message' => $exception->getMessage(),
            ], $status);
        }
    }

    public function webhook(Request $request)
    {
        // Placeholder handler: signature verification + event routing can be
        // expanded once final Monime webhook signature format is confirmed.
        return response()->json([
            'message' => 'Webhook received',
            'received' => true,
        ]);
    }
}
