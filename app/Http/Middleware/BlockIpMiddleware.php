<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;

class BlockIpMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();
        
        // Check Redis set
        if (Redis::sismember('blocked_ips', $ip)) {
        	if ($request->expectsJson() || $request->isJson()) {
                return response()->json([
                    'message' => 'Access denied. Your IP is blocked.',
                    "ip" => $ip,
                    "definity" => "by Sparklabz"
                ], 403);
            }
        
            // If browser: redirect to external website
            return redirect()->away('https://definity.sparklabz.cloud');
        }

        return $next($request);
    }
}