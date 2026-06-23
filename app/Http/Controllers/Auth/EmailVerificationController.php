<?php
// LOCATION: app/Http/Controllers/Auth/EmailVerificationController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\OtpService;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    protected OtpService $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    // POST /register/verify-otp
    public function verify(Request $request)
    {
        $validated = $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $user = $request->user();

        $result = $this->otpService->verify($user, $validated['otp']);

        if (!$result['success']) {
            return response()->json(['success' => false, 'message' => $result['message']], 422);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
        ]);
    }

    // POST /register/resend-otp
    public function resend(Request $request)
    {
        $user = $request->user();

        if ($user->email_verified_at) {
            return response()->json(['success' => true, 'message' => 'Email already verified.']);
        }

        // Simple cooldown: don't allow resend more than once every 60 seconds.
        if ($user->email_otp_expires_at && now()->lessThan($user->email_otp_expires_at->subMinutes(9))) {
            return response()->json([
                'success' => false,
                'message' => 'Please wait a moment before requesting another code.',
            ], 429);
        }

        $this->otpService->generateAndSend($user);

        return response()->json(['success' => true, 'message' => 'A new verification code has been sent.']);
    }
}