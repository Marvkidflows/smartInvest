<?php
// LOCATION: app/Http/Controllers/Auth/LoginController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Invalid email or password.'
                ], 401);
            }
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        if ($request->expectsJson()) {
            return response()->json([
                'token' => $token,
                'user' => [
                    'id'             => $user->id,
                    'name'           => $user->name ?? $user->full_name,
                    'email'          => $user->email,
                    'role'           => $user->role,
                    'balance'        => (float) ($user->balance ?? 0),
                    'referral_code'  => $user->referral_code ?? null,
                    'phone'          => $user->phone ?? null,
                    'country'        => $user->country ?? null,
                    'status'         => $user->status ?? 'active',
                    'created_at'     => $user->created_at,
                ],
                'message' => 'Login successful.',
            ]);
        }

        // Blade fallback (kept for completeness, though likely unused now)
        Auth::login($user);
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('investor-investment.dashboard');
    }

    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Logged out successfully.']);
        }
        return redirect()->route('home');
    }
}