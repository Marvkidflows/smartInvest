<?php
// LOCATION: app/Http/Controllers/Auth/RegisterController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    protected OtpService $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    // =========================================================================
    // STAGE 1 — Personal Info + Email + Password
    // =========================================================================
    public function submitStage1(Request $request)
    {
        $validated = $request->validate([
            'full_name'    => ['required', 'string', 'max:255'],
            'email'        => ['required', 'email', 'max:255'],
            'country_code' => ['nullable', 'string', 'max:10'],
            'phone'        => ['required', 'string', 'max:20'],
            'country'      => ['required', 'string', 'max:100'],
            'password'     => ['required', 'min:8', 'confirmed'],
            'referral_code'=> ['nullable', 'string', 'max:20'],
        ]);

        $existing = User::where('email', $validated['email'])->first();

        // ── Email belongs to a COMPLETED account — real conflict, block it ──
        if ($existing && $existing->registration_completed) {
            return response()->json([
                'success' => false,
                'message' => 'This email is already registered. Please log in instead.',
            ], 422);
        }

        // ── Email belongs to an INCOMPLETE registration — resume it ──
        if ($existing && !$existing->registration_completed) {
            $existing->update([
                'name'         => $validated['full_name'],
                'full_name'    => $validated['full_name'],
                'country_code' => $validated['country_code'] ?? null,
                'phone'        => $validated['phone'],
                'country'      => $validated['country'],
                'password'     => Hash::make($validated['password']),
            ]);

            try {
                $this->otpService->generateAndSend($existing);
            } catch (\Exception $e) {
                \Log::error('OTP resend failed during registration resume: '.$e->getMessage());
            }

            $token = $existing->createToken('registration')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Welcome back! A new verification code has been sent to your email.',
                'token'   => $token,
                'user'    => ['id' => $existing->id, 'email' => $existing->email],
            ]);
        }

        // ── Brand new email — create as before ──
        $referredBy = null;
        if (!empty($validated['referral_code'])) {
            $referrer = User::where('referral_code', $validated['referral_code'])->first();
            if ($referrer) $referredBy = $referrer->id;
        }

        $user = User::create([
            'name'                    => $validated['full_name'],
            'full_name'               => $validated['full_name'],
            'email'                   => $validated['email'],
            'country_code'            => $validated['country_code'] ?? null,
            'phone'                   => $validated['phone'],
            'country'                 => $validated['country'],
            'password'                => Hash::make($validated['password']),
            'role'                    => 'investor',
            'referral_code'           => strtoupper(Str::random(8)),
            'referred_by'             => $referredBy,
            'balance'                 => 0,
            'registration_stage'      => 1,
            'registration_completed'  => false,
            'status'                  => 'active',
        ]);

        try {
            app(\App\Services\TelegramService::class)->newRegistration(
                $validated['full_name'],
                $validated['email']
            );
        } catch (\Exception $e) {
            \Log::error('Telegram notification failed: '.$e->getMessage());
        }

        try {
            $this->otpService->generateAndSend($user);
        } catch (\Exception $e) {
            \Log::error('OTP send failed during registration: '.$e->getMessage());
        }

        $token = $user->createToken('registration')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Stage 1 complete. Verification code sent to your email.',
            'token'   => $token,
            'user'    => ['id' => $user->id, 'email' => $user->email],
        ]);
    }

    // =========================================================================
    // STAGE 2 — KYC / Address Details
    // =========================================================================
    public function submitStage2(Request $request)
    {
        $user = $request->user();

        if (!$user->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Please verify your email before continuing.',
            ], 403);
        }

        $validated = $request->validate([
            'date_of_birth'      => ['required', 'date', 'before:-18 years'],
            'residential_address'=> ['required', 'string', 'max:500'],
            'city'               => ['required', 'string', 'max:100'],
            'state'              => ['nullable', 'string', 'max:100'],
            'postal_code'        => ['nullable', 'string', 'max:20'],
        ], [
            'date_of_birth.before' => 'You must be at least 18 years old.',
        ]);

        $user->update([
            'date_of_birth'       => $validated['date_of_birth'],
            'residential_address' => $validated['residential_address'],
            'city'                => $validated['city'],
            'state'               => $validated['state'] ?? null,
            'postal_code'         => $validated['postal_code'] ?? null,
            'registration_stage'  => 2,
        ]);

        return response()->json(['success' => true, 'message' => 'Stage 2 complete.']);
    }

    // =========================================================================
    // STAGE 3 — Investor Suitability Profile
    // =========================================================================
    public function submitStage3(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'employment_status'    => ['required', 'string', 'max:50'],
            'annual_income_range'  => ['required', 'string', 'max:50'],
            'source_of_funds'      => ['required', 'string', 'max:100'],
            'investment_experience'=> ['required', 'string', 'max:50'],
            'risk_tolerance'       => ['required', 'string', 'max:50'],
            'investment_objectives'=> ['nullable', 'string', 'max:1000'],
        ]);

        $user->update([
            'employment_status'     => $validated['employment_status'],
            'annual_income_range'   => $validated['annual_income_range'],
            'source_of_funds'       => $validated['source_of_funds'],
            'investment_experience' => $validated['investment_experience'],
            'risk_tolerance'        => $validated['risk_tolerance'],
            'investment_objectives' => $validated['investment_objectives'] ?? null,
            'registration_stage'    => 3,
        ]);

        return response()->json(['success' => true, 'message' => 'Stage 3 complete.']);
    }

    // =========================================================================
    // STAGE 4 — Security Setup / Final Step
    // =========================================================================
    public function submitStage4(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'withdrawal_pin'              => ['nullable', 'digits:4', 'confirmed'],
            'withdrawal_pin_confirmation' => ['nullable'],
            'referral_code'               => ['nullable', 'string', 'max:20'],
        ]);

        if ($request->filled('withdrawal_pin')) {
            $user->withdrawal_pin = Hash::make($request->withdrawal_pin);
        }

        $user->registration_stage     = 4;
        $user->registration_completed = true;
        $user->save();

        return response()->json([
            'user' => [
                'id'            => $user->id,
                'name'          => $user->name ?? $user->full_name,
                'email'         => $user->email,
                'role'          => $user->role,
                'balance'       => (float) ($user->balance ?? 0),
                'referral_code' => $user->referral_code,
                'created_at'    => $user->created_at,
            ],
            'message' => 'Registration complete! Welcome to Smart System Investment.',
        ], 201);
    }
}