<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MahasiswaData;

class MahasiswaDataController extends Controller
{
    //
    // public function getMahasiswa()
    // {
    //     $allMahasiswa = MahasiswaData::get();

    //     return response()->json(["data" => $allMahasiswa]);
    // }

    //
    public function getMahasiswa($length = 0)
    {
        $lengthNum = intval($length);

        if ($lengthNum < 1) {
            return response()->json(["reason" => "Invalid Parameter"]);
        }

        $allMahasiswa = MahasiswaData::orderBy('id', 'desc')->take($lengthNum)->get();

        $allProdi = DB::connection('mysql_sipoma_unusida')->table('program_studi')->get();
        $allFakultas = DB::connection('mysql_sipoma_unusida')->table('fakultas')->get();

        return response()->json(["data" => $allMahasiswa, "allProdi" => $allProdi, "allFakultas" => $allFakultas]);
    }
}
