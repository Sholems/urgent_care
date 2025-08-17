<?php

namespace App\Http\Controllers;

use App\Models\EmailOtp;
use App\Models\AppUser;
use App\Models\SmtpSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class OtpController extends Controller
{
    /**
     * Send an OTP code to the given email address.
     */
    public function send(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $otp = (string) random_int(100000, 999999);

        EmailOtp::updateOrCreate(
            ['email' => $request->email],
            [
                'otp' => Hash::make($otp),
                'expires_at' => now()->addMinutes(10),
            ]
        );

        if ($smtp = SmtpSetting::first()) {
            config([
                'mail.default' => $smtp->mailer ?? 'smtp',
                'mail.mailers.smtp.host' => $smtp->host,
                'mail.mailers.smtp.port' => $smtp->port,
                'mail.mailers.smtp.username' => $smtp->username,
                'mail.mailers.smtp.password' => $smtp->password,
                'mail.mailers.smtp.encryption' => $smtp->encryption,
                'mail.from.address' => $smtp->from_address,
                'mail.from.name' => $smtp->from_name,
            ]);
        }

        Mail::raw("Your OTP code is: {$otp}", function ($message) use ($request) {
            $message->to($request->email)->subject('Your OTP Code');
        });

        return response()->json(['message' => 'OTP sent successfully']);
    }

    /**
     * Verify the provided OTP for the email address.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required',
        ]);

        $record = EmailOtp::where('email', $request->email)->first();

        if (
            !$record ||
            $record->expires_at->isPast() ||
            !Hash::check($request->otp, $record->otp)
        ) {
            return response()->json(['message' => 'Invalid or expired OTP'], 422);
        }

        $record->delete();

        if ($user = AppUser::where('user_email', $request->email)->first()) {
            $user->user_email_verified = 'true';
            $user->save();
        }

        return response()->json(['message' => 'OTP verified successfully']);
    }
}

