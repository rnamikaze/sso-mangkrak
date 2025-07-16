<?php

namespace App\Http\Controllers\SIK;

use App\Http\Controllers\Controller;
use App\Models\SIK\SikBiodata;
use App\Models\User;
use Illuminate\Http\Request;

function removeSpaces($str)
{
    return str_replace(' ', '', $str);
}

class BiodataController extends Controller
{
    //
    public function checkEmail(Request $email)
    {
        $rawEmail = removeSpaces($email->email);

        $biodata = User::where('email', $rawEmail)->first();

        // return response()->json($biodata);


        if ($biodata) {
            return response()->json([1, $rawEmail]);
        } else {
            return response()->json([0, $rawEmail]);
        }
    }

    public function checkNik(Request $nik)
    {
        $rawnik = $nik->nik;

        $biodata = SikBiodata::where('nik', $rawnik)->first();

        if ($biodata) {
            return response()->json([1, $rawnik]);
        } else {
            $userBio = User::where('nik', $rawnik)->first();

            if ($userBio) {
                return response()->json([1, $rawnik]);
            }

            return response()->json([0, $rawnik]);
        }
    }
}
