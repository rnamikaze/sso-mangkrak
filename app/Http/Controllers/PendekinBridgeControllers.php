<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\ClientRepository;

class PendekinBridgeControllers extends Controller
{
    public function home()
    {
        $pendekinHost = env("RN_PENDEKIN_HOST");

        $clientId = env("RN_PENDEKIN_CLIENT_ID");

        // Get the client instance
        $clientRepository = new ClientRepository();
        $client = $clientRepository->find($clientId);

        // PASSPORT NOT PRESENT
        if (!$client) {
            return redirect($pendekinHost . "/auth/authorize");
        }

        $userId = Auth::id(); // Get the ID of the currently logged-in user

        $activeTokens = DB::table('oauth_access_tokens')
            ->where('client_id', $clientId)
            ->where('user_id', $userId)
            ->where('revoked', false)
            ->where('expires_at', '>', Carbon::now())
            ->get();

        if ($activeTokens->isEmpty()) {
            return redirect($pendekinHost . "/auth/try-to-athorize");

            return response()->json([
                "success" => false,
                "reason" => "No active tokens found for client ID: $clientId and user ID: $userId"
            ]);
        } else {
            return redirect($pendekinHost);

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
    }
}
