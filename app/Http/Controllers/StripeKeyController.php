<?php

namespace App\Http\Controllers;

use App\Models\StripeKey;

class StripeKeyController extends Controller
{
    public function show()
    {
        $key = StripeKey::first();

        if (!$key) {
            return response()->json(['message' => 'Stripe key not set'], 404);
        }

        return response()->json([
            'publishable_key' => $key->publishable_key,
            'secret_key' => $key->secret_key,
        ]);
    }
}
