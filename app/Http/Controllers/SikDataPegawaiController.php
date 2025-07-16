<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Support\Str;
use App\Models\SIK\SikProdi;
use Illuminate\Http\Request;
use App\Models\SIK\SikBiodata;
use App\Models\SIK\SikExtraBiodata;
use App\Models\SIK\SikFakultas;
use App\Models\SIK\SikUnitKerja;
use App\Models\SIK\SikJabatanStruktural;

function calculateAge($dateOfBirth, $full = false)
{
    // Get the current date
    $currentDate = Carbon::now();

    // Calculate the difference between the current date and the date of birth
    $diff = $currentDate->diff(Carbon::parse($dateOfBirth));

    // Format the output
    $output = "";

    if ($full) {
        if ($diff->y > 0) {
            $output .= $diff->y . " Tahun";
            if ($diff->y > 1) {
                $output .= "s";
            }
            $output .= ", ";
        }
        if ($diff->m > 0) {
            $output .= $diff->m . " Bulan";
            if ($diff->m > 1) {
                $output .= "s";
            }
            $output .= ", ";
        }
        if ($diff->d > 0) {
            $output .= $diff->d . " Hari";
            if ($diff->d > 1) {
                $output .= "s";
            }
        }
    } else {
        $output .= $diff->y;
    }

    return $output;
}

class SikDataPegawaiController extends Controller
{
    //
    public function filterDataPegawai(Request $filter)
    {
        $filterStatus = intval($filter->status);
        $filterProdi = intval($filter->prodi);

        // return $filterStatus . " - " . $filterProdi;

        $dataPegawaiFiltered = null;

        if ($filterStatus === 0 && $filterProdi === 0) {
            $dataPegawaiFiltered = SikBiodata::where('active', 1)->get();
        }

        if ($filterProdi === 0) {
            $dataPegawaiFiltered = SikBiodata::where('active', 1)->where('status', $filterStatus)->get();
        }

        if ($filterStatus === 0) {
            $dataPegawaiFiltered = SikBiodata::where('active', 1)->where('prodi_id', $filterProdi)->get();
        }

        if ($filterStatus !== 0 && $filterProdi !== 0) {
            $dataPegawaiFiltered = SikBiodata::where('active', 1)->where('prodi_id', $filterProdi)->where('status', $filterStatus)->get();
        }

        // $simpleBiodata = SikBiodata::select('id', 'nik', 'fullname', 'img_storage', 'unit_id', 'kelamin', 'tanggal_lahir', 'status', 'telepon')->get();
        $allUnit = SikUnitKerja::all();

        if ($dataPegawaiFiltered !== null) {
            for ($i = 0; $i < sizeof($dataPegawaiFiltered); $i++) {


                $dataPegawaiFiltered[$i]['age'] = calculateAge($dataPegawaiFiltered[$i]['tanggal_lahir']);

                for ($j = 0; $j < sizeof($allUnit); $j++) {
                    if (intval($allUnit[$j]['id']) === intval($dataPegawaiFiltered[$i]['unit_id'])) {
                        $dataPegawaiFiltered[$i]['unit_name'] = $allUnit[$j]['name'];
                    }
                }
            }
        }

        $getProdi = SikProdi::orderBy('name')->get();

        return Inertia::render('SimpegUnusida/SIKMain', [
            'dataPegawai' => $dataPegawaiFiltered,
            'prodi' => $getProdi,
            'refreshToken' => Str::random(5)
        ]);

        // return var_dump($dataPegawaiFiltered);
    }

    public function getStrukturalList(Request $id)
    {
        $unitId = intval($id->id);

        $strukturalList = SikJabatanStruktural::where('unit_id', $unitId)->select('id', 'name', 'unit_id')->get();

        return response()->json($strukturalList);
    }

