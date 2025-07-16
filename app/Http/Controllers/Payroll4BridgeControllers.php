<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\ClientRepository;

class Payroll4BridgeControllers extends Controller
{
    //
    public function home()
    {
        $sipekaHost = env("RN_SLIP_HOST", "http://localhost:8002");

        $clientId = env("RN_SLIP_CLIENT_ID", 5);

        // Get the client instance
        $clientRepository = new ClientRepository();
        $client = $clientRepository->find($clientId);

        if (!$client) {
            return redirect($sipekaHost . "/auth/authorize");
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

        // return "Haha";
        // $user = Auth::user();

        // // Check if the user has an active token
        // $token = $user->tokens()->where('revoked', false)->first();

        // $sipekaHost = env("RN_SLIP_HOST", "http://localhost:8002");

        // // return redirect($sipekaHost . "/auth/redirect-to-autorize");

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
