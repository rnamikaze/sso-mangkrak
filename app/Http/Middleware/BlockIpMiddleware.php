<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockIpMiddleware
{
    // Add your blocked IPs here
    protected $blockedIps = [
        // '79.127.143.209',
	'124.198.131.20',            
	'202.157.184.38',
	'178.128.215.89',
    '139.59.79.178',
    '164.92.86.220',
    '45.146.130.98',
    '188.166.191.56'


        // '202.157.184.38',
        // Add more if needed
    ];

    public function handle(Request $request, Closure $next)
    {
        if (in_array($request->ip(), $this->blockedIps)) {
            // return response()->view('blocked');
           abort(404);
	}

        return $next($request);
    }
}
