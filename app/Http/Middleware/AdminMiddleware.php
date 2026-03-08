<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Check if user is admin
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}