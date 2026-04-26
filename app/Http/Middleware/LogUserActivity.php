<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class LogUserActivity
{
    public function handle(Request $request, Closure $next)
{
    if (Auth::check()) {
        Cache::put(
            'user-is-online-' . Auth::id(),
            true,
            now()->addMinutes(5)
        );
    }

    return $next($request);
}
}