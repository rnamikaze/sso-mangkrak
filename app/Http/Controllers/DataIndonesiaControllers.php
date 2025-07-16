<?php

namespace App\Http\Controllers;

use App\Models\DesaIndonesia;
use App\Models\KabupatenIndonesia;
use App\Models\KecamatanIndonesia;
use Illuminate\Http\Request;

class DataIndonesiaControllers extends Controller
{
    //
    public function getKabupaten($id)
    {
        $kabupaten = KabupatenIndonesia::where('provinsi_id', intval($id))->select('id', 'name', 'name_id')->orderBy('name')->get();


        return response()->json($kabupaten);
    }

    public function getKecamatan($id)
    {
        $kecamatan = KecamatanIndonesia::where('kabupaten_id', intval($id))->select('id', 'name', 'name_id')->orderBy('name')->get();

        return response()->json($kecamatan);
    }

    public function getDesa($id)
    {
        $desa = DesaIndonesia::where('kecamatan_id', intval($id))->select('id', 'name', 'name_id')->orderBy('name')->get();


        return response()->json($desa);
    }
}
