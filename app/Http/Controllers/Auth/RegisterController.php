<?php
// LOCATION: app/Http/Controllers/Auth/RegisterController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    // =========================================================================
    // STAGE 1 — Personal Info + Email + Password (matches your existing stage1)
    // =========================================================================
    public function showStage1()
    {
        return view('auth.register-stage1');
    }

    public function submitStage1(Request $request)
    {
        $validated = $request->validate([
            'full_name'    => ['required', 'string', 'max:255'],
            'email'        => ['required', 'email', 'unique:users,email', 'max:255'],
            'country_code' => ['nullable', 'string', 'max:10'],
            'phone'        => ['required', 'string', 'max:20'],
            'country'      => ['required', 'string', 'max:100'],
            'password'     => ['required', 'min:8', 'confirmed'],
            'referral_code'=> ['nullable', 'string', 'max:20'],
        ]);

        // Find referrer if code given
        $referredBy = null;
        if (!empty($validated['referral_code'])) {
            $referrer = User::where('referral_code', $validated['referral_code'])->first();
            if ($referrer) $referredBy = $referrer->id;
        }

        // Create user immediately at stage 1 (your existing pattern)
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

        // Store user ID in session for next stages
        session(['registration_user_id' => $user->id, 'reg.completed_stage' => 1]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Stage 1 complete.',
            ]);
        }
        return redirect()->route('register.stage2')
            ->with('success', 'Account created! Please complete your profile.');
    }

    // =========================================================================
    // STAGE 2 — KYC / Address Details
    // =========================================================================
    public function showStage2()
    {
        return view('auth.register-stage2');
    }

    public function submitStage2(Request $request)
    {
        $userId = session('registration_user_id');

        if ($request->expectsJson() && !$userId) {
            return response()->json(['message' => 'Session expired. Please start again.'], 422);
        }

        $user = User::find($userId);
        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'User not found.'], 422);
            }
            return redirect()->route('register');
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

        session(['reg.completed_stage' => 2]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Stage 2 complete.']);
        }
        return redirect()->route('register.stage3');
    }

    // =========================================================================
    // STAGE 3 — Investor Suitability Profile
    // =========================================================================
    public function showStage3()
    {
        return view('auth.register-stage3');
    }

    public function submitStage3(Request $request)
    {
        $userId = session('registration_user_id');
        $user   = User::find($userId);

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Session expired. Please start again.'], 422);
            }
            return redirect()->route('register');
        }

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

        session(['reg.completed_stage' => 3]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Stage 3 complete.']);
        }
        return redirect()->route('register.stage4');
    }

    // =========================================================================
    // STAGE 4 — Security Setup / Final Step → Log user in
    // =========================================================================
    public function showStage4()
    {
        return view('auth.register-stage4');
    }

    public function submitStage4(Request $request)
    {
        $userId = session('registration_user_id');
        $user   = User::find($userId);

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Session expired. Please start again.'], 422);
            }
            return redirect()->route('register');
        }

        $request->validate([
            'withdrawal_pin'              => ['nullable', 'digits:4', 'confirmed'],
            'withdrawal_pin_confirmation' => ['nullable'],
            'referral_code'               => ['nullable', 'string', 'max:20'],
        ]);

        // Optional withdrawal PIN
        if ($request->filled('withdrawal_pin')) {
            $user->withdrawal_pin = Hash::make($request->withdrawal_pin);
        }

        $user->registration_stage     = 4;
        $user->registration_completed = true;
        $user->save();

        // Clear session
        session()->forget(['registration_user_id', 'reg.completed_stage']);

        // Log the user in
        Auth::login($user);
        $request->session()->regenerate();

        if ($request->expectsJson()) {
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

        return redirect()->route('investor-investment.dashboard')
            ->with('success', 'Registration complete! Welcome.');
    }
}
