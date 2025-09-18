<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class IpExceedAlert
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    protected $oscDiscordWebhook = "https://discordapp.com/api/webhooks/1403374106786791588/y_m4q4sIpeee6rh8EkxIv_b-L_vo4XGT6--ZyvjfYU9aWf0FC1ZYcGikzzguQ9z8_QiX";

    public function handle($request, Closure $next)
    {
        $ip = $request->ip();
        $key = "rate_limit_{$ip}";
        $maxRequests = 5; // max per second
        $ttl = 1; // seconds

        // Increment request count
        $count = Cache::get($key, 0) + 1;
        Cache::put($key, $count, now()->addSeconds($ttl));

        // If exceeds limit, send Discord alert
        if ($count > $maxRequests) {
            $this->sendDiscordAlert($ip, $count);
        }

        return $next($request);
    }

    protected function sendDiscordAlert($ip, $count)
    {
        try {
            Http::post($this->discordWebhook, [
                'content' => ":rotating_light: **Rate Limit Alert**\nIP: `{$ip}` exceeded {$count} req/sec"
            ]);
        } catch (\Exception $e) {
            // Avoid breaking requests if Discord fails
            \Log::error("Failed to send Discord alert: " . $e->getMessage());
        }
    }
}
