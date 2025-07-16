<?php

namespace App\Http\Controllers\SIK;

use Carbon\Carbon;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Support\Str;
use App\Models\SIK\SikProdi;
use Illuminate\Http\Request;
use App\Models\DesaIndonesia;
use App\Models\SIK\SikBiodata;
use App\Models\SIK\SikFakultas;
use App\Models\SIK\SikUnitKerja;
use App\Models\ProvinsiIndonesia;
use App\Models\KabupatenIndonesia;
use App\Models\KecamatanIndonesia;
use App\Models\SIK\SikKinerjaTask;
use App\Models\SIK\SikExtraBiodata;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\SIK\SikJabatanStrukDos;
use Illuminate\Support\Facades\Storage;
use App\Models\SIK\SikJabatanFungsional;
use App\Models\SIK\SikJabatanStruktural;
use App\Models\SikBaseKpi;
use App\Models\SikStaffFakultasModel;

$rawActiveBio = '
            {
                "id": 161,
                "created_at": "2024-03-07T01:16:08.000000Z",
                "updated_at": "2024-03-13T04:52:46.000000Z",
                "nik": "17081945",
                "nik_ktp": "",
                "master_id": 468,
                "fullname": "Admin",
                "kelamin": 0,
                "img_storage": null,
                "fakultas_id": null,
                "prodi_id": null,
                "tinggi_badan": null,
                "berat_badan": null,
                "tempat_lahir": "Jakarta",
                "tanggal_lahir": "1945-08-17",
                "awal_kerja": "1945-08-17",
                "alamat_rumah": null,
                "telepon": "",
                "email": "17081945",
                "pendidikan_terakhir": null,
                "no_bpjs_kes": "",
                "no_bpjs_kerja": "",
                "alamat": "",
                "kerabat_nama": null,
                "kerabat_hubungan": null,
                "kerabat_telepon": null,
                "status": "2",
                "unit_id": 0,
                "jabatan_struktural_id": 51,
                "jabatan_fungsional_id": null,
                "status_serdos": null,
                "active": 1,
                "jabatan_level": 0,
                "nama_jabatan": "Special Access"
            }
            ';

function generateRandomImageName($ext = 'jpg')
{
    $timestamp = time();
    $randomString = bin2hex(random_bytes(5));
    $imageName = "{$timestamp}_{$randomString}.${ext}";

    return $imageName;
}

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


class SikDashboardControllers extends Controller
{
    public function getActiveBio()
    {
        $activeID = Auth::id();
        $activeBioRaw = '
            {
                "id": 161,
                "created_at": "2024-03-07T01:16:08.000000Z",
                "updated_at": "2024-03-13T04:52:46.000000Z",
                "nik": "17081945",
                "nik_ktp": "",
                "master_id": 468,
                "fullname": "Admin",
                "kelamin": 0,
                "img_storage": null,
                "fakultas_id": null,
                "prodi_id": null,
                "tinggi_badan": null,
                "berat_badan": null,
                "tempat_lahir": "Jakarta",
                "tanggal_lahir": "1945-08-17",
                "awal_kerja": "1945-08-17",
                "alamat_rumah": null,
                "telepon": "",
                "email": "17081945",
                "pendidikan_terakhir": null,
                "no_bpjs_kes": "",
                "no_bpjs_kerja": "",
                "alamat": "",
                "kerabat_nama": null,
                "kerabat_hubungan": null,
                "kerabat_telepon": null,
                "status": "2",
                "unit_id": 0,
                "jabatan_struktural_id": 51,
                "jabatan_fungsional_id": null,
                "status_serdos": null,
                "active": 1,
                "jabatan_level": 0,
                "nama_jabatan": "Special Access"
            }
            ';
        if ($activeID <= 10) {
            $activeBio = json_decode($activeBioRaw);
        } else {
            $activeBio = SikBiodata::where('master_id', $activeID)->first();
        }

        return response()->json(["activeBio" => $activeBio, "id" => $activeID]);
    }

    public function getUsernameKinerja()
    {
        $targetId = intval(Auth::id());
        $targetUser = User::find($targetId);

        $targetUsername = $targetUser->username;

        return response()->json(["username" => $targetUsername]);
    }

