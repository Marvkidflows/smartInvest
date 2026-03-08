<?php
// app/Http/Middleware/RegistrationStageMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;

class RegistrationStageMiddleware
{
    /**
     * Handle an incoming request to protect registration stages.
     * Users cannot skip ahead to later stages without completing earlier ones.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  int  $requiredStage
     * @return mixed
     */
    public function handle(Request $request, Closure $next, int $requiredStage)
    {
        // Get user ID from session
        $userId = session('registration_user_id');
        
        // If no user ID in session, redirect to stage 1
        if (!$userId) {
            return redirect()->route('register')->with('error', 'Please start registration from the beginning.');
        }

        // Find the user
        $user = User::find($userId);
        
        // If user not found or registration already completed, redirect appropriately
        if (!$user) {
            session()->forget('registration_user_id');
            return redirect()->route('register')->with('error', 'User not found. Please register again.');
        }

        // If registration is already completed, redirect to dashboard
        if ($user->registration_completed) {
            session()->forget('registration_user_id');
            return redirect()->route('login')->with('info', 'Registration already completed. Please log in.');
        }

        // Check if user has completed the required previous stage
        if ($user->registration_stage < $requiredStage) {
            // Redirect to their current stage
            return redirect()->route('register.stage' . ($user->registration_stage + 1))
                ->with('error', 'Please complete the current stage before proceeding.');
        }

        return $next($request);
    }
}