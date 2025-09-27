<?php

namespace App\Http\Controllers;

use App\Models\AdminBankDetail;

class AdminBankDetailController extends Controller
{
    public function show()
    {
        $detail = AdminBankDetail::first();

        if (!$detail) {
            return response()->json(['message' => 'Admin bank details not set'], 404);
        }

        return response()->json([
            'bank_name' => $detail->bank_name,
            'account_name' => $detail->account_name,
            'account_number' => $detail->account_number,
            'bank_code' => $detail->bank_code,
        ]);
    }
}
