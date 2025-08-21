<?php

namespace App\Http\Controllers;

use App\Models\AppUser;
use App\Models\TwilioSetting;
use Illuminate\Http\Request;
use Twilio\Rest\Client;

class PhoneOtpController extends Controller
{
    public function send(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        $setting = TwilioSetting::first();
        if (!$setting) {
            return response()->json(['message' => 'Twilio settings not configured'], 500);
        }

        $client = new Client($setting->account_sid, $setting->auth_token);

        $client->verify->v2->services($setting->verify_sid)
            ->verifications
            ->create($request->phone, 'sms');

        return response()->json(['message' => 'OTP sent successfully']);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'code' => 'required|string',
        ]);

        $setting = TwilioSetting::first();
        if (!$setting) {
            return response()->json(['message' => 'Twilio settings not configured'], 500);
        }

        $client = new Client($setting->account_sid, $setting->auth_token);

        $verification = $client->verify->v2->services($setting->verify_sid)
            ->verificationChecks
            ->create([
                'to' => $request->phone,
                'code' => $request->code,
            ]);

        if ($verification->status !== 'approved') {
            return response()->json(['message' => 'Invalid or expired OTP'], 422);
        }

        if ($user = AppUser::where('user_phone_number', $request->phone)->first()) {
            $user->user_phone_verified = 'true';
            $user->save();
        }

        return response()->json(['message' => 'Phone number verified successfully']);
    }
}