    public function extraDash()
    {
        // Fetch all Jabatan Fungsional
        $allJabFun = SikJabatanFungsional::where('active', 1)
            ->where('id', '!=', 0)
            ->select('name', 'id')
            ->get();

        // Grouped counts to optimize database queries
        $groupedCounts = SikBiodata::where('active', 1)
            ->whereIn('status', [1, 2, 3, 4])
            ->selectRaw("
            SUM(kelamin = 1 AND status = 2) as dosenLaki,
            SUM(kelamin = 0 AND status = 2) as dosenPerempuan,
            SUM(kelamin = 1 AND status = 4) as dosenJadosLaki,
            SUM(kelamin = 0 AND status = 4) as dosenJadosPerempuan,
            SUM(kelamin = 1 AND status = 1) as tendikLaki,
            SUM(kelamin = 0 AND status = 1) as tendikPerempuan,
            SUM(kelamin = 1 AND status = 3) as tendikDosenLaki,
            SUM(kelamin = 0 AND status = 3) as tendikDosenPerempuan
        ")
            ->first();

        // Functional Positions
        $functionalCounts = SikBiodata::where('active', 1)
            ->whereIn('status', [2, 3, 4])
            ->whereIn('jabatan_fungsional_id', [1, 2, 3, 4, 5])
            ->selectRaw("
            SUM(jabatan_fungsional_id = 1) as totalAstAhli1,
            SUM(jabatan_fungsional_id = 2) as totalAstAhli2,
            SUM(jabatan_fungsional_id = 3) as totalLektor2,
            SUM(jabatan_fungsional_id = 4) as totalLektor3,
            SUM(jabatan_fungsional_id = 5) as totalGubes
        ")
            ->first();

        // Serdos Status
        $sudahSerdos = SikBiodata::where('active', 1)
            ->whereIn('status', [2, 3, 4])
            ->where('status_serdos', 1)
            ->count();

        $belumSerdos = ($groupedCounts->dosenLaki + $groupedCounts->dosenPerempuan +
            $groupedCounts->tendikDosenLaki + $groupedCounts->tendikDosenPerempuan +
            $groupedCounts->dosenJadosLaki + $groupedCounts->dosenJadosPerempuan) - $sudahSerdos;

        // Kepangkatan Processing
        $allKepangkatan = SikExtraBiodata::whereNotNull('kepangkatan')
            ->where('active', 1)
            ->pluck('kepangkatan')
            ->map(fn($val) => intval($val))
            ->toArray();

        $arrayKepangkatan = array_count_values($allKepangkatan) + array_fill(0, 13, 0);

        // Studi Lanjut
        $studiCounts = SikExtraBiodata::where('active', 1)
            ->whereIn('studi_lanjut', [0, 1])
            ->selectRaw("
            SUM(studi_lanjut = 0) as studiLanjutProses,
            SUM(studi_lanjut = 1) as studiLanjutSelesai
        ")
            ->first();

        // Response Payload
        return response()->json([
            "allJabFunTitle" => $allJabFun,
            "allJabFunVal" => [
                "asistenAhli100" => $functionalCounts->totalAstAhli1,
                "asistenAhli150" => $functionalCounts->totalAstAhli2,
                "lektor200" => $functionalCounts->totalLektor2,
                "lektor300" => $functionalCounts->totalLektor3,
                "guruBesar" => $functionalCounts->totalGubes
            ],
            "pegawaiKelamin" => [
                "dosenLaki" => $groupedCounts->dosenLaki,
                "dosenPerempuan" => $groupedCounts->dosenPerempuan,
                "tendikLaki" => $groupedCounts->tendikLaki,
                "tendikPerempuan" => $groupedCounts->tendikPerempuan,
                "tendikDosenLaki" => $groupedCounts->tendikDosenLaki,
                "tendikDosenPerempuan" => $groupedCounts->tendikDosenPerempuan,
                "dosenJadosLaki" => $groupedCounts->dosenJadosLaki,
                "dosenJadosPerempuan" => $groupedCounts->dosenJadosPerempuan
            ],
            "statusSerdos" => [
                "sudah" => $sudahSerdos,
                "belum" => $belumSerdos
            ],
            "total" => [
                "dosen" => $groupedCounts->dosenLaki + $groupedCounts->dosenPerempuan,
                "tendik" => $groupedCounts->tendikLaki + $groupedCounts->tendikPerempuan,
                "tendikDosen" => $groupedCounts->tendikDosenLaki + $groupedCounts->tendikDosenPerempuan,
                "dosenJados" => $groupedCounts->dosenJadosLaki + $groupedCounts->dosenJadosPerempuan
            ],
            "lainnya" => [
                "studi_lanjut" => [$studiCounts->studiLanjutProses, $studiCounts->studiLanjutSelesai],
                "kepangkatan" => $arrayKepangkatan
            ]
        ]);
    }


    public function dashboard()
    {
        $activeID = Auth::id();

        $activeUsername = Auth::user()->username;

        // Tendik = 1
        // Dosen = 2
        // Dosen + Tendik = 3
        // Dosen + Jabatan Dosen = 4

        $totalDosenLaki = SikBiodata::whereIn('status', [2, 3, 4])->where('kelamin', 1)->count();
        $totalDosenPerempuan = SikBiodata::whereIn('status', [2, 3, 4])->where('kelamin', 0)->count();
        $totalTendikLaki = SikBiodata::whereIn('status', [1, 3])->where('kelamin', 1)->count();
        $totalTendikPerempuan = SikBiodata::whereIn('status', [1, 3])->where('kelamin', 0)->count();
        $sudahSerdos = SikBiodata::whereIn('status', [2, 3, 4])->where('status_serdos', 1)->count();
        $belumSerdos = SikBiodata::whereIn('status', [2, 3, 4])->where('status_serdos', 0)->count();

        $totalAstAhli = SikBiodata::where('jabatan_fungsional_id', 1)->count();
        $totalLektor = SikBiodata::where('jabatan_fungsional_id', 2)->count();
        $totalLektorKepala = SikBiodata::where('jabatan_fungsional_id', 3)->count();
        $totalGubes = SikBiodata::where('jabatan_fungsional_id', 4)->count();

        $dashPayload = [
            $totalDosenLaki,
            $totalDosenPerempuan,
            $totalTendikLaki,
            $totalTendikPerempuan,
            $sudahSerdos,
            $belumSerdos,
            $totalAstAhli,
            $totalLektor,
            $totalLektorKepala,
            $totalGubes,
        ];

        $activeBio = SikBiodata::where('master_id', $activeID)->first();

        // Determine SDM Account
        // $isSDM = $activeUsername === "adminsdmunusida" ? true : false;

        // Determine if account is special, example: superadmin
        if ($activeBio === null) {
            $rawActiveBio = '
            {
                "id": 161,
                "created_at": "2024-03-07T01:16:08.000000Z",
                "updated_at": "2024-03-13T04:52:46.000000Z",
                "nik": "17081945",
                "nik_ktp": "",
                "master_id": 468,
                "fullname": "Admin",
                "kelamin": 0,
                "img_storage": null,
                "fakultas_id": null,
                "prodi_id": null,
                "tinggi_badan": null,
                "berat_badan": null,
                "tempat_lahir": "Jakarta",
                "tanggal_lahir": "1945-08-17",
                "awal_kerja": "1945-08-17",
                "alamat_rumah": null,
                "telepon": "",
                "email": "17081945",
                "pendidikan_terakhir": null,
                "no_bpjs_kes": "",
                "no_bpjs_kerja": "",
                "alamat": "",
                "kerabat_nama": null,
                "kerabat_hubungan": null,
                "kerabat_telepon": null,
                "status": "2",
                "unit_id": 0,
                "jabatan_struktural_id": 51,
                "jabatan_fungsional_id": null,
                "status_serdos": null,
                "active": 1,
                "jabatan_level": 0,
                "nama_jabatan": "Special Access",
                "data_filled": 1
            }
            ';

            $activeBio = json_decode($rawActiveBio);

            $returnView = 2;
            if ($activeUsername === "superadmin") {
                $returnView = 0;
            }

            return Inertia::render('SimpegUnusida/SIKMain', [
                'dataDash' => $dashPayload,
                'activeBioProps' => $activeBio,
                'viewId' => [$returnView, 0]
            ]);
            // return "test";
        } else {
            $targetProdi = SikProdi::where('id', intval($activeBio->prodi_id))->select('name')->first();
            $prodiName = $targetProdi->name;
            $targetStrukdos = SikJabatanStrukDos::where('id', intval($activeBio->jabatan_strukdos_id))->select('name')->first();

            if (intval($activeBio->status) === 5) {
                $targetStaffFakultas = SikStaffFakultasModel::find(intval($activeBio->staff_fakultas_id));

                $activeBio->jabatan_strukdos = $targetStaffFakultas->name;
                $activeBio->nama_prodi = "Staff Fakultas";
            } else {
                if ($activeBio->jabatan_strukdos_id === null || $activeBio->jabatan_strukdos_id === 0) {
                    $jabatanStrukdosName = "Null";
                } else {
                    $jabatanStrukdosName = $targetStrukdos->name;

                    $activeBio->jabatan_strukdos = $jabatanStrukdosName;
                    $activeBio->nama_prodi = ucwords($prodiName);
                }
            }
        }

        // Get target account Jabatan Struktural name
        $getTargetStruktural = SikJabatanStruktural::find(intval($activeBio->jabatan_struktural_id));

        $activeBio->nama_jabatan = $getTargetStruktural->name;

        if ($activeUsername === "adminsdmunusida") {
            $activeBio->data_filled = 1;
        }

        // return "test";

        return Inertia::render('SimpegUnusida/SIKMain', [
            'dataDash' => $dashPayload,
            'activeBioProps' => $activeBio
        ]);
    }

    public function dataDash()
    {
        $totalDosenLaki = SikBiodata::whereIn('status', [2, 3, 4])->where('kelamin', 1)->count();
        $totalDosenPerempuan = SikBiodata::whereIn('status', [2, 3, 4])->where('kelamin', 0)->count();
        $totalTendikLaki = SikBiodata::whereIn('status', [1, 3])->where('kelamin', 1)->count();
        $totalTendikPerempuan = SikBiodata::whereIn('status', [1, 3])->where('kelamin', 0)->count();
        $sudahSerdos = SikBiodata::whereIn('status', [2, 3, 4])->where('status_serdos', 1)->count();
        $belumSerdos = SikBiodata::whereIn('status', [2, 3, 4])->where('status_serdos', 0)->count();

        $totalAstAhli = SikBiodata::where('jabatan_fungsional_id', 1)->count();
        $totalLektor = SikBiodata::where('jabatan_fungsional_id', 2)->count();
        $totalLektorKepala = SikBiodata::where('jabatan_fungsional_id', 3)->count();
        $totalGubes = SikBiodata::where('jabatan_fungsional_id', 4)->count();

        $dashPayload = [
            $totalDosenLaki,
            $totalDosenPerempuan,
            $totalTendikLaki,
            $totalTendikPerempuan,
            $sudahSerdos,
            $belumSerdos,
            $totalAstAhli,
            $totalLektor,
            $totalLektorKepala,
            $totalGubes
        ];

        $activeBio = SikBiodata::where('master_id', Auth::id())->first();

        if ($activeBio === null) {
            global $rawActiveBio;

            $activeBio = json_decode($rawActiveBio);

            return Inertia::render('SimpegUnusida/SIKMain', [
                'dataDash' => $dashPayload,
                'activeBioProps' => $activeBio
            ]);
        }

        $getTargetStruktural = SikJabatanStruktural::find(intval($activeBio->jabatan_struktural_id));

        $activeBio->nama_jabatan = $getTargetStruktural->name;

        return Inertia::render('SimpegUnusida/SIKMain', [
            'dataDash' => $dashPayload,
            'activeBioProps' => $activeBio
        ]);
    }

    public function getUnitList(Request $req)
    {
        $validated = $req->validate([
            "by" => "required|in:@rnamikaze",
        ]);

        $allUnit = SikUnitKerja::where('active', 1)->get();
        $allFakultas = SikFakultas::where('active', 1)->get();
        $getProdi = SikProdi::orderBy('name')->where('active', 1)->get();

        return response()->json([
            "success" => true,
            "unitList" => $allUnit,
            "fakultasList" => $allFakultas,
            "prodiList" => $getProdi
        ], 200);
    }

    public function getFakultasList(Request $req)
    {
        $validated = $req->validate([
            "by" => "required|in:@rnamikaze",
        ]);

        $allFakultas = SikFakultas::where('active', 1)->get();

        return response()->json([
            "success" => true,
            "fakultasList" => $allFakultas
        ], 200);
    }

    public function getProgramStudiList(Request $req)
    {
        $validated = $req->validate([
            "by" => "required|in:@rnamikaze",
        ]);

        $getProdi = SikProdi::orderBy('name')->where('active', 1)->get();

        return response()->json([
            "success" => true,
            "prodiList" => $getProdi
        ], 200);
    }

    public function dataPegawaiAxios(Request $req)
    {
        $isInitial = $req->initial;
        $status = intval($req->statusSelected);
        $prodi = intval($req->prodiSelected);
        $fakultas = intval($req->fakultasSelected);
        $unit = intval($req->unitSelected);
        $sortMode = intval($req->sortMode);
        $sortName = "asc";

        switch ($sortMode) {
            case 1:
                $sortName = "asc";
                break;

            default:
                $sortName = "desc";
                break;
        }

        $allUnit = SikUnitKerja::where('active', 1)->get();
        $allFakultas = SikFakultas::where('active', 1)->get();

        // Dont use where('active', 1) to SikBiodata Model, this function is for display All Biodata wheter is active or not
        if ($isInitial) {
            $simpleBiodata = SikBiodata::select('id', 'master_id', 'nik', 'fullname', 'img_storage', 'unit_id', 'kelamin', 'tanggal_lahir', 'status', 'telepon', 'active', 'fakultas_id', 'unit_id')->get();

            for ($i = 0; $i < sizeof($simpleBiodata); $i++) {

                $simpleBiodata[$i]['age'] = calculateAge($simpleBiodata[$i]['tanggal_lahir']);

                // for ($j = 0; $j < sizeof($allUnit); $j++) {
                //     if (intval($allUnit[$j]['id']) === intval($simpleBiodata[$i]['unit_id'])) {
                //         $simpleBiodata[$i]['unit_name'] = $allUnit[$j]['name'];
                //     }
                // }

                // $unit = $allUnit->firstWhere('id', $specifiedId);
                if ($simpleBiodata[$i]['status'] === "2" || $simpleBiodata[$i]['status'] === "4" || $simpleBiodata[$i]['status'] === "5") {
                    $fakultasId = intval($simpleBiodata[$i]['fakultas_id']);
                    $targetFakultas = $allFakultas->firstWhere('id', $fakultasId);
                    $simpleBiodata[$i]['unit_name'] = "Fakultas " . $targetFakultas->name;
                } else {
                    $unitId = intval($simpleBiodata[$i]['unit_id']);
                    $targetUnit = $allUnit->firstWhere('id', $unitId);
                    $simpleBiodata[$i]['unit_name'] = $targetUnit->name;
                }
            }

            // $getProdi = SikProdi::orderBy('name')->where('active', 1)->get();

            for ($i = 0; $i < sizeof($simpleBiodata); $i++) {
                $targetBiodata = SikBiodata::where('master_id', intval($simpleBiodata[$i]['master_id']))
                    ->first();
                $targetBiodataOkay = false;

                $targetExtraBiodata = SikExtraBiodata::where('biodata_id', intval($targetBiodata->id))
                    ->first();
                $targetExtraBiodataOkay = false;

                // Check if the user exists and if the specific columns are not null
                $biodataStatus = intval($targetBiodata->status);

                // Staff Fakultas [5]:
                $columnsToCheck = [
                    'nik',
                    'nik_ktp',
                    'fullname',
                    'kelamin',
                    'tempat_lahir',
                    'tanggal_lahir',
                    'telepon',
                    'email',
                    'pendidikan_terakhir',
                    // Special
                    'fakultas_id',
                    'staff_fakultas_id',
                    'status_kerja'
                ];

                // Tendik [1]:
                if ($biodataStatus === 1) {
                    $columnsToCheck = [
                        'nik',
                        'nik_ktp',
                        'fullname',
                        'kelamin',
                        'tempat_lahir',
                        'tanggal_lahir',
                        'telepon',
                        'email',
                        'pendidikan_terakhir',
                        // Special
                        'unit_id',
                        'jabatan_struktural_id',
                        'status_kerja'
                    ];
                }
                // Dosen [2]:
                else if ($biodataStatus === 2) {
                    $columnsToCheck = [
                        'nik',
                        'nik_ktp',
                        'fullname',
                        'kelamin',
                        'tempat_lahir',
                        'tanggal_lahir',
                        'telepon',
                        'email',
                        'pendidikan_terakhir',
                        // Special
                        'fakultas_id',
                        'prodi_id',
                    ];
                }
                // Dosen + Tendik [3]:
                else if ($biodataStatus === 3) {
                    $columnsToCheck = [
                        'nik',
                        'nik_ktp',
                        'fullname',
                        'kelamin',
                        'tempat_lahir',
                        'tanggal_lahir',
                        'telepon',
                        'email',
                        'pendidikan_terakhir',
                        // Special
                        'unit_id',
                        'jabatan_struktural_id',
                        'fakultas_id',
                        'prodi_id',
                        'status_kerja'
                    ];
                }
                // Dosen + Jabatan Dosen [4]:
                else if ($biodataStatus === 4) {
                    $columnsToCheck = [
                        'nik',
                        'nik_ktp',
                        'fullname',
                        'kelamin',
                        'tempat_lahir',
                        'tanggal_lahir',
                        'telepon',
                        'email',
                        'pendidikan_terakhir',
                        // Special
                        'fakultas_id',
                        'jabatan_strukdos_id',
                        'prodi_id'
                    ];
                }

                if ($targetBiodata) {
                    // $columnsToCheck = [
                    //     'nik',
                    //     'nik_ktp',
                    //     'fullname',
                    //     'kelamin',
                    //     'tempat_lahir',
                    //     'tanggal_lahir',
                    //     'telepon',
                    //     'email',
                    //     'pendidikan_terakhir',
                    // ];

                    $allNotNull = collect($columnsToCheck)->every(function ($column) use ($targetBiodata) {
                        return !is_null($targetBiodata->$column); // Return true only if the column is not null
                    });

                    // If the count of not null columns is greater than 0, we have some values
                    if ($allNotNull) {

                        $targetBiodataOkay = true;
                    }
                }

                if ($targetExtraBiodata && $targetBiodataOkay) {
                    $columnsToCheck = [
                        'status_pernikahan',
                        'provinsi',
                        'kota_kab',
                        'kecamatan',
                        'desa_kel',
                        'rt',
                        'rw',
                        'kode_pos',
                    ];

                    $allNotNull = collect($columnsToCheck)->every(function ($column) use ($targetExtraBiodata) {
                        return !is_null($targetExtraBiodata->$column); // Return true only if the column is not null
                    });

                    // If the count of not null columns is greater than 0, we have some values
                    if ($allNotNull) {

                        $targetExtraBiodataOkay = true;
                    }
                }

                $simpleBiodata[$i]['biodata_okay'] = $targetBiodataOkay && $targetExtraBiodataOkay;
            }

            return response()->json([
                'dataPegawai' => $simpleBiodata,
                // 'prodi' => $getProdi,
                // 'fakultas' => $allFakultas,
                // 'unit' => $allUnit
                // 'refreshToken' => Str::random(5),
                // 'activeBioProps' => $activeBio
            ]);
        }

        // Status
        // 1: Tendik
        // 2: Dosen
        // 3: Dosen + Tendik
        // 4: Dosen + Jabatan Dosen
        if ($status === 1 || $status === 3) {
            // $allUnit = SikUnitKerja::where('active', 1)->get();
            $simpleBiodata = null;

            if ($unit) {
                if ($sortMode === 0) {
                    $simpleBiodata = SikBiodata::select('id', 'master_id', 'nik', 'fullname', 'img_storage', 'unit_id', 'kelamin', 'tanggal_lahir', 'status', 'telepon', 'active', 'fakultas_id', 'unit_id')
                        ->where('status', $status)->where('unit_id', $unit)->get();
                } else {
                    $simpleBiodata = SikBiodata::select('id', 'master_id', 'nik', 'fullname', 'img_storage', 'unit_id', 'kelamin', 'tanggal_lahir', 'status', 'telepon', 'active', 'fakultas_id', 'unit_id')
                        ->where('status', $status)->where('unit_id', $unit)->orderBy('fullname', $sortName)->get();
                }
            } else {
                if ($sortMode === 0) {
                    $simpleBiodata = SikBiodata::select('id', 'master_id', 'nik', 'fullname', 'img_storage', 'unit_id', 'kelamin', 'tanggal_lahir', 'status', 'telepon', 'active', 'fakultas_id', 'unit_id')
                        ->where('status', $status)->get();
                } else {
                    $simpleBiodata = SikBiodata::select('id', 'master_id', 'nik', 'fullname', 'img_storage', 'unit_id', 'kelamin', 'tanggal_lahir', 'status', 'telepon', 'active', 'fakultas_id', 'unit_id')
                        ->where('status', $status)->orderBy('fullname', $sortName)->get();
                }
            }

            for ($i = 0; $i < sizeof($simpleBiodata); $i++) {
                $simpleBiodata[$i]['age'] = calculateAge($simpleBiodata[$i]['tanggal_lahir']);

                // for ($j = 0; $j < sizeof($allUnit); $j++) {
                //     if (intval($allUnit[$j]['id']) === intval($simpleBiodata[$i]['unit_id'])) {
                //         $simpleBiodata[$i]['unit_name'] = $allUnit[$j]['name'];
                //     }
                // }

                if ($simpleBiodata[$i]['status'] === "2" || $simpleBiodata[$i]['status'] === "4" || $simpleBiodata[$i]['status'] === "5") {
                    $fakultasId = intval($simpleBiodata[$i]['fakultas_id']);
                    $targetFakultas = $allFakultas->firstWhere('id', $fakultasId);
                    $simpleBiodata[$i]['unit_name'] = "Fakultas " . $targetFakultas->name;
                } else {
                    $unitId = intval($simpleBiodata[$i]['unit_id']);
                    $targetUnit = $allUnit->firstWhere('id', $unitId);
                    $simpleBiodata[$i]['unit_name'] = $targetUnit->name;
                }
            }
        } else {
            // $allFakultas = SikFakultas::where('active', 1)->get();
            $simpleBiodata = null;

            if ($fakultas) {
                if ($prodi) {
                    if ($sortMode === 0) {
                        $simpleBiodata = SikBiodata::select('id', 'master_id', 'nik', 'fullname', 'img_storage', 'fakultas_id', 'kelamin', 'tanggal_lahir', 'status', 'telepon', 'active', 'fakultas_id', 'unit_id')
                            ->where('status', $status)->where('fakultas_id', $fakultas)->where('prodi_id', $prodi)->get();
                    } else {
                        $simpleBiodata = SikBiodata::select('id', 'master_id', 'nik', 'fullname', 'img_storage', 'fakultas_id', 'kelamin', 'tanggal_lahir', 'status', 'telepon', 'active', 'fakultas_id', 'unit_id')
                            ->where('status', $status)->where('fakultas_id', $fakultas)->where('prodi_id', $prodi)->orderBy('fullname', $sortName)->get();
                    }
                } else {
                    if ($sortMode === 0) {
                        $simpleBiodata = SikBiodata::select('id', 'master_id', 'nik', 'fullname', 'img_storage', 'fakultas_id', 'kelamin', 'tanggal_lahir', 'status', 'telepon', 'active', 'fakultas_id', 'unit_id')
                            ->where('status', $status)->where('fakultas_id', $fakultas)->get();
                    } else {
                        $simpleBiodata = SikBiodata::select('id', 'master_id', 'nik', 'fullname', 'img_storage', 'fakultas_id', 'kelamin', 'tanggal_lahir', 'status', 'telepon', 'active', 'fakultas_id', 'unit_id')
                            ->where('status', $status)->where('fakultas_id', $fakultas)->orderBy('fullname', $sortName)->get();
                    }
                }
            } else {
                if ($sortMode === 0) {
                    $simpleBiodata = SikBiodata::select('id', 'master_id', 'nik', 'fullname', 'img_storage', 'fakultas_id', 'kelamin', 'tanggal_lahir', 'status', 'telepon', 'active', 'fakultas_id', 'unit_id')
                        ->where('status', $status)->get();
                } else {
                    $simpleBiodata = SikBiodata::select('id', 'master_id', 'nik', 'fullname', 'img_storage', 'fakultas_id', 'kelamin', 'tanggal_lahir', 'status', 'telepon', 'active', 'fakultas_id', 'unit_id')
                        ->where('status', $status)->orderBy('fullname', $sortName)->get();
                }
            }

            for ($i = 0; $i < sizeof($simpleBiodata); $i++) {

                $simpleBiodata[$i]['age'] = calculateAge($simpleBiodata[$i]['tanggal_lahir']);

                // for ($j = 0; $j < sizeof($allFakultas); $j++) {
                //     if (intval($allFakultas[$j]['id']) === intval($simpleBiodata[$i]['fakultas_id'])) {
                //         $simpleBiodata[$i]['unit_name'] = $allFakultas[$j]['name'];
                //     }
                // }

                if ($simpleBiodata[$i]['status'] === "2" || $simpleBiodata[$i]['status'] === "4" || $simpleBiodata[$i]['status'] === "5") {
                    $fakultasId = intval($simpleBiodata[$i]['fakultas_id']);
                    $targetFakultas = $allFakultas->firstWhere('id', $fakultasId);
                    $simpleBiodata[$i]['unit_name'] = "Fakultas " . $targetFakultas->name;
                } else {
                    $unitId = intval($simpleBiodata[$i]['unit_id']);
                    $targetUnit = $allUnit->firstWhere('id', $unitId);
                    $simpleBiodata[$i]['unit_name'] = $targetUnit->name;
                }
            }
        }

        if ($status === 0) {
            // $allUnit = SikUnitKerja::where('active', 1)->get();

            if ($sortMode === 0) {
                $simpleBiodata = SikBiodata::select('id', 'master_id', 'nik', 'fullname', 'img_storage', 'unit_id', 'kelamin', 'tanggal_lahir', 'status', 'telepon', 'active', 'fakultas_id', 'unit_id')
                    ->get();
            } else {
                $simpleBiodata = SikBiodata::select('id', 'master_id', 'nik', 'fullname', 'img_storage', 'unit_id', 'kelamin', 'tanggal_lahir', 'status', 'telepon', 'active', 'fakultas_id', 'unit_id')
                    ->orderBy('fullname', $sortName)->get();
            }

            for ($i = 0; $i < sizeof($simpleBiodata); $i++) {

                $simpleBiodata[$i]['age'] = calculateAge($simpleBiodata[$i]['tanggal_lahir']);

                // for ($j = 0; $j < sizeof($allUnit); $j++) {
                //     if (intval($allUnit[$j]['id']) === intval($simpleBiodata[$i]['unit_id'])) {
                //         $simpleBiodata[$i]['unit_name'] = $allUnit[$j]['name'];
                //     }
                // }

                if ($simpleBiodata[$i]['status'] === "2" || $simpleBiodata[$i]['status'] === "4" || $simpleBiodata[$i]['status'] === "5") {
                    $fakultasId = intval($simpleBiodata[$i]['fakultas_id']);
                    $targetFakultas = $allFakultas->firstWhere('id', $fakultasId);
                    $simpleBiodata[$i]['unit_name'] = "Fakultas " . $targetFakultas->name;
                } else {
                    $unitId = intval($simpleBiodata[$i]['unit_id']);
                    $targetUnit = $allUnit->firstWhere('id', $unitId);
                    $simpleBiodata[$i]['unit_name'] = $targetUnit->name;
                }
            }
        }

        for ($i = 0; $i < sizeof($simpleBiodata); $i++) {
            $targetBiodata = SikBiodata::where('master_id', intval($simpleBiodata[$i]['master_id']))
                ->first();
            $targetBiodataOkay = false;

            $targetExtraBiodata = SikExtraBiodata::where('biodata_id', intval($targetBiodata->id))
                ->first();
            $targetExtraBiodataOkay = false;

            // Check if the user exists and if the specific columns are not null
            $biodataStatus = intval($targetBiodata->status);

            // Staff Fakultas [5]:
            $columnsToCheck = [
                'nik',
                'nik_ktp',
                'fullname',
                'kelamin',
                'tempat_lahir',
                'tanggal_lahir',
                'telepon',
                'email',
                'pendidikan_terakhir',
                // Special
                'fakultas_id',
                'staff_fakultas_id',
                'status_kerja'
            ];

            // Tendik [1]:
            if ($biodataStatus === 1) {
                $columnsToCheck = [
                    'nik',
                    'nik_ktp',
                    'fullname',
                    'kelamin',
                    'tempat_lahir',
                    'tanggal_lahir',
                    'telepon',
                    'email',
                    'pendidikan_terakhir',
                    // Special
                    'unit_id',
                    'jabatan_struktural_id',
                    'status_kerja'
                ];
            }
            // Dosen [2]:
            else if ($biodataStatus === 2) {
                $columnsToCheck = [
                    'nik',
                    'nik_ktp',
                    'fullname',
                    'kelamin',
                    'tempat_lahir',
                    'tanggal_lahir',
                    'telepon',
                    'email',
                    'pendidikan_terakhir',
                    // Special
                    'fakultas_id',
                    'prodi_id',
                ];
            }
            // Dosen + Tendik [3]:
            else if ($biodataStatus === 3) {
                $columnsToCheck = [
                    'nik',
                    'nik_ktp',
                    'fullname',
                    'kelamin',
                    'tempat_lahir',
                    'tanggal_lahir',
                    'telepon',
                    'email',
                    'pendidikan_terakhir',
                    // Special
                    'unit_id',
                    'jabatan_struktural_id',
                    'fakultas_id',
                    'prodi_id',
                    'status_kerja'
                ];
            }
            // Dosen + Jabatan Dosen [4]:
            else if ($biodataStatus === 4) {
                $columnsToCheck = [
                    'nik',
                    'nik_ktp',
                    'fullname',
                    'kelamin',
                    'tempat_lahir',
                    'tanggal_lahir',
                    'telepon',
                    'email',
                    'pendidikan_terakhir',
                    // Special
                    'fakultas_id',
                    'jabatan_strukdos_id',
                    'prodi_id'
                ];
            }

            if ($targetBiodata) {
                // $columnsToCheck = [
                //     'nik',
                //     'nik_ktp',
                //     'fullname',
                //     'kelamin',
                //     'tempat_lahir',
                //     'tanggal_lahir',
                //     'telepon',
                //     'email',
                //     'pendidikan_terakhir',
                // ];

                $allNotNull = collect($columnsToCheck)->every(function ($column) use ($targetBiodata) {
                    return !is_null($targetBiodata->$column); // Return true only if the column is not null
                });

                // If the count of not null columns is greater than 0, we have some values
                if ($allNotNull) {

                    $targetBiodataOkay = true;
                }
            }

            if ($targetExtraBiodata && $targetBiodataOkay) {
                $columnsToCheck = [
                    'status_pernikahan',
                    'provinsi',
                    'kota_kab',
                    'kecamatan',
                    'desa_kel',
                    'rt',
                    'rw',
                    'kode_pos',
                ];

                $allNotNull = collect($columnsToCheck)->every(function ($column) use ($targetExtraBiodata) {
                    return !is_null($targetExtraBiodata->$column); // Return true only if the column is not null
                });

                // If the count of not null columns is greater than 0, we have some values
                if ($allNotNull) {

                    $targetExtraBiodataOkay = true;
                }
            }

            $simpleBiodata[$i]['biodata_okay'] = $targetBiodataOkay && $targetExtraBiodataOkay;
        }

        return response()->json([
            'dataPegawai' => $simpleBiodata,
            // 'debug' => $sortMode
            // 'refreshToken' => Str::random(5),
            // 'activeBioProps' => $activeBio
        ]);
    }

    public function dataPegawai()
    {
        $simpleBiodata = SikBiodata::select('id', 'master_id', 'nik', 'fullname', 'img_storage', 'unit_id', 'kelamin', 'tanggal_lahir', 'status', 'telepon', 'active')->get();
        $allUnit = SikUnitKerja::all();

        for ($i = 0; $i < sizeof($simpleBiodata); $i++) {

            $simpleBiodata[$i]['age'] = calculateAge($simpleBiodata[$i]['tanggal_lahir']);

            for ($j = 0; $j < sizeof($allUnit); $j++) {
                if (intval($allUnit[$j]['id']) === intval($simpleBiodata[$i]['unit_id'])) {
                    $simpleBiodata[$i]['unit_name'] = $allUnit[$j]['name'];
                }
            }
        }

        $getProdi = SikProdi::orderBy('name')->get();

        $activeBio = SikBiodata::where('master_id', Auth::id())->first();

        if ($activeBio === null) {
            global $rawActiveBio;

            $activeBio = json_decode($rawActiveBio);

            return Inertia::render('SimpegUnusida/SIKMain', [
                'dataPegawai' => $simpleBiodata,
                'prodi' => $getProdi,
                'refreshToken' => Str::random(5),
                'activeBioProps' => $activeBio,
            ]);
        }

        $getTargetStruktural = SikJabatanStruktural::find(intval($activeBio->jabatan_struktural_id));

        $activeBio->nama_jabatan = $getTargetStruktural->name;


        return Inertia::render('SimpegUnusida/SIKMain', [
            // 'dataPegawai' => $simpleBiodata,
            // 'prodi' => $getProdi,
            'refreshToken' => Str::random(5),
            'activeBioProps' => $activeBio
        ]);
    }

    public function dataPegawaiTerpilih(Request $id)
    {
        $data = SikBiodata::find(intval($id->id));
        $dataExtra = SikExtraBiodata::where('biodata_id', intval($id->id))->first();
        $getUnit = SikUnitKerja::orderBy('name')->get();
        $getJabFun = SikJabatanFungsional::orderBy('name')->get();
        $getJastur = SikJabatanStruktural::orderBy('name')->get();
        $getFakultas = SikFakultas::orderBy('name')->get();
        $getProdi = SikProdi::orderBy('name')->get();
        $getProvinsi = ProvinsiIndonesia::orderBy('name')->get();

        $url = "";

        if ($data['img_storage'] !== null) {

            $url = Storage::url('public/profile/' . $data->nik . '_' . $data->master_id . '/' . $data->img_storage);
            $data['profile_img_path'] =  $url;
        } else {
            $data['profile_img_path'] = null;
        }

        if ($dataExtra->provinsi !== null) {
            $provinsi = $dataExtra->provinsi;
            $provinsi_2 = $dataExtra->provinsi_2;
            $kota_kab = $dataExtra->kota_kab;
            $kota_kab_2 = $dataExtra->kota_kab_2;
            $kecamatan = $dataExtra->kecamatan;
            $kecamatan_2 = $dataExtra->kecamatan_2;
        }

        $getKabupaten = [];
        $getKabupaten2 = [];
        $getKecamatan = [];
        $getKecamatan2 = [];
        $getDesa = [];
        $getDesa2 = [];


        if ($dataExtra->provinsi !== null) {
            $getKabupaten = KabupatenIndonesia::where('provinsi_id', $provinsi)->get();
            $getKabupaten2 = KabupatenIndonesia::where('provinsi_id', $provinsi_2)->get();
            $getKecamatan = KecamatanIndonesia::where('kabupaten_id', $kota_kab)->get();
            $getKecamatan2 = KecamatanIndonesia::where('kabupaten_id', $kota_kab_2)->get();
            $getDesa = DesaIndonesia::where('kecamatan_id', $kecamatan)->get();
            $getDesa2 = DesaIndonesia::where('kecamatan_id', $kecamatan_2)->get();
        }

        // $allUnit = SikUnitKerja::all();

        // for ($i = 0; $i < sizeof($simpleBiodata); $i++) {

        //     for ($j = 0; $j < sizeof($allUnit); $j++) {
        //         if (intval($allUnit[$j]['id']) === intval($simpleBiodata[$i]['unit_id'])) {
        //             $simpleBiodata[$i]['unit_name'] = $allUnit[$j]['unit_name'];
        //         }
        //     }
        // }

        // return $provinsi;

        $activeBio = SikBiodata::where('master_id', Auth::id())->first();

        if ($activeBio === null) {
            global $rawActiveBio;

            $activeBio = json_decode($rawActiveBio);
        } else {
            $getTargetStruktural = SikJabatanStruktural::find(intval($activeBio->jabatan_struktural_id));

            $activeBio->nama_jabatan = $getTargetStruktural->name;
        }

        if (Auth::user()->username === "adminsdmunusida") {
            $activeBio->data_filled = 1;
        }

        return Inertia::render('SimpegUnusida/SIKMain', [
            'dataPegawai' => false,
            'selectedDataPegawai' => [$data, $dataExtra],
            'unitKerja' => $getUnit,
            'jabatanFun' => $getJabFun,
            'jabatanStr' => $getJastur,
            'fakultas' => $getFakultas,
            'prodi' => $getProdi,
            'listProvinsi' => $getProvinsi,
            'listKabupaten' => $getKabupaten,
            'listKabupaten2' => $getKabupaten2,
            'listKecamatan' => $getKecamatan,
            'listDesa' => $getDesa,
            'listKecamatan2' => $getKecamatan2,
            'listDesa2' => $getDesa2,
            'activeBioProps' => $activeBio
        ]);
    }

    public function tambahPegawai()
    {
        // $data = SikBiodata::find(intval($id->id));
        $getUnit = SikUnitKerja::orderBy('name')->get();
        $getJabFun = SikJabatanFungsional::orderBy('name')->get();
        $getJastur = SikJabatanStruktural::orderBy('name')->get();
        $getFakultas = SikFakultas::orderBy('name')->get();
        $getProdi = SikProdi::orderBy('name')->get();
        $getProvinsi = ProvinsiIndonesia::orderBy('name')->get();

        // $allUnit = SikUnitKerja::all();

        // for ($i = 0; $i < sizeof($simpleBiodata); $i++) {

        //     for ($j = 0; $j < sizeof($allUnit); $j++) {
        //         if (intval($allUnit[$j]['id']) === intval($simpleBiodata[$i]['unit_id'])) {
        //             $simpleBiodata[$i]['unit_name'] = $allUnit[$j]['unit_name'];
        //         }
        //     }
        // }

        $activeBio = SikBiodata::where('master_id', Auth::id())->first();

        if ($activeBio === null) {
            global $rawActiveBio;

            $activeBio = json_decode($rawActiveBio);
        } else {
            $getTargetStruktural = SikJabatanStruktural::find(intval($activeBio->jabatan_struktural_id));

            $activeBio->nama_jabatan = $getTargetStruktural->name;
        }


        return Inertia::render('SimpegUnusida/SIKMain', [
            // 'dataPegawai' => false,
            // 'selectedDataPegawai' => $data,
            'unitKerja' => $getUnit,
            'jabatanFun' => $getJabFun,
            'jabatanStr' => $getJastur,
            'fakultas' => $getFakultas,
            'prodi' => $getProdi,
            'listProvinsi' => $getProvinsi,
            'activeBioProps' => $activeBio
        ]);
    }

    public function simpanTambahPegawai(Request $data)
    {
        // return response()->json(["gambar" => $data->imgTemp]);

        $profileImageName = $data->imgTemp;
        $newPegawai = new SikBiodata;
        $newPegawaiExtra = new SikExtraBiodata;

        $addUser = new User;

        // Hehehe

        foreach ($data as $key => $value) {
            if ($value === 'null') {
                $data[$key] = null;
            }
        }
        // return response()->json(["debug" => $data->jabatan_strukdos_id, "debug2" => null]);

        $addUser->nik = $data->nik;
        $addUser->username = $data->nik;
        $addUser->name = $data->fullname;
        $addUser->email = $data->email;
        $addUser->phone = $data->telepon;
        $addUser->password = Hash::make($data->nik);
        $addUser->remember_token = Str::random(10);
        $addUser->level = 5;
        $addUser->active = 1;
        $addUser->allowed_app_arr = serialize(['usl']);
        $addUser->save();

        $users = User::where('nik', $data->nik)->first();
        $nextId = $users->id;

        $newPegawai->nik = $data->nik;
        $newPegawai->master_id = $nextId;
        $newPegawai->fullname = $data->fullname;
        $newPegawai->kelamin = $data->kelamin;
        $newPegawai->tinggi_badan = $data->tinggi_badan;
        $newPegawai->berat_badan = $data->berat_badan;
        $newPegawai->tempat_lahir = $data->tempat_lahir;
        $newPegawai->tanggal_lahir = Carbon::createFromFormat('Y-m-d', $data->tanggal_lahir)->format('Y-m-d');
        $newPegawai->telepon = $data->telepon;
        $newPegawai->fakultas_id = $data->fakultas === 'null' ? null : $data->fakultas;
        $newPegawai->prodi_id = $data->prodi === 'null' ? null : $data->prodi;
        $newPegawai->base_nominal_kpi = 0;

        if (intval($data->status) !== 2 && intval($data->status) !== 4) {
            $newPegawai->status_kerja = $data->status_kerja;
        } else {
            $newPegawai->status_kerja = null;
        }

        $newPegawai->status = $data->status;

        if (intval($data->status) === 5) {
            $newPegawai->jabatan_strukdos_id = 6;

            if (!$data->staff_fakultas_id) {
                $newPegawai->staff_fakultas_id = 1;
            } else {
                $newPegawai->staff_fakultas_id = $data->staff_fakultas_id;
            }

            $newPegawai->unit_id = null;
            $newPegawai->jabatan_struktural_id = null;
        } else {
            $newPegawai->jabatan_strukdos_id = $data->jabatan_strukdos_id === 'null' ? null : $data->jabatan_strukdos_id;

            $newPegawai->unit_id = $data->unit_id;
            $newPegawai->jabatan_struktural_id = $data->jabatan_struktural_id === 'null' ? null : $data->jabatan_struktural_id;
            $newPegawai->staff_fakultas_id = null;
        }

        $newPegawai->email = $data->email;
        $newPegawai->pendidikan_terakhir = $data->pendidikan_terakhir;
        $newPegawai->no_bpjs_kes = $data->no_bpjs_kes;
        $newPegawai->no_bpjs_kerja = $data->no_bpjs_kerja;
        $newPegawai->kerabat_nama = $data->kerabat_nama;
        $newPegawai->kerabat_hubungan = $data->kerabat_hubungan;
        $newPegawai->kerabat_telepon = $data->kerabat_telepon;

        $newPegawai->nidn = $data->nidn;

        $newPegawai->nik_ktp = $data->nik_ktp;
        $newPegawai->awal_kerja = $data->awal_kerja;

        $newPegawai->jabatan_fungsional_id = $data->jabatan_fungsional_id === 'null' ? null : $data->jabatan_fungsional_id;

        $newPegawai->status_serdos = $data->status_serdos === 'null' ? null : $data->status_serdos;
        $newPegawai->active = 1;
        $newPegawai->save();

        $buff = SikBiodata::where('nik', $data->nik)->first();

        $newPegawaiExtra->biodata_id = $buff->id;

        $newPegawaiExtra->url_ijazah = $data->url_ijazah;
        $newPegawaiExtra->provinsi = $data->provinsi;
        $newPegawaiExtra->kota_kab = $data->kota_kab;
        $newPegawaiExtra->kecamatan = $data->kecamatan;
        $newPegawaiExtra->desa_kel = $data->desa_kel;
        $newPegawaiExtra->rt = $data->rt;
        $newPegawaiExtra->rw = $data->rw;
        $newPegawaiExtra->kode_pos = $data->kodepos;

        $newPegawaiExtra->minat = $data->minat;
        $newPegawaiExtra->bakat = $data->bakat;
        $newPegawaiExtra->kompetensi = $data->kompetensi;
        $newPegawaiExtra->status_pernikahan = $data->status_pernikahan;

        $newPegawaiExtra->kepangkatan = $data->kepangkatan === 'null' ? null : $data->kepangkatan;
        $newPegawaiExtra->studi_lanjut = $data->studi_lanjut === 'null' ? null : $data->studi_lanjut;

        $newPegawaiExtra->provinsi_2 = $data->provinsi_2;
        $newPegawaiExtra->kota_kab_2 = $data->kota_kab_2;
        $newPegawaiExtra->kecamatan_2 = $data->kecamatan_2;
        $newPegawaiExtra->desa_kel_2 = $data->desa_kel_2;
        $newPegawaiExtra->rt_2 = $data->rt_2;
        $newPegawaiExtra->rw_2 = $data->rw_2;
        $newPegawaiExtra->kode_pos_2 = $data->kodepos_2;

        $newPegawaiExtra->save();

        $bioImg = SikBiodata::where('nik', $data->nik)->first();


        $doMove = null;

        // Change to 'public' if necessary
        if (Storage::disk('public')->exists('temp/' . $data->imgTemp)) {
            $imageName = $bioImg->nik . '/' . strval($data->imgTemp);
            // $folder = 'profile/' . $bioImg->nik . '_' . strval($bioImg->master_id);
            $folder = 'profile/' . $bioImg->nik;

            // FIXED
            // $sourcePath = 'temp/' . $data->imgTemp;

            $sourcePath = 'temp/' . $data->imgTemp;
            $destinationPath = 'profile/' . preg_replace('/\s+/', '', $imageName);

            $doMove = Storage::disk('public')->move($sourcePath, $destinationPath);

            if ($doMove) {
                // Set permissions for the new folder
                $permissions = 'public'; // 'public' or 'private'
                Storage::disk('public')->setVisibility($folder, $permissions);

                $bioImg->img_storage = $profileImageName;
                $bioImg->save();
            }
        }

        // if ($data->hasFile('imgFile') && $data->file('imgFile')->isValid()) {
        //     $image = $data->file('imgFile');
        //     // $imageName = time() . '.' . $image->getClientOriginalExtension();
        //     $imageName = $bioImg->nik . '_' . $bioImg->master_id . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();

        //     $status = Storage::disk('public')->putFileAs('profile', $image, $imageName);

        //     if ($status) {
        //         $bioImg->img_storage = $imageName;
        //         $bioImg->save();
        //     }
        // }

        // Render BAck data pegawai page

        $simpleBiodata = SikBiodata::select('id', 'nik', 'fullname', 'unit_id', 'kelamin', 'status', 'telepon')->get();
        $allUnit = SikUnitKerja::all();

        for ($i = 0; $i < sizeof($simpleBiodata); $i++) {

            for ($j = 0; $j < sizeof($allUnit); $j++) {
                if (intval($allUnit[$j]['id']) === intval($simpleBiodata[$i]['unit_id'])) {
                    $simpleBiodata[$i]['unit_name'] = $allUnit[$j]['name'];
                }
            }
        }

        if ($doMove) {
            return response()->json(["good" => true]);
        }
        return response()->json(["good" => false]);

        // Debug
        return Inertia::render('SimpegUnusida/SIKMain', [
            'dataPegawai' => $simpleBiodata,
            // 'prodi' => $getProdi,
            // 'viewId' => 1,
            // 'refreshToken' => Str::random(5),
            // 'unitKerja' => $getUnit,
            // 'jabatanFun' => $getJabFun,
            // 'jabatanStr' => $getJastur,
            // 'fakultas' => $getFakultas,
            // 'prodi' => $getProdi,
            // 'listProvinsi' => $getProvinsi
        ]);

        // return to_route('sik.data-pegawai');


        // Must FIX Later
        // $valid = $data->validate([
        //     'nik' => 'required',
        //     'fullname' => 'required|string',
        //     'tempat_lahir' => 'required|string',
        //     'tinggi_badan' => 'required',
        //     'berat_badan' => 'required',
        //     'telepon' => 'required',
        //     'email' => 'required|email',
        //     'pendidikan_terakhir' => 'required',
        //     'url_ijazah' => 'required|active_url',
        //     'no_bpjs_kes' => 'required',
        //     'no_bpjs_kerja' => 'required',
        //     'status' => 'required', // 1:dosen, 2: serdos
        //     'unit_id' => 'required',
        //     'jabatan_struktural_id' => 'required',
        //     'jabatan_fungsional_id' => 'required',
        //     'status_serdos' => 'required',
        //     'fakultas' => 'required',
        //     'prodi' => 'required',

        //     // Kerabat
        //     'kerabat_nama' => 'required|string',
        //     'kerabat_hubungan' => 'required|string',
        //     'kerabat_telepon' => 'required',

        //     // Alamat
        //     'provinsi' => 'required',
        //     'kota_kab' => 'required',
        //     'kecamatan' => 'required',
        //     'desa_kel' => 'required',
        //     'rt' => 'required',
        //     'rw' => 'required',
        //     'kodepos' => 'required',
        //     // Alamat 2
        //     'provinsi_2' => 'required',
        //     'kota_kab_2' => 'required',
        //     'kecamatan_2' => 'required',
        //     'desa_kel_2' => 'required',
        //     'rt_2' => 'required',
        //     'rw_2' => 'required',
        //     'kodepos_2' => 'required',
        // ]);
    }

    public function simpanUbahPegawaiTest(Request $data)
    {
        return response()->json($data);
    }

    public function simpanUbahPegawai(Request $data)
    {
        $pegawaiId = $data->mainId;

        $newPegawai = SikBiodata::where("id", intval($pegawaiId))->first();
        $newPegawaiExtra = SikExtraBiodata::where('biodata_id', $pegawaiId)->first();

        $addUser = User::find($newPegawai->master_id);

        $addUser->nik = $data->nik;
        $addUser->username = $data->nik;
        $addUser->name = $data->fullname;
        $addUser->email = $data->email;
        $addUser->phone = $data->telepon;
        // $addUser->password = Hash::make($data->nik);
        // $addUser->remember_token = Str::random(10);
        // $addUser->level = 5;
        // $addUser->active = 1;
        // $addUser->allowed_app_arr = serialize(['usl']);
        $addUser->save();

        // $users = User::where('nik', $data->nik)->first();
        // $nextId = $users->id;

        $newPegawai->nik = $data->nik;
        // $newPegawai->master_id = $nextId;
        $newPegawai->fullname = $data->fullname;
        $newPegawai->kelamin = $data->kelamin;
        $newPegawai->tinggi_badan = $data->tinggi_badan;
        $newPegawai->berat_badan = $data->berat_badan;
        $newPegawai->tempat_lahir = $data->tempat_lahir;
        $newPegawai->tanggal_lahir = Carbon::createFromFormat('Y-m-d', $data->tanggal_lahir)->format('Y-m-d');
        $newPegawai->telepon = $data->telepon;
        $newPegawai->fakultas_id = $data->fakultas === 'null' ? null : $data->fakultas;
        $newPegawai->prodi_id = $data->prodi === 'null' ? null : $data->prodi;
        $newPegawai->nidn = $data->nidn;

        $newPegawai->email = $data->email;
        $newPegawai->pendidikan_terakhir = $data->pendidikan_terakhir;
        $newPegawai->no_bpjs_kes = $data->no_bpjs_kes;
        $newPegawai->no_bpjs_kerja = $data->no_bpjs_kerja;
        $newPegawai->kerabat_nama = $data->kerabat_nama;
        $newPegawai->kerabat_hubungan = $data->kerabat_hubungan;
        $newPegawai->kerabat_telepon = $data->kerabat_telepon;

        $newPegawai->nidn = $data->nidn;

        $newPegawai->status = $data->status;

        if (intval($data->status) !== 2 && intval($data->status) !== 4) {
            $newPegawai->status_kerja = $data->status_kerja;
        } else {
            $newPegawai->status_kerja = null;
        }

        if (intval($data->status) === 5) {
            $newPegawai->jabatan_strukdos_id = 6;

            if (!$newPegawai->staff_fakultas_id || !$data->staff_fakultas_id) {
                $newPegawai->staff_fakultas_id = 1;
            } else {
                $newPegawai->staff_fakultas_id = $data->staff_fakultas_id;
            }

            $newPegawai->unit_id = null;
            $newPegawai->jabatan_struktural_id = null;
        } else {
            $newPegawai->jabatan_strukdos_id = $data->jabatan_strukdos_id === 'null' ? null : $data->jabatan_strukdos_id;

            $newPegawai->unit_id = $data->unit_id;
            $newPegawai->jabatan_struktural_id = $data->jabatan_struktural_id === 'null' ? null : $data->jabatan_struktural_id;
            $newPegawai->staff_fakultas_id = null;
        }

        $newPegawai->nik_ktp = $data->nik_ktp;
        $newPegawai->awal_kerja = $data->awal_kerja;

        $newPegawai->jabatan_fungsional_id = $data->jabatan_fungsional_id === 'null' ? null : $data->jabatan_fungsional_id;

        $newPegawai->status_serdos = $data->status_serdos === 'null' ? null : $data->status_serdos;
        $newPegawai->active = 1;

        // Update data_filled
        $newPegawai->data_filled = true;

        $newPegawai->save();

        // $buff = SikBiodata::where('nik', $data->nik)->first();

        // $newPegawaiExtra->biodata_id = $buff->id;

        $newPegawaiExtra->url_ijazah = $data->url_ijazah;
        $newPegawaiExtra->provinsi = $data->provinsi;
        $newPegawaiExtra->kota_kab = $data->kota_kab;
        $newPegawaiExtra->kecamatan = $data->kecamatan;

        $newPegawaiExtra->desa_kel = $data->desa_kel;
        $newPegawaiExtra->rt = $data->rt;
        $newPegawaiExtra->rw = $data->rw;
        $newPegawaiExtra->kode_pos = $data->kodepos;

        $newPegawaiExtra->minat = $data->minat;
        $newPegawaiExtra->bakat = $data->bakat;
        $newPegawaiExtra->kompetensi = $data->kompetensi;
        $newPegawaiExtra->status_pernikahan = $data->status_pernikahan;

        $newPegawaiExtra->kepangkatan = $data->kepangkatan === 'null' ? null : $data->kepangkatan;
        $newPegawaiExtra->studi_lanjut = $data->studi_lanjut === 'null' ? null : $data->studi_lanjut;

        // $newPegawaiExtra->kepangkatan = $data->kepangkatan;
        // $newPegawaiExtra->studi_lanjut = $data->studi_lanjut;

        $newPegawaiExtra->provinsi_2 = $data->provinsi_2;
        $newPegawaiExtra->kota_kab_2 = $data->kota_kab_2;
        $newPegawaiExtra->kecamatan_2 = $data->kecamatan_2;
        $newPegawaiExtra->desa_kel_2 = $data->desa_kel_2;
        $newPegawaiExtra->rt_2 = $data->rt_2;
        $newPegawaiExtra->rw_2 = $data->rw_2;
        $newPegawaiExtra->kode_pos_2 = $data->kodepos_2;

        if ($newPegawaiExtra->save()) {
            return response()->json(["good" => true]);
        }
        return response()->json(["good" => false]);

        // $bioImg = SikBiodata::find($pegawaiId);

        // if (Storage::disk('public')->exists('profile/' . $bioImg->img_storage)) {
        //     Storage::disk('public')->delete('profile/' . $bioImg->img_storage);
        // }

        // if ($data->hasFile('imgFile') && $data->file('imgFile')->isValid()) {
        //     $image = $data->file('imgFile');
        //     // $imageName = time() . '.' . $image->getClientOriginalExtension();
        //     $imageName = $bioImg->nik . '_' . $bioImg->master_id . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();

        //     $status = Storage::disk('public')->putFileAs('profile', $image, $imageName);

        //     if ($status) {
        //         $bioImg->img_storage = $imageName;
        //         $bioImg->save();
        //     }
        // }

        // $simpleBiodata = SikBiodata::select('id', 'nik', 'fullname', 'unit_id', 'kelamin', 'status', 'telepon')->get();
        // $allUnit = SikUnitKerja::all();

        // for ($i = 0; $i < sizeof($simpleBiodata); $i++) {

        //     for ($j = 0; $j < sizeof($allUnit); $j++) {
        //         if (intval($allUnit[$j]['id']) === intval($simpleBiodata[$i]['unit_id'])) {
        //             $simpleBiodata[$i]['unit_name'] = $allUnit[$j]['name'];
        //         }
        //     }
        // }



        // return to_route('sik.dashboard');

        // STay on the page
        $data = SikBiodata::find(intval($pegawaiId));
        $dataExtra = SikExtraBiodata::where('biodata_id', intval($pegawaiId))->first();
        $getUnit = SikUnitKerja::orderBy('name')->get();
        $getJabFun = SikJabatanFungsional::orderBy('name')->get();
        $getJastur = SikJabatanStruktural::orderBy('name')->get();
        $getFakultas = SikFakultas::orderBy('name')->get();
        $getProdi = SikProdi::orderBy('name')->get();
        $getProvinsi = ProvinsiIndonesia::orderBy('name')->get();

        // $url = "";

        // if ($data['img_storage'] !== null) {

        //     $url = Storage::url('public/profile/' . $data->img_storage);
        //     $data['profile_img_path'] =  $url;
        // } else {
        //     $data['profile_img_path'] = null;
        // }

        if ($dataExtra->provinsi !== null) {
            $provinsi = $dataExtra->provinsi;
            $provinsi_2 = $dataExtra->provinsi_2;
            $kota_kab = $dataExtra->kota_kab;
            $kota_kab_2 = $dataExtra->kota_kab_2;
            $kecamatan = $dataExtra->kecamatan;
            $kecamatan_2 = $dataExtra->kecamatan_2;
        }

        $getKabupaten = [];
        $getKabupaten2 = [];
        $getKecamatan = [];
        $getKecamatan2 = [];
        $getDesa = [];
        $getDesa2 = [];


        if ($dataExtra->provinsi !== null) {
            $getKabupaten = KabupatenIndonesia::where('provinsi_id', $provinsi)->get();
            $getKabupaten2 = KabupatenIndonesia::where('provinsi_id', $provinsi_2)->get();
            $getKecamatan = KecamatanIndonesia::where('kabupaten_id', $kota_kab)->get();
            $getKecamatan2 = KecamatanIndonesia::where('kabupaten_id', $kota_kab_2)->get();
            $getDesa = DesaIndonesia::where('kecamatan_id', $kecamatan)->get();
            $getDesa2 = DesaIndonesia::where('kecamatan_id', $kecamatan_2)->get();
        }

        // if ($data->initial === "sso") {
        //     $activeId = Auth::id();

        //     $userActive = User::where('id', intval($activeId))->get();

        //     return Inertia::render('Sso/NewSsoDashboard', [
        //         "userAccount" => $userActive,
        //         'dataPegawai' => false,
        //         'selectedDataPegawai' => [$data, $dataExtra],
        //         'unitKerja' => $getUnit,
        //         'jabatanFun' => $getJabFun,
        //         'jabatanStr' => $getJastur,
        //         'fakultas' => $getFakultas,
        //         'prodi' => $getProdi,
        //         'listProvinsi' => $getProvinsi,
        //         'listKabupaten' => $getKabupaten,
        //         'listKabupaten2' => $getKabupaten2,
        //         'listKecamatan' => $getKecamatan,
        //         'listDesa' => $getDesa,
        //         'listKecamatan2' => $getKecamatan2,
        //         'listDesa2' => $getDesa2,
        //     ]);
        // }

        // return Inertia::render('SimpegUnusida/SIKMain', [
        //     'dataPegawai' => false,
        //     'selectedDataPegawai' => [$data, $dataExtra],
        //     'unitKerja' => $getUnit,
        //     'jabatanFun' => $getJabFun,
        //     'jabatanStr' => $getJastur,
        //     'fakultas' => $getFakultas,
        //     'prodi' => $getProdi,
        //     'listProvinsi' => $getProvinsi,
        //     'listKabupaten' => $getKabupaten,
        //     'listKabupaten2' => $getKabupaten2,
        //     'listKecamatan' => $getKecamatan,
        //     'listDesa' => $getDesa,
        //     'listKecamatan2' => $getKecamatan2,
        //     'listDesa2' => $getDesa2,
        // ]);


        // Must FIX Later
        // $valid = $data->validate([
        //     'nik' => 'required',
        //     'fullname' => 'required|string',
        //     'tempat_lahir' => 'required|string',
        //     'tinggi_badan' => 'required',
        //     'berat_badan' => 'required',
        //     'telepon' => 'required',
        //     'email' => 'required|email',
        //     'pendidikan_terakhir' => 'required',
        //     'url_ijazah' => 'required|active_url',
        //     'no_bpjs_kes' => 'required',
        //     'no_bpjs_kerja' => 'required',
        //     'status' => 'required', // 1:dosen, 2: serdos
        //     'unit_id' => 'required',
        //     'jabatan_struktural_id' => 'required',
        //     'jabatan_fungsional_id' => 'required',
        //     'status_serdos' => 'required',
        //     'fakultas' => 'required',
        //     'prodi' => 'required',

        //     // Kerabat
        //     'kerabat_nama' => 'required|string',
        //     'kerabat_hubungan' => 'required|string',
        //     'kerabat_telepon' => 'required',

        //     // Alamat
        //     'provinsi' => 'required',
        //     'kota_kab' => 'required',
        //     'kecamatan' => 'required',
        //     'desa_kel' => 'required',
        //     'rt' => 'required',
        //     'rw' => 'required',
        //     'kodepos' => 'required',
        //     // Alamat 2
        //     'provinsi_2' => 'required',
        //     'kota_kab_2' => 'required',
        //     'kecamatan_2' => 'required',
        //     'desa_kel_2' => 'required',
        //     'rt_2' => 'required',
        //     'rw_2' => 'required',
        //     'kodepos_2' => 'required',
        // ]);
    }

    public function simpanUbahPegawai2(Request $data)
    {
        $pegawaiId = $data->mainId;

        $newPegawai = SikBiodata::find($pegawaiId);
        $newPegawaiExtra = SikExtraBiodata::where('biodata_id', $pegawaiId)->first();

        $addUser = User::find($newPegawai->master_id);

        $addUser->nik = $data->nik;
        $addUser->username = $data->nik;
        $addUser->name = $data->fullname;
        $addUser->email = $data->email;
        $addUser->phone = $data->telepon;
        // $addUser->password = Hash::make($data->nik);
        // $addUser->remember_token = Str::random(10);
        // $addUser->level = 5;
        // $addUser->active = 1;
        // $addUser->allowed_app_arr = serialize(['usl']);
        $addUser->save();

        // $users = User::where('nik', $data->nik)->first();
        // $nextId = $users->id;

        $newPegawai->nik = $data->nik;
        // $newPegawai->master_id = $nextId;
        $newPegawai->fullname = $data->fullname;
        $newPegawai->kelamin = $data->kelamin;
        $newPegawai->tinggi_badan = $data->tinggi_badan;
        $newPegawai->berat_badan = $data->berat_badan;
        $newPegawai->tempat_lahir = $data->tempat_lahir;
        $newPegawai->tanggal_lahir = Carbon::createFromFormat('Y-m-d', $data->tanggal_lahir)->format('Y-m-d');
        $newPegawai->telepon = $data->telepon;
        $newPegawai->fakultas_id = $data->fakultas;
        $newPegawai->prodi_id = $data->prodi;

        $newPegawai->email = $data->email;
        $newPegawai->pendidikan_terakhir = $data->pendidikan_terakhir;
        $newPegawai->no_bpjs_kes = $data->no_bpjs_kes;
        $newPegawai->no_bpjs_kerja = $data->no_bpjs_kerja;
        $newPegawai->kerabat_nama = $data->kerabat_nama;
        $newPegawai->kerabat_hubungan = $data->kerabat_hubungan;
        $newPegawai->kerabat_telepon = $data->kerabat_telepon;
        $newPegawai->status = $data->status;

        $newPegawai->nik_ktp = $data->nik_ktp;
        $newPegawai->awal_kerja = $data->awal_kerja;

        $newPegawai->unit_id = $data->unit_id;
        $newPegawai->jabatan_struktural_id = $data->jabatan_struktural_id;
        $newPegawai->jabatan_fungsional_id = $data->jabatan_fungsional_id;
        $newPegawai->status_serdos = $data->status_serdos;
        $newPegawai->active = 1;
        $newPegawai->save();

        // $buff = SikBiodata::where('nik', $data->nik)->first();

        // $newPegawaiExtra->biodata_id = $buff->id;

        $newPegawaiExtra->url_ijazah = $data->url_ijazah;
        $newPegawaiExtra->provinsi = $data->provinsi;
        $newPegawaiExtra->kota_kab = $data->kota_kab;
        $newPegawaiExtra->kecamatan = $data->kecamatan;
        $newPegawaiExtra->desa_kel = $data->desa_kel;
        $newPegawaiExtra->rt = $data->rt;
        $newPegawaiExtra->rw = $data->rw;
        $newPegawaiExtra->kode_pos = $data->kodepos;

        $newPegawaiExtra->minat = $data->minat;
        $newPegawaiExtra->bakat = $data->bakat;
        $newPegawaiExtra->kompetensi = $data->kompetensi;
        $newPegawaiExtra->status_pernikahan = $data->status_pernikahan;


        $newPegawaiExtra->provinsi_2 = $data->provinsi_2;
        $newPegawaiExtra->kota_kab_2 = $data->kota_kab_2;
        $newPegawaiExtra->kecamatan_2 = $data->kecamatan_2;
        $newPegawaiExtra->desa_kel_2 = $data->desa_kel_2;
        $newPegawaiExtra->rt_2 = $data->rt_2;
        $newPegawaiExtra->rw_2 = $data->rw_2;
        $newPegawaiExtra->kode_pos_2 = $data->kodepos_2;

        $newPegawaiExtra->save();

        // $bioImg = SikBiodata::find($pegawaiId);

        // if (Storage::disk('public')->exists('profile/' . $bioImg->img_storage)) {
        //     Storage::disk('public')->delete('profile/' . $bioImg->img_storage);
        // }

        // if ($data->hasFile('imgFile') && $data->file('imgFile')->isValid()) {
        //     $image = $data->file('imgFile');
        //     // $imageName = time() . '.' . $image->getClientOriginalExtension();
        //     $imageName = $bioImg->nik . '_' . $bioImg->master_id . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();

        //     $status = Storage::disk('public')->putFileAs('profile', $image, $imageName);

        //     if ($status) {
        //         $bioImg->img_storage = $imageName;
        //         $bioImg->save();
        //     }
        // }



        // $simpleBiodata = SikBiodata::select('id', 'nik', 'fullname', 'unit_id', 'kelamin', 'status', 'telepon')->get();
        // $allUnit = SikUnitKerja::all();

        // for ($i = 0; $i < sizeof($simpleBiodata); $i++) {

        //     for ($j = 0; $j < sizeof($allUnit); $j++) {
        //         if (intval($allUnit[$j]['id']) === intval($simpleBiodata[$i]['unit_id'])) {
        //             $simpleBiodata[$i]['unit_name'] = $allUnit[$j]['name'];
        //         }
        //     }
        // }



        // return to_route('sik.dashboard');

        // STay on the page
        $data = SikBiodata::find(intval($pegawaiId));
        $dataExtra = SikExtraBiodata::where('biodata_id', intval($pegawaiId))->first();
        $getUnit = SikUnitKerja::orderBy('name')->get();
        $getJabFun = SikJabatanFungsional::orderBy('name')->get();
        $getJastur = SikJabatanStruktural::orderBy('name')->get();
        $getFakultas = SikFakultas::orderBy('name')->get();
        $getProdi = SikProdi::orderBy('name')->get();
        $getProvinsi = ProvinsiIndonesia::orderBy('name')->get();

        // $url = "";

        // if ($data['img_storage'] !== null) {

        //     $url = Storage::url('public/profile/' . $data->img_storage);
        //     $data['profile_img_path'] =  $url;
        // } else {
        //     $data['profile_img_path'] = null;
        // }

        if ($dataExtra->provinsi !== null) {
            $provinsi = $dataExtra->provinsi;
            $provinsi_2 = $dataExtra->provinsi_2;
            $kota_kab = $dataExtra->kota_kab;
            $kota_kab_2 = $dataExtra->kota_kab_2;
            $kecamatan = $dataExtra->kecamatan;
            $kecamatan_2 = $dataExtra->kecamatan_2;
        }

        $getKabupaten = [];
        $getKabupaten2 = [];
        $getKecamatan = [];
        $getKecamatan2 = [];
        $getDesa = [];
        $getDesa2 = [];


        if ($dataExtra->provinsi !== null) {
            $getKabupaten = KabupatenIndonesia::where('provinsi_id', $provinsi)->get();
            $getKabupaten2 = KabupatenIndonesia::where('provinsi_id', $provinsi_2)->get();
            $getKecamatan = KecamatanIndonesia::where('kabupaten_id', $kota_kab)->get();
            $getKecamatan2 = KecamatanIndonesia::where('kabupaten_id', $kota_kab_2)->get();
            $getDesa = DesaIndonesia::where('kecamatan_id', $kecamatan)->get();
            $getDesa2 = DesaIndonesia::where('kecamatan_id', $kecamatan_2)->get();
        }

        $activeId = Auth::id();

        $userActive = User::where('id', intval($activeId))->get();

        return Inertia::render('Sso/NewSsoDashboard', [
            "userAccount" => $userActive,
            'dataPegawai' => false,
            'selectedDataPegawai' => [$data, $dataExtra],
            'unitKerja' => $getUnit,
            'jabatanFun' => $getJabFun,
            'jabatanStr' => $getJastur,
            'fakultas' => $getFakultas,
            'prodi' => $getProdi,
            'listProvinsi' => $getProvinsi,
            'listKabupaten' => $getKabupaten,
            'listKabupaten2' => $getKabupaten2,
            'listKecamatan' => $getKecamatan,
            'listDesa' => $getDesa,
            'listKecamatan2' => $getKecamatan2,
            'listDesa2' => $getDesa2,
        ]);


        // Must FIX Later
        // $valid = $data->validate([
        //     'nik' => 'required',
        //     'fullname' => 'required|string',
        //     'tempat_lahir' => 'required|string',
        //     'tinggi_badan' => 'required',
        //     'berat_badan' => 'required',
        //     'telepon' => 'required',
        //     'email' => 'required|email',
        //     'pendidikan_terakhir' => 'required',
        //     'url_ijazah' => 'required|active_url',
        //     'no_bpjs_kes' => 'required',
        //     'no_bpjs_kerja' => 'required',
        //     'status' => 'required', // 1:dosen, 2: serdos
        //     'unit_id' => 'required',
        //     'jabatan_struktural_id' => 'required',
        //     'jabatan_fungsional_id' => 'required',
        //     'status_serdos' => 'required',
        //     'fakultas' => 'required',
        //     'prodi' => 'required',

        //     // Kerabat
        //     'kerabat_nama' => 'required|string',
        //     'kerabat_hubungan' => 'required|string',
        //     'kerabat_telepon' => 'required',

        //     // Alamat
        //     'provinsi' => 'required',
        //     'kota_kab' => 'required',
        //     'kecamatan' => 'required',
        //     'desa_kel' => 'required',
        //     'rt' => 'required',
        //     'rw' => 'required',
        //     'kodepos' => 'required',
        //     // Alamat 2
        //     'provinsi_2' => 'required',
        //     'kota_kab_2' => 'required',
        //     'kecamatan_2' => 'required',
        //     'desa_kel_2' => 'required',
        //     'rt_2' => 'required',
        //     'rw_2' => 'required',
        //     'kodepos_2' => 'required',
        // ]);
    }

    public function saveProfileImage(Request $img)
    {
        $targetBiodata = SikBiodata::find(intval($img->id));

        if ($img->hasFile('img_profile') && $img->file('img_profile')->isValid()) {
            $image = $img->file('img_profile');
            // $imageName = time() . '.' . $image->getClientOriginalExtension();
            $imageName = $targetBiodata->nik . '_' . $targetBiodata->master_id . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();

            // if (Storage::disk('public')->exists('profile/' . $targetBiodata->nik . '/' . $targetBiodata->img_storage)) {
            //     Storage::disk('public')->delete('profile/' . $targetBiodata->nik . '/' . $targetBiodata->img_storage);
            // }

            $targetUserNik = $targetBiodata->nik;
            $targetUserImg = $targetBiodata->img_storage;

            if (Storage::exists('public/profile/' . $targetUserNik . '/' . $targetUserImg)) {
                Storage::delete('public/profile/' . $targetUserNik . '/' . $targetUserImg);
            }

            $status = Storage::disk('public')->putFileAs('profile/' . $targetBiodata->nik . '/', $image, $imageName);

            if ($status) {
                Storage::disk('public')->setVisibility($status, 'public'); // Or use 'private' if appropriate

                $targetBiodata->img_storage = $imageName;
                $targetBiodata->save();

                $data = SikBiodata::find(intval($img->id));

                $url = "";

                if ($data['img_storage'] !== null) {

                    $url = Storage::url('public/profile/' . $targetBiodata->nik . '_' . $targetBiodata->master_id . '/' . $data->img_storage);
                    // $data['profile_img_path'] =  $url;
                }

                return response()->json(['success' => true, 'img_storage_path' => $url, 'message' => 'File uploaded successfully']);
            } else {
                return response()->json(['success' => false, 'img_storage_path' => "empty", 'message' => 'Error Occured !']);
            }
        } else {
            return response()->json(['success' => false, 'img_storage_path' => "empty", 'message' => 'No file uploaded']);
        }

        // Pass / NOT RUN ==========================

        // STay on the page
        $data = SikBiodata::find(intval($img->id));
        $dataExtra = SikExtraBiodata::where('biodata_id', intval($img->id))->first();

        $url = "";

        if ($data['img_storage'] !== null) {

            $url = Storage::url('public/profile/' . $targetBiodata->nik . '_' . $targetBiodata->master_id . '/' . $data->img_storage);
            $data['profile_img_path'] =  $url;
        } else {
            $data['profile_img_path'] = null;
        }

        $getUnit = SikUnitKerja::orderBy('name')->get();
        $getJabFun = SikJabatanFungsional::orderBy('name')->get();
        $getJastur = SikJabatanStruktural::orderBy('name')->get();
        $getFakultas = SikFakultas::orderBy('name')->get();
        $getProdi = SikProdi::orderBy('name')->get();
        $getProvinsi = ProvinsiIndonesia::orderBy('name')->get();

        if ($dataExtra->provinsi !== null) {
            $provinsi = $dataExtra->provinsi;
            $provinsi_2 = $dataExtra->provinsi_2;
            $kota_kab = $dataExtra->kota_kab;
            $kota_kab_2 = $dataExtra->kota_kab_2;
            $kecamatan = $dataExtra->kecamatan;
            $kecamatan_2 = $dataExtra->kecamatan_2;
        }

        $getKabupaten = [];
        $getKabupaten2 = [];
        $getKecamatan = [];
        $getKecamatan2 = [];
        $getDesa = [];
        $getDesa2 = [];


        if ($dataExtra->provinsi !== null) {
            $getKabupaten = KabupatenIndonesia::where('provinsi_id', $provinsi)->get();
            $getKabupaten2 = KabupatenIndonesia::where('provinsi_id', $provinsi_2)->get();
            $getKecamatan = KecamatanIndonesia::where('kabupaten_id', $kota_kab)->get();
            $getKecamatan2 = KecamatanIndonesia::where('kabupaten_id', $kota_kab_2)->get();
            $getDesa = DesaIndonesia::where('kecamatan_id', $kecamatan)->get();
            $getDesa2 = DesaIndonesia::where('kecamatan_id', $kecamatan_2)->get();
        }


        if ($img->initial === "sso") {
            $activeId = Auth::id();

            $userActive = User::where('id', intval($activeId))->get();

            return Inertia::render('Sso/NewSsoDashboard', [
                "userAccount" => $userActive,
                'dataPegawai' => false,
                'selectedDataPegawai' => [$data, $dataExtra],
                'unitKerja' => $getUnit,
                'jabatanFun' => $getJabFun,
                'jabatanStr' => $getJastur,
                'fakultas' => $getFakultas,
                'prodi' => $getProdi,
                'listProvinsi' => $getProvinsi,
                'listKabupaten' => $getKabupaten,
                'listKabupaten2' => $getKabupaten2,
                'listKecamatan' => $getKecamatan,
                'listDesa' => $getDesa,
                'listKecamatan2' => $getKecamatan2,
                'listDesa2' => $getDesa2,
            ]);
        }

        return Inertia::render('SimpegUnusida/SIKMain', [
            'dataPegawai' => false,
            'selectedDataPegawai' => [$data, $dataExtra],
            'unitKerja' => $getUnit,
            'jabatanFun' => $getJabFun,
            'jabatanStr' => $getJastur,
            'fakultas' => $getFakultas,
            'prodi' => $getProdi,
            'listProvinsi' => $getProvinsi,
            'listKabupaten' => $getKabupaten,
            'listKabupaten2' => $getKabupaten2,
            'listKecamatan' => $getKecamatan,
            'listDesa' => $getDesa,
            'listKecamatan2' => $getKecamatan2,
            'listDesa2' => $getDesa2,
        ]);
    }

    public function saveProfileImageTemp2(Request $img)
    {
        return var_dump($img['saveName']);
    }

    public function saveProfileImageTemp(Request $img)
    {
        // $targetBiodata = SikBiodata::find(intval($img->id));

        if ($img->hasFile('img_profile') && $img->file('img_profile')->isValid()) {
            $image = $img->file('img_profile');
            // $imageName = time() . '.' . $image->getClientOriginalExtension();

            // For later
            // $imageName = $img->saveName;

            $imageName = generateRandomImageName($image->getClientOriginalExtension());

            if (Storage::disk('public')->exists('temp/' . $img->saveBefore)) {
                Storage::disk('public')->delete('temp/' . $img->saveBefore);
            }

            // Storage::disk('public')->putFileAs('temp/', $image, $imageName);

            $status = Storage::disk('public')->put('temp/' . $imageName, file_get_contents($image));

            Storage::disk('public')->setVisibility($status, 'public'); // Or use 'private' if appropriate

            if ($status) {
                // $targetBiodata->img_storage = $imageName;
                // $targetBiodata->save();

                // Debug
                return response()->json(['success' => true, 'img_storage_path' => $imageName, 'message' => 'File uploaded successfully']);
            }
        }

        // STay on the page
        $getUnit = SikUnitKerja::orderBy('name')->get();
        $getJabFun = SikJabatanFungsional::orderBy('name')->get();
        $getJastur = SikJabatanStruktural::orderBy('name')->get();
        $getFakultas = SikFakultas::orderBy('name')->get();
        $getProdi = SikProdi::orderBy('name')->get();
        $getProvinsi = ProvinsiIndonesia::orderBy('name')->get();

        // $allUnit = SikUnitKerja::all();

        // for ($i = 0; $i < sizeof($simpleBiodata); $i++) {

        //     for ($j = 0; $j < sizeof($allUnit); $j++) {
        //         if (intval($allUnit[$j]['id']) === intval($simpleBiodata[$i]['unit_id'])) {
        //             $simpleBiodata[$i]['unit_name'] = $allUnit[$j]['unit_name'];
        //         }
        //     }
        // }

        return Inertia::render('SimpegUnusida/SIKMain', [
            // 'dataPegawai' => false,
            // 'selectedDataPegawai' => $data,
            'unitKerja' => $getUnit,
            'jabatanFun' => $getJabFun,
            'jabatanStr' => $getJastur,
            'fakultas' => $getFakultas,
            'prodi' => $getProdi,
            'listProvinsi' => $getProvinsi
        ]);
    }

    public function disableUser(Request $req)
    {
        $validated = $req->validate([
            "id" => "required|numeric|min_digits:1",
        ]);

        $selectedId = intval($validated['id']);

        $selectedBiodata = SikBiodata::find(intval($selectedId));
        $selectedExtraBio = SikExtraBiodata::where('biodata_id', intval($selectedId))->first();

        $selectedExtraBio->active = !$selectedExtraBio->active;
        $selectedExtraBio->save();

        $master_id = $selectedBiodata->master_id;

        $selectedBiodata->active = !$selectedBiodata->active;
        $selectedBiodata->save();

        $selectedUser = User::find($master_id);

        $selectedUser->active = !$selectedUser->active;

        $selectedUser->save();

        return response()->json(['success' => true, 'value' => $selectedUser->active]);

        // Render Back to Data Pegawai
        // $simpleBiodata = SikBiodata::select('id', 'master_id', 'nik', 'fullname', 'img_storage', 'unit_id', 'kelamin', 'tanggal_lahir', 'status', 'telepon', 'active')->get();
        // $allUnit = SikUnitKerja::all();

        // for ($i = 0; $i < sizeof($simpleBiodata); $i++) {

        //     $simpleBiodata[$i]['age'] = calculateAge($simpleBiodata[$i]['tanggal_lahir']);

        //     for ($j = 0; $j < sizeof($allUnit); $j++) {
        //         if (intval($allUnit[$j]['id']) === intval($simpleBiodata[$i]['unit_id'])) {
        //             $simpleBiodata[$i]['unit_name'] = $allUnit[$j]['name'];
        //         }
        //     }
        // }

        // $getProdi = SikProdi::orderBy('name')->get();

        // return Inertia::render('SimpegUnusida/SIKMain', [
        //     'dataPegawai' => $simpleBiodata,
        //     'prodi' => $getProdi,
        //     'refreshToken' => Str::random(5)
        // ]);
    }

    public function deleteUser(Request $req)
    {
        $validated = $req->validate([
            "id" => "required|numeric|min_digits:1",
            "active" => "required|numeric|min_digits:1",
            "master_id" => "required|numeric|min_digits:1",
            "nik" => "required|string",
        ]);

        $selectedId = intval($validated['id']);
        $master_id = intval($validated['master_id']);
        $active = intval($validated['active']);
        $nik = $validated['nik'];
        $isDelete = false;

        $targetUser = SikBiodata::where('nik', $nik)->first();
        $targetUserNik = $targetUser->nik;
        $targetUserImg = $targetUser->img_storage;

        if ($active === 0) {
            $isDelete = true;
        }

        $delete = false;
        $deleteBiodata = false;

        if ($isDelete) {
            $allTargetTask = SikKinerjaTask::where('assigned_biodata_id', $selectedId)->delete();
            $deleteBiodata = SikBiodata::destroy($selectedId);
            $delete = User::destroy($master_id);
        }

        // Render Back to Data Pegawai
        $simpleBiodata = SikBiodata::select('id', 'master_id', 'nik', 'fullname', 'img_storage', 'unit_id', 'kelamin', 'tanggal_lahir', 'status', 'telepon', 'active')->get();
        $allUnit = SikUnitKerja::all();

        for ($i = 0; $i < sizeof($simpleBiodata); $i++) {

            $simpleBiodata[$i]['age'] = calculateAge($simpleBiodata[$i]['tanggal_lahir']);

            for ($j = 0; $j < sizeof($allUnit); $j++) {
                if (intval($allUnit[$j]['id']) === intval($simpleBiodata[$i]['unit_id'])) {
                    $simpleBiodata[$i]['unit_name'] = $allUnit[$j]['name'];
                }
            }
        }

        $getProdi = SikProdi::orderBy('name')->get();

        // Ensure the folder exists before attempting to delete
        if (Storage::exists('public/profile/' . $targetUserNik)) {
            Storage::deleteDirectory('public/profile/' . $targetUserNik);
            // return response()->json(['message' => 'Folder deleted successfully']);
        } else {
            // return response()->json(['error' => 'Folder not found'], 404);
        }

        if ($delete && $deleteBiodata) {
            return response()->json(["success" => true], 204);
            // return Inertia::render('SimpegUnusida/SIKMain', [
            //     'dataPegawai' => $simpleBiodata,
            //     'prodi' => $getProdi,
            //     'refreshToken' => Str::random(5),
            //     'callToast' => ["Berhasil Menghapus", "success", Str::random(5)]
            // ]);
        }

        return response()->json([
            "success" => false,
            "debug_delete" => $delete,
            "debug_delete_biodata" => $deleteBiodata,
            "debug_value" => [
                "id" => $selectedId,
                "master_d" => $master_id,
                "active" => $active,
                "nik" => $nik
            ],
        ], 400);
        // return Inertia::render('SimpegUnusida/SIKMain', [
        //     'dataPegawai' => $simpleBiodata,
        //     'prodi' => $getProdi,
        //     'refreshToken' => Str::random(5),
        //     'callToast' => ["Gagal Menghapus", "warning", Str::random(5)]
        // ]);
    }

    public function determineUserType()
    {
        $activeID = intval(Auth::id());

        $tryBiodata = SikBiodata::where('master_id', $activeID)->first();

        $defaultStatus = 4;

        $targetName = "User";
        $jabatanName = "Default";
        $username = "default";
        $statusKepegawaian = "Default";
        $imageSrc = "https://storage.unusida.id/storage/images/no-image-placeholder.png";

        // For Dosen Variable
        $forDosen = false;
        $isTendikButTu = false;

        // $isDataFilled = false;

        if ($tryBiodata) {
            if (intval($tryBiodata->status) === 5) {
                $isTendikButTu = true;
            }

            $tryUser = User::find(intval($activeID));
            $username = $tryUser->username;

            $targetName = $tryBiodata->fullname;
            $imageSrc = "/storage/profile/" . $tryBiodata->nik . "/" . $tryBiodata->img_storage;

            if (intval($tryBiodata->status) === 5) {
                $targetFakultas = SikFakultas::find(intval($tryBiodata->fakultas_id));

                $targetStaffFakultas = SikStaffFakultasModel::find(intval($tryBiodata->staff_fakultas_id));

                $jabatanName = $targetStaffFakultas->name . ", Fakultas " . ucwords($targetFakultas->name);

                $statusKepegawaian = "Tendik";
            } else {
                if (intval($tryBiodata->jabatan_strukdos_id) === 0) {
                    $getStruktural = SikJabatanStruktural::find(intval($tryBiodata->jabatan_struktural_id));

                    $statusKepegawaian = intval($tryBiodata->status) === 2 || intval($tryBiodata->status) === 4  ? "Dosen" : "Tendik";
                    $jabatanName = $getStruktural->name;
                } else {
                    $getStruktural = SikJabatanStrukDos::find(intval($tryBiodata->jabatan_strukdos_id));

                    $statusKepegawaian = intval($tryBiodata->status) === 2 || intval($tryBiodata->status) === 4  ? "Dosen" : "Tendik";
                    $jabatanName = $getStruktural->name;
                }

                if ($getStruktural->jabatan_level == 1) {
                    $defaultStatus = 1;
                } else {
                    $defaultStatus = 4;
                }
            }

            // return response()->json($getStruktural);
            // TU Fakultas problem "intval($tryBiodata->status) === 5"
            if ((intval($tryBiodata->status) === 2 || intval($tryBiodata->status) === 4) && intval($tryBiodata->jabatan_struktural_id) === 0) {
                $forDosen = true;
            }

            // $isDataFilled = boolval($tryBiodata->data_filled);
        } else {
            $tryUser = User::find(intval($activeID));

            $targetName = $tryUser->name;
            $username = $tryUser->username;
            $jabatanName = "Special Access";
            $statusKepegawaian = "Admin";

            $defaultStatus = 1;

            if ($tryUser->username == "superadmin") {
                $defaultStatus = 0;
            }
            // Add Another
        }

        $payload = [
            "accessStatus" => $defaultStatus,
            "fullname" => $targetName,
            "jabatan" => $jabatanName,
            "username" => $username,
            "statusKepegawaian" => $statusKepegawaian,
            "forDosen" => $forDosen,
            "isTendikButTu" => $isTendikButTu,
            "imgSrc" => $imageSrc,
            // "dataFilled" => $isDataFilled
        ];

        return response()->json($payload);
    }

    public function getCurrentUserId(Request $data)
    {
        $targetUser = SikBiodata::where('nik', intval($data->nik))->first();

        if ($targetUser) {
            return response()->json(['id' => $targetUser->id, "good" => true]);
        }
        return response()->json(["good" => false]);
    }

    public function getImgStorage(Request $data)
    {
        $targetUser = SikBiodata::where('nik', $data->nik)->first();

        $imgStorage = $targetUser->img_storage;

        if ($targetUser) {
            return response()->json(['img_storage' => $imgStorage, 'good' => true]);
        }
        return response()->json(['img_storage' => $imgStorage, 'good' => false]);
    }

    public function getStatusKerjaList(Request $req)
    {
        $validated = $req->validate([
            "by" => "required|in:@rnamikaze"
        ]);

        $statusKerjaList = SikBaseKpi::all();
        $staffFakultasList = SikStaffFakultasModel::all();

        return response()->json([
            "success" => true,
            "statusKerjaList" => $statusKerjaList,
            "staffFakultasList" => $staffFakultasList
        ]);
    }

    public function getDataPegawaiAxios(Request $req)
    {
        $validated = $req->validate([
            "by" => "required|in:@rnamikaze",
            "biodata_id" => "required|numeric|min_digits:1"
        ]);

        $biodataId = intval($validated['biodata_id']);
        $targetBiodata = SikBiodata::find($biodataId);
        $extraBiodataId = intval($targetBiodata->id);
        $targetExtraBiodata = SikExtraBiodata::where('biodata_id', $extraBiodataId)->first();

        $payload = [
            "initial" => "sik",
            "modeEdit" => null,
            "mainId" => $targetBiodata->id,
            "nik" => $targetBiodata->nik,
            "fullname" => $targetBiodata->fullname,
            "tempat_lahir" => $targetBiodata->tempat_lahir,
            "tanggal_lahir" => $targetBiodata->tanggal_lahir,
            "tinggi_badan" => $targetBiodata->tinggi_badan,
            "berat_badan" => $targetBiodata->berat_badan,
            "kelamin" => $targetBiodata->kelamin,
            "nidn" => $targetBiodata->nidn,

            "telepon" => $targetBiodata->telepon,
            "email" => $targetBiodata->email,
            "pendidikan_terakhir" => $targetBiodata->pendidikan_terakhir,
            "url_ijazah" => $targetExtraBiodata->url_ijazah,
            "no_bpjs_kes" => $targetBiodata->no_bpjs_kes,
            "no_bpjs_kerja" => $targetBiodata->no_bpjs_kerja,
            "staff_fakultas_id" => $targetBiodata->staff_fakultas_id,

            // Status Kerja
            "status" => $targetBiodata->status, // 1" => tendik, 2" => dosen
            "unit_id" => $targetBiodata->unit_id,
            "jabatan_struktural_id" => $targetBiodata->jabatan_struktural_id,
            "jabatan_fungsional_id" => $targetBiodata->jabatan_fungsional_id,
            "status_serdos" => $targetBiodata->status_serdos,
            "fakultas" => $targetBiodata->fakultas_id,
            "prodi" => $targetBiodata->prodi_id,
            "imgFile" => null,

            // Kerabat
            "kerabat_nama" => $targetBiodata->kerabat_nama,
            "kerabat_hubungan" => $targetBiodata->kerabat_hubungan,
            "kerabat_telepon" => $targetBiodata->kerabat_telepon,

            // Alamat
            "provinsi" => $targetExtraBiodata->provinsi,
            "kota_kab" => $targetExtraBiodata->kota_kab,
            "kecamatan" => $targetExtraBiodata->kecamatan,
            "desa_kel" => $targetExtraBiodata->desa_kel,
            "rt" => $targetExtraBiodata->rt,
            "rw" => $targetExtraBiodata->rw,
            "kodepos" => $targetExtraBiodata->kode_pos,
            // Alamat 2
            "provinsi_2" => $targetExtraBiodata->provinsi_2,
            "kota_kab_2" => $targetExtraBiodata->kota_kab_2,
            "kecamatan_2" => $targetExtraBiodata->kecamatan_2,
            "desa_kel_2" => $targetExtraBiodata->desa_kel_2,
            "rt_2" => $targetExtraBiodata->rt_2,
            "rw_2" => $targetExtraBiodata->rw_2,
            "kodepos_2" => $targetExtraBiodata->kode_pos_2,
            "imgTemp" => null,
            "jabatan_strukdos_id" => $targetBiodata->jabatan_strukdos_id,

            "kepangkatan" => $targetExtraBiodata->kepangkatan,
            "studi_lanjut" => $targetExtraBiodata->studi_lanjut,

            // New Collumn
            "nik_ktp" => $targetBiodata->nik_ktp,
            "awal_kerja" => $targetBiodata->awal_kerja,
            "minat" => $targetExtraBiodata->minat,
            "bakat" => $targetExtraBiodata->bakat,
            "kompetensi" => $targetExtraBiodata->kompetensi,
            "status_pernikahan" => $targetExtraBiodata->status_pernikahan,
            "status_kerja" => $targetBiodata->status_kerja,
        ];

        return response()->json([
            "success" => true,
            // "debug" => $biodataId,
            "selectedDataPegawai" => $payload
        ]);
    }
}
