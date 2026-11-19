<?php

namespace App\Http\Controllers;

class StripeKeyController extends Controller
{
    public function show()
    {
        return response()->json([
            'message' => 'Stripe payments are temporarily unavailable. Please use offline or cash payment methods.',
        ], 503);
    }
}
