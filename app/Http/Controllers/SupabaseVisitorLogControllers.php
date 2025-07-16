<?php

namespace App\Http\Controllers;

use App\Models\SupabaseVisitorLog;
use Illuminate\Http\Request;

class SupabaseVisitorLogControllers extends Controller
{
    //
    public function getAll()
    {
        $allLog = SupabaseVisitorLog::get();

        return response()->json(["data" => $allLog]);
    }
}
