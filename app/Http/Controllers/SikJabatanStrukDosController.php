<?php

namespace App\Http\Controllers;

use App\Models\SIK\SikJabatanStrukDos;
use Illuminate\Http\Request;

class SikJabatanStrukDosController extends Controller
{
    //
    public function getJabatanStrukDosList()
    {
        $allJabatanJabatanStrukDos = SikJabatanStrukDos::where('active', 1)->get();

        return response()->json($allJabatanJabatanStrukDos);
    }
}
