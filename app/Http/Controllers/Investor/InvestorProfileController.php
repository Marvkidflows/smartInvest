<?php
// LOCATION: app/Http/Controllers/Investor/InvestorProfileController.php

namespace App\Http\Controllers\Investor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class InvestorProfileController extends Controller
{
    // GET /investor-investment/investor/profile
    public function show(Request $request)
    {
        $user = Auth::user();

        $data = [
            'user' => [
                'id'             => $user->id,
                'name'           => $user->name ?? $user->full_name,
                'full_name'      => $user->full_name ?? $user->name,
                'email'          => $user->email,
                'phone'          => $user->phone ?? null,
                'country'        => $user->country ?? null,
                'country_code'   => $user->country_code ?? null,
                'address'        => $user->address ?? null,
                'city'           => $user->city ?? null,
                'state'          => $user->state ?? null,
                'postal_code'    => $user->postal_code ?? null,
                'date_of_birth'  => $user->date_of_birth ?? null,
                'balance'        => (float) ($user->balance ?? 0),
                'referral_code'  => $user->referral_code ?? null,
                'status'         => $user->status ?? 'active',
                'role'           => $user->role,
                'created_at'     => $user->created_at->toDateString(),
                'profile_photo'  => $user->profile_photo_url
                                    ?? $user->avatar
                                    ?? null,
            ],
        ];

        if ($request->expectsJson()) {
            return response()->json($data);
        }
        return view('investor.profile.show', $data);
    }

    // GET /investor-investment/investor/profile/edit
    public function edit(Request $request)
    {
        return $this->show($request);
    }

    // PUT /investor-investment/investor/profile
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name'           => ['sometimes', 'string', 'max:255'],
            'full_name'      => ['sometimes', 'string', 'max:255'],
            'email'          => ['sometimes', 'email', 'unique:users,email,' . $user->id],
            'phone'          => ['sometimes', 'string', 'max:20'],
            'country'        => ['sometimes', 'string', 'max:100'],
            'address'        => ['sometimes', 'string', 'max:500'],
            'city'           => ['sometimes', 'string', 'max:100'],
            'state'          => ['nullable', 'string', 'max:100'],
            'postal_code'    => ['nullable', 'string', 'max:20'],
            'date_of_birth'  => ['nullable', 'date'],
            'current_password'   => ['sometimes', 'string'],
            'new_password'       => ['sometimes', 'min:8', 'confirmed'],
        ]);

        // Handle password change
        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Current password is incorrect.'], 422);
                }
                return back()->withErrors(['current_password' => 'Current password is incorrect.']);
            }
            $validated['password'] = Hash::make($request->new_password);
        }

        // Remove password fields from validated before updating
        unset($validated['current_password'], $validated['new_password'], $validated['new_password_confirmation']);

        // Sync name / full_name
        if (isset($validated['full_name'])) {
            $validated['name'] = $validated['full_name'];
        } elseif (isset($validated['name'])) {
            $validated['full_name'] = $validated['name'];
        }

        $user->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Profile updated successfully.',
                'user'    => [
                    'id'    => $user->id,
                    'name'  => $user->fresh()->name ?? $user->full_name,
                    'email' => $user->fresh()->email,
                ],
            ]);
        }

        return back()->with('success', 'Profile updated.');
    }
}
