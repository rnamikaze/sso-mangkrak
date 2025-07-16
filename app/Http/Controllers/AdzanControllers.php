<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AdzanControllers extends Controller
{
    //
    public function retrieveAdzanbyCity()
    {
        $city = request('city', 'sidoarjo'); // Default to 'sidoarjo' if no city is provided
        $method = 5;
        $apiKey = 'e8ffd3a42b3fedab2765526835655acb';
        $response = Http::get("http://muslimsalat.com/{$city}/daily/{$method}.json", [
            'key' => $apiKey,
        ]);

        return $response->json();
    }
}
