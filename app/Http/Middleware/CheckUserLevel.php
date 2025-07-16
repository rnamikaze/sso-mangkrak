<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Inertia\Inertia;

class CheckUserLevel
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $urlRoute = $request->fullUrl();

        $activeUser = User::find(intval(Auth::id()));

        if (Auth::check()) {
            $activeUsername = Auth::user()->username;
        } else {
            // $activeUsername = null; // or any default value you want to assign
            return redirect('/sso-dashboard');
        }

        // $activeUsername = Auth::user()->username;
        // Special User have an id below from 9
        if ($activeUsername == "superadmin") {
            return $next($request);
        }

        if ($activeUser) {
            $allowedApp = unserialize($activeUser->allowed_app_arr);

            if ($allowedApp) {
                $allowPass = false;

                if ($allowedApp === false) {
                    $allowPass = true;
                } else {
                    for ($i = 0; $i < sizeof($allowedApp); $i++) {
                        if (str_contains($urlRoute, strval($allowedApp[$i]))) {
                            $allowPass = true;
                        }
                    }
                }

                if ($allowPass) {
                    return $next($request);
                } else {
                    return redirect('/sso-dashboard/forbidden');
                    // return redirect('/debug/' . $allowedApp[0]);
                }
            }
        }

        return Inertia::render('Guest/NewLogin', ['loginStatus' => null, 'warningText' => ['Anda Harus Login Dulu !', 'warning']]);
    }
}
