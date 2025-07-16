<?php

namespace App\Http\Controllers\AutoSlip;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Aslip\MailerCounterModel;

function convertDate($dateValue)
{
    $formattedDate = Carbon::parse($dateValue)->format('d/m/Y');
    return $formattedDate;
}

class MailerCounterControllers extends Controller
{
    //
    public function checkToday()
    {
        // Get the current UTC datetime and set time to 00:00:00
        $utcDateTime = Carbon::now('UTC')->startOfDay();

        // // Retrieve records from the database where created_at is equal to utcDateTime
        // $records = MailerCounterModel::whereDate('created_at', $utcDateTime)->first();

        $counterData = null;

        $records = MailerCounterModel::whereDate('created_at', now('Europe/London')->toDateString())->first();

        if (!$records) {
            // Create a new row only if no record exists for the current date
            $newRecord = MailerCounterModel::create([
                // Add your column values here
                'daily_counter' => 0,
                'active' => 1
                // Add more columns as needed
            ]);
            $counterData = 0;
            // return response()->json(['message' => 'New record created', 'data' => $newRecord], 201);
        } else {
            $counterData = $records->daily_counter;
        }

        // Check if any records match the condition
        // if ($records) {
        //     // Records found for the same date
        //     $counterData = $records->daily_counter;
        // } else {
        //     // No records found for today
        //     $createNew = new MailerCounterModel;

        //     $createNew->active = 1;
        //     $createNew->daily_counter = 0;

        //     $createNew->save();

        //     $counterData = 0;
        //     // return response()->json(['message' => 'No records found for today'], 404);
        // }

        return response()->json(["dailyCounter" => $counterData]);
    }

    public function addOne()
    {
        // Get the current UTC datetime and set time to 00:00:00
        $utcDateTime = Carbon::now('UTC')->startOfDay();

        // Retrieve records from the database where created_at is equal to utcDateTime
        // $targetCounter = MailerCounterModel::whereDate('created_at', $utcDateTime)->first();

        $newCounter = 0;

        $records = MailerCounterModel::whereDate('created_at', now('Europe/London')->toDateString())->first();

        if (!$records) {
            // Create a new row only if no record exists for the current date
            $newRecord = MailerCounterModel::create([
                // Add your column values here
                'daily_counter' => 0,
                'active' => 1
                // Add more columns as needed
            ]);
            $counterData = 0;
            // return response()->json(['message' => 'New record created', 'data' => $newRecord], 201);
        } else {
            // $counterData = $records->daily_counter;
            $editCounter = MailerCounterModel::whereDate('created_at', now('Europe/London')->toDateString())->first();
            $newCounter = intval($editCounter->daily_counter) + 1;
            $editCounter->daily_counter = $newCounter;

            $editCounter->save();
        }

        // // Check if any records match the condition
        // if ($targetCounter) {
        //     // Records found for the same date
        //     $editCounter = MailerCounterModel::find($targetCounter->id);
        //     $newCounter = intval($editCounter->daily_counter) + 1;
        //     $editCounter->daily_counter = $newCounter;

        //     $editCounter->save();
        // } else {
        //     // No records found for today
        //     $createNew = new MailerCounterModel;

        //     $createNew->active = 1;
        //     $createNew->daily_counter = 0;

        //     $createNew->save();

        //     // $counterData = 0;
        //     // return response()->json(['message' => 'No records found for today'], 404);
        // }

        return response()->json("Failed");
    }

    public function getDailyTable()
    {
        $dailyTabel = MailerCounterModel::all();

        for ($i = 0; $i < sizeof($dailyTabel); $i++) {
            $dailyTabel[$i]->formated_date = convertDate($dailyTabel[$i]->created_at);
        }

        return response()->json($dailyTabel);
    }
}