    public function customDataView(Request $req)
    {
        $mode = intval($req->mode);
        $status = intval($req->status);

        $unitPointer = $status === 2 || $status === 4 ? 'fakultas_id' : 'unit_id';

        // Mode Code
        // 1 : Dosen Laki Laki
        // 2 : Dosen Perempuan
        // 3 : Dosen + Jados Laki Laki
        // 4 : Dosen + Jados Perempuan
        // 5 : Tendik Laki
        // 6 : Tendik Perempuan
        // 7 : Tendos Laki
        // 8 : Tendos Perempuan

        // 9 : Serdos Sudah
        // 10 : Serdos Belum

        // 11 : asisten ahli-100.00
        // 12 : asisten ahli-150.00
        // 13 : Lektor-200.00
        // 14 : Lektor-300.00
        // 15 : guru besar

        // ID Status Pegawai
        // 1: Tendik
        // 2: Dosen
        // 3: Dosen + Tendik
        // 4: Dosen + Jabatan Dosen

        // ID Jabatan Fungsional
        // 1 : asisten ahli 100
        // 2 : asisten ahli 150
        // 3 : lektor 200
        // 4 : lektor 300
        // 5 : guru besar

        switch ($mode) {
            case 1:
                $dataPegawai = SikBiodata::select('id', 'master_id', 'nik', 'fullname', 'img_storage', $unitPointer, 'kelamin', 'tanggal_lahir', 'status', 'telepon', 'active')
                    ->where('kelamin', 1)->where('status', $status)->where('active', 1)->get();
                break;
            case 2:
                $dataPegawai = SikBiodata::select('id', 'master_id', 'nik', 'fullname', 'img_storage', $unitPointer, 'kelamin', 'tanggal_lahir', 'status', 'telepon', 'active')
                    ->where('kelamin', 0)->where('status', $status)->where('active', 1)->get();
                break;
            case 3:
                $dataPegawai = SikBiodata::select('id', 'master_id', 'nik', 'fullname', 'img_storage', $unitPointer, 'kelamin', 'tanggal_lahir', 'status', 'telepon', 'active')
                    ->where('kelamin', 1)->where('status', $status)->where('active', 1)->get();
                break;
            case 4:
                $dataPegawai = SikBiodata::select('id', 'master_id', 'nik', 'fullname', 'img_storage', $unitPointer, 'kelamin', 'tanggal_lahir', 'status', 'telepon', 'active')
                    ->where('kelamin', 0)->where('status', $status)->where('active', 1)->get();
                break;
            case 5:
                $dataPegawai = SikBiodata::select('id', 'master_id', 'nik', 'fullname', 'img_storage', $unitPointer, 'kelamin', 'tanggal_lahir', 'status', 'telepon', 'active')
                    ->where('kelamin', 1)->where('status', $status)->where('active', 1)->get();
                break;
            case 6:
                $dataPegawai = SikBiodata::select('id', 'master_id', 'nik', 'fullname', 'img_storage', $unitPointer, 'kelamin', 'tanggal_lahir', 'status', 'telepon', 'active')
                    ->where('kelamin', 0)->where('status', $status)->where('active', 1)->get();
                break;
            case 7:
                $dataPegawai = SikBiodata::select('id', 'master_id', 'nik', 'fullname', 'img_storage', $unitPointer, 'kelamin', 'tanggal_lahir', 'status', 'telepon', 'active')
                    ->where('kelamin', 1)->where('status', $status)->where('active', 1)->get();
                break;
            case 8:
                $dataPegawai = SikBiodata::select('id', 'master_id', 'nik', 'fullname', 'img_storage', $unitPointer, 'kelamin', 'tanggal_lahir', 'status', 'telepon', 'active')
                    ->where('kelamin', 0)->where('status', $status)->where('active', 1)->get();
                break;
            case 9:
                $dataPegawai = SikBiodata::select('id', 'master_id', 'nik', 'fullname', 'img_storage', $unitPointer, 'kelamin', 'tanggal_lahir', 'status', 'telepon', 'active')
                    ->whereIn('status', [2, 3, 4])->where('status_serdos', 1)->where('active', 1)->get();
                break;
            case 10:
                $dataPegawai = SikBiodata::select('id', 'master_id', 'nik', 'fullname', 'img_storage', 'fakultas_id', 'kelamin', 'tanggal_lahir', 'status', 'telepon', 'active')
                    ->whereIn('status', [2, 3, 4])->where(function ($query) {
                        $query->where('status_serdos', '!=', 1)
                            ->orWhereNull('status_serdos');
                    })->where('active', 1)->get();
                break;

            case 11:
            case 12:
            case 13:
            case 14:
            case 15:
                $dataPegawai = SikBiodata::select('id', 'master_id', 'nik', 'fullname', 'img_storage', 'fakultas_id', 'unit_id', 'kelamin', 'tanggal_lahir', 'status', 'telepon', 'active')
                    ->whereIn('status', [2, 3, 4])->where('jabatan_fungsional_id', $status)->where('active', 1)->get();
                break;

            case 16:
            case 17:
                $dataExtra = SikExtraBiodata::select('biodata_id')->where('studi_lanjut', $status)->where('active', true)->get();

                $arrayId = [];

                for ($i = 0; $i < sizeof($dataExtra); $i++) {
                    array_push($arrayId, $dataExtra[$i]->biodata_id);
                }

                $dataPegawai = SikBiodata::select('id', 'master_id', 'nik', 'fullname', 'img_storage', 'fakultas_id', 'unit_id', 'kelamin', 'tanggal_lahir', 'status', 'telepon', 'active')
                    ->whereIn('id', $arrayId)->where('active', 1)->get();
                break;

            case 18:
                $dataExtra = SikExtraBiodata::select('biodata_id')->where('kepangkatan', $status)->where('active', true)->get();

                $arrayId = [];

                for ($i = 0; $i < sizeof($dataExtra); $i++) {
                    array_push($arrayId, $dataExtra[$i]->biodata_id);
                }

                $dataPegawai = SikBiodata::select('id', 'master_id', 'nik', 'fullname', 'img_storage', 'fakultas_id', 'unit_id', 'kelamin', 'tanggal_lahir', 'status', 'telepon', 'active')
                    ->whereIn('id', $arrayId)->where('active', 1)->get();
                break;

            default:
                $dataPegawai = [];
                break;
        }

        if ($status === 2 || $status === 4) {
            $allUnit = SikFakultas::where('active', 1)->get();
        } else {
            $allUnit = SikUnitKerja::where('active', 1)->get();
        }

        for ($i = 0; $i < sizeof($dataPegawai); $i++) {

            $dataPegawai[$i]['age'] = calculateAge($dataPegawai[$i]['tanggal_lahir']);

            for ($j = 0; $j < sizeof($allUnit); $j++) {
                if (intval($allUnit[$j]['id']) === intval($dataPegawai[$i][$unitPointer])) {
                    $dataPegawai[$i]['unit_name'] = $allUnit[$j]['name'];
                }
            }
        }

        return Inertia::render('SimpegUnusida/SIKMain', [
            'mode' => $mode,
            'viewCode' => 1,
            'refreshToken' => Str::random(5),
            'dataPegawai' => $dataPegawai,
            'shutDownAxios' => true
        ]);
    }
}
