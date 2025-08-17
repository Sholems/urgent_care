<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\EmailOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Change the authenticated user's password.
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required',
            'new_password' => 'required',
        ]);

        $otpRecord = EmailOtp::where('email', $request->email)->first();

        if (
            !$otpRecord ||
            $otpRecord->expires_at->isPast() ||
            !Hash::check($request->otp, $otpRecord->otp)
        ) {
            return response()->json([
                'message' => 'Invalid or expired OTP',
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        $otpRecord->delete();

        return response()->json([
            'message' => 'Password changed successfully',
            'user' => $user,
        ], 200);
    }
}

