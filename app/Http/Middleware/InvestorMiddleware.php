<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class InvestorMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || !auth()->user()->isInvestor()) {
            abort(403, 'Unauthorized access');
        }

        return $next($request);
    }
}
