<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Inertia\Inertia;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use Laravel\Passport\TokenRepository;
use Illuminate\Support\Facades\Session;

class SipekaBridgeControllers extends Controller
{
    //
    public function home()
    {
        $sipekaHost = env("RN_SIPEKA_HOST", "http://localhost:8001");

        $clientId = env("RN_SIPEKA_CLIENT_ID", 8);

        // Get the client instance
        $clientRepository = new ClientRepository();
        $client = $clientRepository->find($clientId);

        if (!$client) {
            return redirect($sipekaHost . "/auth/try-to-autorize");
        }

        $userId = Auth::id(); // Get the ID of the currently logged-in user

        $activeTokens = DB::table('oauth_access_tokens')
            ->where('client_id', $clientId)
            ->where('user_id', $userId)
            ->where('revoked', false)
            ->where('expires_at', '>', Carbon::now())
            ->get();

        if ($activeTokens->isEmpty()) {
            return redirect($sipekaHost . "/auth/redirect-to-autorize");

            return response()->json([
                "success" => false,
                "reason" => "No active tokens found for client ID: $clientId and user ID: $userId"
            ]);
        } else {
            return redirect($sipekaHost);

            $retArr = [];

            foreach ($activeTokens as $token) {
                array_push($retArr, [
                    "Token ID: " => $token->id,
                    "User ID: " => $token->user_id,
                    "Expires at: " => $token->expires_at
                ]);
            }

            return response()->json(["success" => true, "token" => $retArr]);
        }

        // Check if the user has an active token
        // $token = $user->tokens()->where('revoked', false)->first();

        // $sipekaHost = env("RN_SIPEKA_HOST", "http://localhost:8001");

        // return redirect($sipekaHost . "/auth/redirect-to-autorize");

        // if ($token) {
        //     return redirect($sipekaHost);
        // } else {
        //     // No active token found
        //     // return response()->json(['message' => 'No active token found'], 404);

        //     return redirect($sipekaHost . "/auth/redirect-to-autorize");
        // }
        // return Inertia::render('Sipeka/SipekaMain');
    }
}
