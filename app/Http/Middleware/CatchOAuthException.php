<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use League\OAuth2\Server\Exception\OAuthServerException;

class CatchOAuthException
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $next($request);
        } catch (OAuthServerException $e) {
            if ($e->getErrorType() === 'invalid_client') {
                return redirect()->route('error.warning')->with('error', 'Invalid client credentials.');
            }

            throw $e;
        }

        // return $next($request);
    }
}
