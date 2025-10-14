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

        $phone = $this->normalizePhoneNumber($request->phone);
        if (!$phone) {
            return response()->json(['message' => 'Invalid phone number format'], 422);
        }

        $client = new Client($setting->account_sid, $setting->auth_token);

        $client->verify->v2->services($setting->verify_sid)
            ->verifications
            ->create($phone, 'sms');

        return response()->json(['message' => 'OTP sent successfully']);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'phone'      => 'required|string',
            'code'       => 'required|string',
            'user_email' => 'nullable|email',
        ]);

        $setting = TwilioSetting::first();
        if (!$setting) {
            return response()->json(['message' => 'Twilio settings not configured'], 500);
        }

        $phone = $this->normalizePhoneNumber($request->phone);
        if (!$phone) {
            return response()->json(['message' => 'Invalid phone number format'], 422);
        }

        $client = new Client($setting->account_sid, $setting->auth_token);

        $verification = $client->verify->v2->services($setting->verify_sid)
            ->verificationChecks
            ->create([
                'to' => $phone,
                'code' => $request->code,
            ]);

        if ($verification->status !== 'approved') {
            return response()->json(['message' => 'Invalid or expired OTP'], 422);
        }

        $user = null;

        if ($request->filled('user_email')) {
            $user = AppUser::where('user_email', strtolower($request->user_email))->first();
        }

        if (!$user) {
            $user = AppUser::where(function ($query) use ($phone, $request) {
                $query->where('user_phone_number', $phone);

                if ($phone !== $request->phone) {
                    $query->orWhere('user_phone_number', $request->phone);
                }
            })->first();
        }

        if ($user) {
            $user->user_phone_number  = $phone;
            $user->user_phone_verified = 'true';
            $user->save();
        }

        return response()->json(['message' => 'Phone number verified successfully']);
    }

    protected function normalizePhoneNumber(string $phone): ?string
    {
        $trimmed = trim($phone);
        if ($trimmed === '') {
            return null;
        }

        $digits = preg_replace('/[^\d+]/', '', $trimmed);
        if ($digits === null || $digits === '') {
            return null;
        }

        if ($digits[0] !== '+') {
            $digits = '+' . ltrim($digits, '+');
        }

        if (!preg_match('/^\+\d{6,15}$/', $digits)) {
            return null;
        }

        return $digits;
    }
}
