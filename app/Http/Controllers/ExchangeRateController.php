<?php

namespace App\Http\Controllers;

use App\Models\ExchangeRate;

class ExchangeRateController extends Controller
{
    /**
     * Return the latest USD to SLL exchange rate.
     */
    public function show()
    {
        $rate = ExchangeRate::latest()->first();

        if (!$rate) {
            return response()->json(['message' => 'Exchange rate not available'], 404);
        }

        return $rate;
    }
}
