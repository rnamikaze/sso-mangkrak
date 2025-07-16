<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ExtraApiControllers extends Controller
{
    public function getPrayerTimes(Request $req)
    {
        $validated = $req->validate([
            "by" => "required|in:@rnamikaze",
            "input_date" => "required|date_format:Y-m-d"
        ]);

        $date = Carbon::parse($validated['input_date'])->format('d-m-Y'); // Get current date in dd-mm-yyyy format

        $url = "https://api.aladhan.com/v1/timingsByCity/{$date}";

        $response = Http::get($url, [
            'city' => 'Sidoarjo',
            'country' => 'ID',
            'x7xapikey' => 'e9965b42fbd413960f6a79b282014e2b',
            'method' => 20,
            'shafaq' => 'general',
            'timezonestring' => 'Asia/Jakarta',
            'calendarMethod' => 'UAQ',
        ]);

        if ($response->successful()) {
            $data = $response->json();

            $hijriDay = $data['data']['date']['hijri']['day'];
            $hijriMonth = $data['data']['date']['hijri']['month']['en'];
            $hijriYear = $data['data']['date']['hijri']['year'];
            $hijriDate = $hijriDay . " " . $hijriMonth . " " . $hijriYear;
            $calcMethod = $data['data']['meta']['method']['name'];

            // Extract only required prayer times
            $filteredData = [
                'date' => $data['data']['date']['readable'] ?? null,
                'shubuh' => $data['data']['timings']['Fajr'] ?? null,
                'imsak' => $data['data']['timings']['Imsak'] ?? null,
                'dzuhur' => $data['data']['timings']['Dhuhr'] ?? null,
                'ashar' => $data['data']['timings']['Asr'] ?? null,
                'maghrib' => $data['data']['timings']['Maghrib'] ?? null,
                'isya' => $data['data']['timings']['Isha'] ?? null,
                'sunrise' => $data['data']['timings']['Sunrise'] ?? null,
                'hijri_date' => $hijriDate,
                'calc_method' => $calcMethod
            ];

            return response()->json($filteredData);
        } else {
            return response()->json(['error' => 'Failed to fetch data'], 500);
        }
    }

    public function getPrayerTimes3Days(Request $req)
    {
        $validated = $req->validate([
            "by" => "required|in:@rnamikaze",
            "input_date" => "required|date_format:Y-m-d"
        ]);

        $startDate = Carbon::parse($validated['input_date']);
        $filteredData = ["prayers_time" => []];

        for ($i = 0; $i < 3; $i++) {
            $date = $startDate->copy()->addDays($i)->format('d-m-Y'); // Format for API request
            $displayDate = $startDate->copy()->addDays($i)->format('Y-m-d'); // Key format

            $url = "https://api.aladhan.com/v1/timingsByCity/{$date}";
            $response = Http::get($url, [
                'city' => 'Sidoarjo',
                'country' => 'ID',
                'x7xapikey' => 'e9965b42fbd413960f6a79b282014e2b',
                'method' => 20,
                'shafaq' => 'general',
                'timezonestring' => 'Asia/Jakarta',
                'calendarMethod' => 'UAQ',
            ]);

            if ($response->successful()) {
                $data = $response->json();

                $hijriDate = '';
                $calcMethod = '';

                $hijriDay = $data['data']['date']['hijri']['day'];
                $hijriMonth = $data['data']['date']['hijri']['month']['en'];
                $hijriYear = $data['data']['date']['hijri']['year'];
                $hijriDate = "$hijriDay $hijriMonth $hijriYear";
                $calcMethod = $data['data']['meta']['method']['name'];

                $filteredData["prayers_time"][] = [
                    'date' => $displayDate,
                    'date_readable' => $data['data']['date']['readable'] ?? null,
                    'shubuh' => $data['data']['timings']['Fajr'] ?? null,
                    'imsak' => $data['data']['timings']['Imsak'] ?? null,
                    'dzuhur' => $data['data']['timings']['Dhuhr'] ?? null,
                    'ashar' => $data['data']['timings']['Asr'] ?? null,
                    'maghrib' => $data['data']['timings']['Maghrib'] ?? null,
                    'isya' => $data['data']['timings']['Isha'] ?? null,
                    'sunrise' => $data['data']['timings']['Sunrise'] ?? null,
                    'hijri_date' => $hijriDate,
                    'calc_method' => $calcMethod
                ];
            } else {
                return response()->json(['error' => 'Failed to fetch data'], 500);
            }
        }

        return response()->json($filteredData);
    }
}
