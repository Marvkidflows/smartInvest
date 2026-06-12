<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    $user = $request->user();

    return response()->json([
        'id'            => $user->id,
        'name'          => $user->name ?? $user->full_name,
        'email'         => $user->email,
        'role'          => $user->role,
        'balance'       => (float) ($user->balance ?? 0),
        'referral_code' => $user->referral_code ?? null,
        'status'        => $user->status ?? 'active',
    ]);
});