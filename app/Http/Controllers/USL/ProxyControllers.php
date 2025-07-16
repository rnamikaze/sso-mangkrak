<?php

namespace App\Http\Controllers\USL;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class ProxyControllers extends Controller
{
    //
    public function fetchData(Request $request)
    {
        $url = $request->query('url');

        try {
            // Use Laravel's HTTP client to fetch data
            $response = Http::get($url);

            // Forward the response data
            return $response->body();
        } catch (\Exception $e) {
            // Handle errors
            return response()->json(['error' => 'Failed to fetch data.'], 500);
        }
    }
}
