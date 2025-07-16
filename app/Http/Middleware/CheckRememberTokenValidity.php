<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRememberTokenValidity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        if (!Auth::check() && $request->hasCookie(Auth::guard()->getRecallerName())) {
            $user = Auth::guard()->user();
            if ($user) {
                Auth::login($user); // Log the user in if remember token is still valid
                return to_route('sso.dashboard'); // Redirect to dashboard route
            }
        }

        return $next($request);
    }
}
