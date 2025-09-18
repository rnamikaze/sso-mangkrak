<?php

namespace App\Http\Controllers\SIK;

use App\Exports\ExcelAbsensiExport;
use App\Exports\KpiSummary;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\SIK\SikBiodata;
use App\Imports\SIK\PresensiFirst;
use App\Models\SIK\SikKinerjaTask;
use App\Http\Controllers\Controller;
use App\Models\JabatanBaseNominalModels;
use App\Models\SIK\SikExtraBiodata;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\SIK\SikKinerjaSubTask;
use App\Models\SIK\SikJabatanStruktural;
use App\Models\SIK\SikKinerjaDosenTask;
use App\Models\SIK\SikKinerjaSubDosenTask;
use App\Models\SIK\SikUnitKerja;
use App\Models\SikBaseKpi;

function formatDate(Carbon $date)
{
    return $date->format('d_m_y_H_i_s');
}

function getActiveUserInfo()
{
    $user = User::find(intval(Auth::id()));
    $userInfo = [$user->id, $user->email, $user->name, $user->nik];

    return $userInfo;
}

function removeNonNumbers($str)
{
    return preg_replace('/\D/', '', $str);
}

function removeNonNumbersAndAlphabets($str)
{
    return preg_replace('/[^a-zA-Z0-9]/', '', $str);
}

function calculatePeriod($inputDate = null)
{
    $buffer = $inputDate === null ? Carbon::now('Asia/Jakarta') : Carbon::createFromFormat('Y-m-d', $inputDate);
    // Convert input date to Carbon instance for easier date manipulation
    $inputDate = $buffer;

    // if ($inputDate !== null) {
    //     // Convert input date to Carbon instance for easier date manipulation
    //     $inputDate = \Carbon\Carbon::createFromFormat('Y-m-d', $inputDate);
    // }

    // Get the day of the input date
    $dayOfMonth = $inputDate->day;

    // Initialize start and end periods
    $startPeriod = null;
    $endPeriod = null;

    if ($dayOfMonth < 20) {
        // If the day is before 20th of the month
        $startPeriod = $inputDate->copy()->subMonth()->startOfMonth()->addDays(19);
        $endPeriod = $inputDate->copy()->startOfMonth()->addDays(18);
    } else {
        // If the day is on or after 20th of the month
        $startPeriod = $inputDate->copy()->startOfMonth()->addDays(19);
        $endPeriod = $inputDate->copy()->addMonth()->startOfMonth()->addDays(18);
    }

    return [$startPeriod->format('Y-m-d'), $endPeriod->format('Y-m-d')];
}

function getKpiAndNominal($startDate, $endDate, $targetId)
{
    $targetBiodata = SikBiodata::find(intval($targetId));

    if (!$targetBiodata) {
        return [
            'id' => $targetId,
            "fullname" => "Null",
            "nik" => "Null",
            "kpiValue" => "Null",
            "nominalFromKpi" => "Null",
            "baseNominal" => "Null"
        ];
    }

    $targetBaseNominal = SikBaseKpi::where('status_kerja_id', $targetBiodata->status_kerja)->value('base') ?? 0;
    $statusPegawai = intval($targetBiodata->status);

    $tasksModel = ($statusPegawai === 5) ? SikKinerjaDosenTask::class : SikKinerjaTask::class;
    $tasks = null;

    if ($statusPegawai === 5) {
        $tasks = $tasksModel::where('assigned_biodata_id', $targetBiodata->id)
            ->whereBetween('created_at', ["{$startDate} 00:00:00", "{$endDate} 23:59:59"])
            ->get();
    } else {
        $tasks = $tasksModel::where('assigned_biodata_id', $targetBiodata->id)
            ->whereBetween('created_at', ["{$startDate} 00:00:00", "{$endDate} 23:59:59"])
            // ->where('is_validated', true)
            ->get();
    }


    $targetAbsensi = SikExtraBiodata::where('biodata_id', $targetBiodata->id)->value('current_absensi_json');
    $arrAbsensi = json_decode($targetAbsensi) ?? (object)["hadir" => 0, "terlambat" => 0, "tidak_hadir" => 0];

    $sumAllPercentage = 0;
    $taskCount = $tasks->count();

    foreach ($tasks as $task) {
        $subTaskModel = ($statusPegawai === 5) ? SikKinerjaSubDosenTask::class : SikKinerjaSubTask::class;
        $taskPercentages = $subTaskModel::where('task_id', $task->id)->pluck('progress_int');

        $task->percentage_counted = $taskPercentages->avg() ?? 0;
        $sumAllPercentage += $task->percentage_counted;
    }

    $allTaskAverage = ($taskCount > 0) ? number_format($sumAllPercentage / $taskCount, 2) : 0;

    $finalKpiValue = (($arrAbsensi->hadir * 20) + ($arrAbsensi->terlambat * 5) + ($arrAbsensi->tidak_hadir * 5) + ($allTaskAverage * 70)) / 100;
    $kpiBerapaDuit = ($finalKpiValue * $targetBaseNominal) / 100;

    $categories = [
        ['id' => 1, 'title' => "Sangat baik", 'from' => 91, 'to' => 100],
        ['id' => 2, 'title' => "Baik", 'from' => 81, 'to' => 90],
        ['id' => 3, 'title' => "Cukup baik", 'from' => 71, 'to' => 80],
        ['id' => 4, 'title' => "Kurang baik", 'from' => 61, 'to' => 70],
        ['id' => 5, 'title' => "Sangat tidak baik", 'from' => 0, 'to' => 60],
    ];

    $statusPencapaian = collect($categories)->firstWhere(fn($c) => $finalKpiValue >= $c['from'] && $finalKpiValue <= $c['to'])['id'] ?? 5;

    return [
        'id' => $targetBiodata->id,
        "fullname" => $targetBiodata->fullname,
        "nik" => $targetBiodata->nik,
        "kpiValue" => number_format($finalKpiValue, 2),
        "nominalFromKpi" => $kpiBerapaDuit,
        "baseNominal" => $targetBaseNominal,
        "statusPencapaian" => $statusPencapaian,
    ];
}


class KpiMainControllers extends Controller
{
    private function sanitizeString($inputString)
    {
        return preg_replace("/[^a-zA-Z0-9]/", "", $inputString);
    }

    public function importAbsensi(Request $file)
    {
        $tempFile = null;

        $uploadSuccess = false;
        $filename = formatDate(Carbon::now()) . ".xlsx";

        if ($file->hasFile('excel_file') && $file->file('excel_file')->isValid()) {
            $tempFile = $file->excel_file;
            // $filePath = Storage::url('public/excel/' . $filename);
            // $filePath = "http://localhost:8000/storage/excel/26_03_24_04_59_34.xlsx";

            // $sheetName = Excel::getSheetNames($tempFile)[0];
            $data = Excel::toCollection(new PresensiFirst(), $tempFile);

            return response()->json(["good" => true, "import" => $data]);
        }
        return response(["good" => false]);
    }

    public function importAbsensiAdmin(Request $file)
    {
        $tempFile = null;

        $uploadSuccess = false;
        $filename = formatDate(Carbon::now()) . ".xlsx";

        if ($file->hasFile('excel_file') && $file->file('excel_file')->isValid()) {
            $tempFile = $file->excel_file;
            // $filePath = Storage::url('public/excel/' . $filename);
            // $filePath = "http://localhost:8000/storage/excel/26_03_24_04_59_34.xlsx";

            // $sheetName = Excel::getSheetNames($tempFile)[0];
            $data = Excel::toCollection(new PresensiFirst(), $tempFile);

            return response()->json(["good" => true, "import" => $data]);
        }
        return response(["good" => false]);
    }

    public function saveImportAbsensiAdmin(Request $newAbsensi)
    {
        $data = $newAbsensi->data;

        // return response()->json($data[0]['rn_nik']);

        $skipped = [];

        for ($i = 0; $i < sizeof($data); $i++) {
            $targetNik = $data[$i]['rn_nik'];
            $targetBio = SikBiodata::where('nik', removeNonNumbersAndAlphabets($targetNik))->first();
            if (!$targetBio) {
                array_push($skipped, [
                    "nik" => $data[$i]['rn_nik']
                ]);
                continue;
            }
            $targetId = $targetBio->id;
            $targetAbsensi = SikExtraBiodata::find($targetId);

            // if ($data[$i]['rn_abs_hadir'] !== "") {
            //     $valueHadir = $data[$i]['rn_abs_hadir'];
            // }

            // if ($data[$i]['rn_abs_terlambat'] !== "") {
            //     $valueTerlambat = $data[$i]['rn_abs_terlambat'];
            // }

            // if ($data[$i]['rn_abs_tidak_hadir'] !== "") {
            //     $valueTidakHadir = $data[$i]['rn_abs_tidak_hadir'];
            // }

            $absensiArray = ["hadir" => intval($data[$i]['rn_abs_hadir']), "terlambat" => intval($data[$i]['rn_abs_terlambat']), "tidak_hadir" => intval($data[$i]['rn_abs_tidak_hadir'])];
            $targetAbsensi->current_absensi_json = json_encode($absensiArray);
            $targetAbsensi->save();
        }

        return response()->json(["good" => true]);
    }

    public function getAllAbsensiValue()
    {
        $allBiodata = SikBiodata::where('active', 1)->get();
        $allExtraBiodata = SikExtraBiodata::all();

        $absensiArray = [];

        for ($i = 0; $i < sizeof($allBiodata); $i++) {
            $name = $allBiodata[$i]->fullname;
            $nik = $allBiodata[$i]->nik;
            $absensi = null;
            for ($j = 0; $j < sizeof($allExtraBiodata); $j++) {
                if (intval($allBiodata[$i]->id) === intval($allExtraBiodata[$j]->biodata_id)) {
                    $absensi = json_decode($allExtraBiodata[$j]->current_absensi_json);
                }
            }
            array_push($absensiArray, ['fullname' => $name, 'nik' => $nik, "absensi" => $absensi]);
        }

        $targetLatestAbsensi = SikExtraBiodata::orderBy('updated_at', 'desc')->first();
        $latestDate = Carbon::parse($targetLatestAbsensi->updated_at)->format('H:i:s d/m/Y');

        return response()->json(["absensi" => $absensiArray, "lastUpload" => $latestDate]);
    }

    // Get Task Percentage with All its Task Info
    public function getTaskPercentage(Request $taskInfo)
    {
        // Temporary get by Active User
        $userActiveId = Auth::id();
        $userActiveName = getActiveUserInfo()[2];

        $startDate = $taskInfo->startDate;
        $endDate = $taskInfo->endDate;

        $getTask = SikKinerjaTask::where('assigned_biodata_id', $userActiveId)
            ->whereRaw("DATE(created_at) BETWEEN ? AND ?", [$startDate, $endDate])->get();

        $targetBiodata = SikBiodata::find(intval($userActiveId));

        $targetJabatan = SikJabatanStruktural::find(intval($targetBiodata->jabatan_struktural_id));

        if ($getTask) {
            for ($t = 0; $t < sizeof($getTask); $t++) {
                // Mimic the Task Initial Value in TaskList.jsx
                $getTask[$t]->assigne_name = $userActiveName;

                // Ambil Semua Sub Task
                $getAllSubTask = SikKinerjaSubTask::where('task_id', intval($getTask[$t]->id))->get();

                // $progressSum = SikKinerjaSubTask::where('task_id', intval($getTask[$t]->id))->sum('progress_int');
                // $progressCount = SikKinerjaSubTask::where('task_id', intval($getTask[$t]->id))->count();

                // if ($progressCount < 1) {
                //     $percentage = 0;
                // } else {
                //     $percentage = ($progressSum / ($progressCount * 2)) * 100;
                // }

                // New Calculation == 4 <<
                // Fetch the task_percentage column values from the database

                $taskPercentages = SikKinerjaSubTask::where('task_id', intval($getTask[$t]->id))->pluck('progress_int');

                // Map the values of task_percentage to their respective percentages
                $mappedPercentages = $taskPercentages->map(function ($value) {
                    switch (intval($value)) {
                        // case 0:
                        //     return 0;
                        // case 1:
                        //     return 50;
                        // case 2:
                        //     return 100;
                        default:
                            return $value; // Handle unexpected values, though it's assumed there are none
                    }
                });

                // Calculate the overall percentage
                $percentage = $mappedPercentages->avg();
                // End of new calculation == 4 <<

                $getTask[$t]->percentage_counted = $percentage;
                $getTask[$t]->childTask = $getAllSubTask ? $getAllSubTask : [];
                $getTask[$t]->jabatan_level = $targetJabatan->jabatan_level;
            }
        }

        return response()->json($getTask);
    }

    // Get Only the Task Percentage
    public function getTaskPercentageOnly(Request $taskInfo)
    {
        $validated = $taskInfo->validate([
            "by" => "required|in:@rnamikaze",
            "startDate" => "required|date",
            "endDate" => "required|date"
        ]);

        // Temporary get by Active User
        // ID Users is different at ID Sik_Biodata Database
        $userActiveId = Auth::id();
        $userActiveName = getActiveUserInfo()[2];

        // Retrive Biodata ID
        $targetBiodata = SikBiodata::where('master_id', intval($userActiveId))->first();
        $statusPegawai = intval($targetBiodata->status);

        $targetBiodataId = intval($targetBiodata->id);

        $targetBaseDb = SikBaseKpi::where('status_kerja_id', intval($targetBiodata->status_kerja))->first();

        // Target User Base Nominal
        $targetBaseNominal = 0;
        if ($targetBaseDb) {
            $targetBaseNominal = $targetBaseDb->base;
        }

        // Legacy KPI
        // $targetJabatanStruktural = SikJabatanStruktural::find(intval($targetBiodata->jabatan_struktural_id));
        // $targetBaseDb = JabatanBaseNominalModels::where('jabatan_level', intval($targetJabatanStruktural->jabatan_level))->first();

        // // Target User Base Nominal
        // $targetBaseNominal = 0;
        // if ($targetBaseDb) {
        //     $targetBaseNominal = $targetBaseDb->base_nominal_kpi;
        // }

        $startDate = $taskInfo->startDate;
        $endDate = $taskInfo->endDate;

        $getTask = null;

        // Main Get Task Database Query without task that hasnt done or not 100%
        if ($statusPegawai === 5) {
            $getTask = SikKinerjaDosenTask::where('assigned_biodata_id', $targetBiodataId)
                ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->get();
        } else {
            // Validation work onli here
            $getTask = SikKinerjaTask::where('assigned_biodata_id', $targetBiodataId)
                ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                // ->where('is_validated', true)
                ->get();
        }


        // ->whereRaw("DATE(created_at) BETWEEN ? AND ?", [$startDate, $endDate])->get();

        // Extra Get Task Database Query WITH task that hasnt done or not 100%
        // $getTask = SikKinerjaTask::where('assigned_biodata_id', $targetBiodataId)
        //     ->whereRaw("DATE(created_at) BETWEEN ? AND ?", [$startDate, $endDate])
        //     ->orWhere('assigned_biodata_id', $targetBiodataId)->where("progress_percentage", "<", 100)->get();

        // $getTask = SikKinerjaTask::where('assigned_biodata_id', $targetBiodataId)->get();

        // return response()->json($getTask);

        $targetJabatan = SikJabatanStruktural::find(intval($targetBiodata->jabatan_struktural_id));

        // return response()->json($targetJabatan);
        $payload = [];
        $sumAllPercentage = 0;
        $taskAvailableLength = 0;
        $arrAbsensi = [];

        // Get absensi from active account
        $targetAbsensi = SikExtraBiodata::where('biodata_id', $targetBiodataId)->first();

        if ($targetAbsensi) {
            $rawAbsensi = $targetAbsensi->current_absensi_json;
            $arrAbsensi = json_decode($rawAbsensi);
        }

        if ($getTask) {
            for ($t = 0; $t < sizeof($getTask); $t++) {
                // Mimic the Task Initial Value in TaskList.jsx
                $getTask[$t]->assigne_name = $userActiveName;

                $getAllSubTask = null;
                $taskPercentages = null;

                // Ambil Semua Sub Task
                if ($statusPegawai === 5) {
                    // Fetch all sub-tasks in one query
                    $getAllSubTask = SikKinerjaSubDosenTask::where('task_id', intval($getTask[$t]->id))->get();

                    // Extract the progress_int values using pluck() on the collection
                    $taskPercentages = $getAllSubTask->pluck('progress_int');
                } else {
                    // Fetch all sub-tasks in one query
                    $getAllSubTask = SikKinerjaSubTask::where('task_id', intval($getTask[$t]->id))->get();

                    // Extract the progress_int values using pluck() on the collection
                    $taskPercentages = $getAllSubTask->pluck('progress_int');
                }

                // $progressSum = SikKinerjaSubTask::where('task_id', intval($getTask[$t]->id))->sum('progress_int');
                // $progressCount = SikKinerjaSubTask::where('task_id', intval($getTask[$t]->id))->count();

                // if ($progressCount < 1) {
                //     $percentage = 0;
                // } else {
                //     $percentage = ($progressSum / ($progressCount * 2)) * 100;
                // }

                // New Calculation == 4 <<
                // Fetch the task_percentage column values from the database

                // Map the values of task_percentage to their respective percentages
                $mappedPercentages = $taskPercentages->map(function ($value) {
                    switch (intval($value)) {
                        // case 0:
                        //     return 0;
                        // case 1:
                        //     return 50;
                        // case 2:
                        //     return 100;
                        default:
                            return $value; // Handle unexpected values, though it's assumed there are none
                    }
                });

                // Calculate the overall percentage
                $percentage = $mappedPercentages->avg();
                // End of new calculation == 4 <<

                $getTask[$t]->percentage_counted = $percentage;
                $getTask[$t]->childTask = $getAllSubTask ? $getAllSubTask : [];
                $getTask[$t]->jabatan_level = $targetJabatan->jabatan_level;

                $payload2 = ["title" => $getTask[$t]->title, "percentage_counted" => $percentage];

                array_push($payload, $payload2);
            }
        }

        // Counte Average of $payload (all task)
        $valueSum = 0;
        $valueLength = sizeof($payload);
        $allTaskAverage = 0;

        foreach ($payload as $value) {
            $valueSum += $value['percentage_counted'];
        }

        $allTaskAverage = sizeof($payload) > 0 ? ($valueSum / $valueLength) : 0;

        // Count KPI Value
        $finalKpiValue = (($arrAbsensi->hadir * 20) + ($arrAbsensi->terlambat * 5) + ($arrAbsensi->tidak_hadir * 5) + ($allTaskAverage * 70)) / 100;
        $kpiBerapaDuit = ($finalKpiValue * $targetBaseNominal) / 100;
        $statusPencapaian = 1;

        $categories = [
            [
                'id' => 1,
                'title' => "Sangat baik",
                'valueTo' => 100,
                'valueFrom' => 91,
            ],
            [
                'id' => 2,
                'title' => "Baik",
                'valueTo' => 90,
                'valueFrom' => 81,
            ],
            [
                'id' => 3,
                'title' => "Cukup baik",
                'valueTo' => 80,
                'valueFrom' => 71,
            ],
            [
                'id' => 4,
                'title' => "Kurang baik",
                'valueTo' => 70,
                'valueFrom' => 61,
            ],
            [
                'id' => 5,
                'title' => "Sangat tidak baik",
                'valueTo' => 60,
                'valueFrom' => 0,
            ],
        ];

        foreach ($categories as $category) {
            if ($finalKpiValue >= $category['valueFrom'] && $finalKpiValue <= $category['valueTo']) {
                $statusPencapaian = $category['id'];
                break; // Exit loop once the correct category is found
            }
        }

        // Output
        // {
        //     "taskPercentage": [
        //       {
        //         "title": "SIPEKA",
        //         "percentage_counted": 69.28571428571429
        //       }
        //     ],
        //     "baseNominal": 300000,
        //     "arrAbsensi": {
        //       "hadir": 32,
        //       "terlambat": 58,
        //       "tidak_hadir": 10
        //     }
        //   }

        return response()->json([
            "success" => true,
            "taskPercentage" => $payload,
            "baseNominal" => $targetBaseNominal,
            "arrAbsensi" => $arrAbsensi,
            "finalKpiValue" => $finalKpiValue,
            "kpiBerapaDuit" => $kpiBerapaDuit,
            "taskAverage" => $allTaskAverage,
            "statusPencapaian" => $statusPencapaian,
            // "debug" => [
            //     "task" => $getTask,
            //     "date" => [$startDate, $endDate],
            //     "assignedId" => $targetBiodataId
            // ]
        ], 201);
    }

    // Get Only the Task Percentage Per User
    public function getTaskPercentageOnlyPerUser(Request $taskInfo)
    {
        // Temporary get by Active User
        // ID Users is different at ID Sik_Biodata Database
        $userActiveId = intval($taskInfo->id);
        $userActiveName = getActiveUserInfo()[2];

        // Half Debug Return / Comment if Deploy
        // return response()->json($userActiveId);

        // Retrive Biodata ID
        $targetBiodata = SikBiodata::where('id', intval($userActiveId))->first();
        $statusPegawai = intval($targetBiodata->status);
        $targetBiodataId = $targetBiodata->id;

        $targetBaseDb = SikBaseKpi::where('status_kerja_id', intval($targetBiodata->status_kerja))->first();

        // Target User Base Nominal
        $targetBaseNominal = 0;
        if ($targetBaseDb) {
            $targetBaseNominal = $targetBaseDb->base;
        }

        // Legacy KPI
        // $targetJabatanStruktural = SikJabatanStruktural::find(intval($targetBiodata->jabatan_struktural_id));
        // $targetBaseDb = JabatanBaseNominalModels::where('jabatan_level', intval($targetJabatanStruktural->jabatan_level))->first();

        // // Target User Base Nominal
        // $targetBaseNominal = 0;
        // if ($targetBaseDb) {
        //     $targetBaseNominal = $targetBaseDb->base_nominal_kpi;
        // }

        $startDate = $taskInfo->startDate;
        $endDate = $taskInfo->endDate;

        $getTask = null;

        // Main Get Task Database Query without task that hasnt done or not 100%
        if ($statusPegawai === 5) {
            $getTask = SikKinerjaDosenTask::where('assigned_biodata_id', $targetBiodataId)
                ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->get();
        } else {
            // validate work here
            $getTask = SikKinerjaTask::where('assigned_biodata_id', $targetBiodataId)
                ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                // ->where('is_validated', true)
                ->get();
        }

        // $getTask = SikKinerjaTask::where('assigned_biodata_id', $targetBiodataId)
        //     ->whereRaw("DATE(created_at) BETWEEN ? AND ?", [$startDate, $endDate])->get();

        // Extra Get Task Database Query WITH task that hasnt done or not 100%
        // $getTask = SikKinerjaTask::where('assigned_biodata_id', $targetBiodataId)
        //     ->whereRaw("DATE(created_at) BETWEEN ? AND ?", [$startDate, $endDate])
        //     ->orWhere('assigned_biodata_id', $targetBiodataId)->where("progress_percentage", "<", 100)->get();

        // $getTask = SikKinerjaTask::where('assigned_biodata_id', $targetBiodataId)->get();

        // return response()->json($getTask);

        $targetJabatan = SikJabatanStruktural::find(intval($targetBiodata->jabatan_struktural_id));

        // return response()->json($targetJabatan);
        $payload = [];
        $sumAllPercentage = 0;

        $arrAbsensi = [];
        // Get absensi from active account
        $targetAbsensi = SikExtraBiodata::where('biodata_id', $targetBiodataId)->first();

        if ($targetAbsensi) {
            $rawAbsensi = $targetAbsensi->current_absensi_json;
            $arrAbsensi = json_decode($rawAbsensi);
        }


        if ($getTask) {
            for ($t = 0; $t < sizeof($getTask); $t++) {
                // Mimic the Task Initial Value in TaskList.jsx
                $getTask[$t]->assigne_name = $userActiveName;

                $getAllSubTask = null;
                $taskPercentages = null;

                // Ambil Semua Sub Task
                if ($statusPegawai === 5) {
                    // Fetch all sub-tasks in one query
                    $getAllSubTask = SikKinerjaSubDosenTask::where('task_id', intval($getTask[$t]->id))->get();

                    // Extract the progress_int values using pluck() on the collection
                    $taskPercentages = $getAllSubTask->pluck('progress_int');
                } else {
                    // Fetch all sub-tasks in one query
                    $getAllSubTask = SikKinerjaSubTask::where('task_id', intval($getTask[$t]->id))->get();

                    // Extract the progress_int values using pluck() on the collection
                    $taskPercentages = $getAllSubTask->pluck('progress_int');
                }
                // $progressSum = SikKinerjaSubTask::where('task_id', intval($getTask[$t]->id))->sum('progress_int');
                // $progressCount = SikKinerjaSubTask::where('task_id', intval($getTask[$t]->id))->count();

                // if ($progressCount < 1) {
                //     $percentage = 0;
                // } else {
                //     $percentage = ($progressSum / ($progressCount * 2)) * 100;
                // }

                // New Calculation == 4 <<
                // Fetch the task_percentage column values from the database

                // Map the values of task_percentage to their respective percentages
                $mappedPercentages = $taskPercentages->map(function ($value) {
                    switch (intval($value)) {
                        // case 0:
                        //     return 0;
                        // case 1:
                        //     return 50;
                        // case 2:
                        //     return 100;
                        default:
                            return $value; // Handle unexpected values, though it's assumed there are none
                    }
                });

                // Calculate the overall percentage
                $percentage = $mappedPercentages->avg();
                // End of new calculation == 4 <<

                $getTask[$t]->percentage_counted = $percentage;
                $getTask[$t]->childTask = $getAllSubTask ? $getAllSubTask : [];
                $getTask[$t]->jabatan_level = $targetJabatan->jabatan_level;

                $payload2 = ["title" => $getTask[$t]->title, "percentage_counted" => $percentage];

                array_push($payload, $payload2);
            }

            // Counte Average of $payload (all task)
            $valueSum = 0;
            $valueLength = sizeof($payload);
            $allTaskAverage = 0;

            foreach ($payload as $value) {
                $valueSum += $value['percentage_counted'];
            }

            $allTaskAverage = sizeof($payload) > 0 ? ($valueSum / $valueLength) : 0;

            // Count KPI Value
            $finalKpiValue = (($arrAbsensi->hadir * 20) + ($arrAbsensi->terlambat * 5) + ($arrAbsensi->tidak_hadir * 5) + ($allTaskAverage * 70)) / 100;
            $kpiBerapaDuit = ($finalKpiValue * $targetBaseNominal) / 100;
            $statusPencapaian = 1;

            $categories = [
                [
                    'id' => 1,
                    'title' => "Sangat baik",
                    'valueTo' => 100,
                    'valueFrom' => 91,
                ],
                [
                    'id' => 2,
                    'title' => "Baik",
                    'valueTo' => 90,
                    'valueFrom' => 81,
                ],
                [
                    'id' => 3,
                    'title' => "Cukup baik",
                    'valueTo' => 80,
                    'valueFrom' => 71,
                ],
                [
                    'id' => 4,
                    'title' => "Kurang baik",
                    'valueTo' => 70,
                    'valueFrom' => 61,
                ],
                [
                    'id' => 5,
                    'title' => "Sangat tidak baik",
                    'valueTo' => 60,
                    'valueFrom' => 0,
                ],
            ];

            foreach ($categories as $category) {
                if ($finalKpiValue >= $category['valueFrom'] && $finalKpiValue <= $category['valueTo']) {
                    $statusPencapaian = $category['id'];
                    break; // Exit loop once the correct category is found
                }
            }
        }

        return response()->json([
            "success" => true,
            "taskPercentage" => $payload,
            "baseNominal" => $targetBaseNominal,
            "arrAbsensi" => $arrAbsensi,
            "finalKpiValue" => $finalKpiValue,
            "kpiBerapaDuit" => $kpiBerapaDuit,
            "taskAverage" => $allTaskAverage,
            "statusPencapaian" => $statusPencapaian,
            "debug" => [
                "task" => $getTask,
                "date" => [$startDate, $endDate],
                "assignedId" => $targetBiodataId
            ]
        ], 201);
    }

    // Get Only the Task Percentage Loop -/ TEST
    public function getTaskPercentageLoopList(Request $taskInfo)
    {
        $type = $taskInfo->type;

        if ($type === "dosen") {
            $allActiveUser = SikBiodata::where('active', 1)->whereIn('status', [2, 3, 4])->get();
        } else if ($type === "tendik") {
            $allActiveUser = SikBiodata::where('active', 1)->whereIn('status', [1, 5])->get();
        } else {
            return response()->json(["success" => false]);
        }

        // return response()->json($allActiveUser);

        $startDate = $taskInfo->startDate;
        $endDate = $taskInfo->endDate;

        $allData = [];

        for ($i = 0; $i < sizeof($allActiveUser); $i++) {
            $targetTempId = $allActiveUser[$i]->id;

            $tempData = getKpiAndNominal($startDate, $endDate, $targetTempId);

            array_push($allData, $tempData);
        }

        return response()->json(["allData" => $allData, "success" => true]);
    }

    // Get Only the Task Percentage Loop -/ TEST
    public function getTaskPercentageLoopTestUnused(Request $taskInfo)
    {
        // Temporary get by Active User
        // ID Users is different at ID Sik_Biodata Database
        // $userActiveIdTest = 16; // Test ID, manual
        $userActiveId = $taskInfo->targetId;
        $userActiveName = getActiveUserInfo()[2];

        // Retrive Biodata ID
        $targetBiodata = SikBiodata::where('master_id', intval($userActiveId))->first();
        $targetBiodataId = $targetBiodata->id;

        // Target User Base Nominal
        $targetBaseNominal = $targetBiodata->base_nominal_kpi;

        $startDate = $taskInfo->startDate;
        $endDate = $taskInfo->endDate;

        // Main Get Task Database Query without task that hasnt done or not 100%
        $getTask = SikKinerjaTask::where('assigned_biodata_id', $targetBiodataId)
            ->whereRaw("DATE(created_at) BETWEEN ? AND ?", [$startDate, $endDate])->get();

        // Extra Get Task Database Query WITH task that hasnt done or not 100%
        $getTask = SikKinerjaTask::where('assigned_biodata_id', $targetBiodataId)
            ->whereRaw("DATE(created_at) BETWEEN ? AND ?", [$startDate, $endDate])
            ->orWhere('assigned_biodata_id', $targetBiodataId)->where("progress_percentage", "<", 100)->get();

        $targetJabatan = SikJabatanStruktural::find(intval($targetBiodata->jabatan_struktural_id));

        $payload = [];
        $sumAllPercentage = 0;
        $taskAvailableLength = 0;

        if ($getTask) {
            for ($t = 0; $t < sizeof($getTask); $t++) {
                // Mimic the Task Initial Value in TaskList.jsx
                $getTask[$t]->assigne_name = $userActiveName;

                // Ambil Semua Sub Task
                $getAllSubTask = SikKinerjaSubTask::where('task_id', intval($getTask[$t]->id))->get();

                // $progressSum = SikKinerjaSubTask::where('task_id', intval($getTask[$t]->id))->sum('progress_int');
                // $progressCount = SikKinerjaSubTask::where('task_id', intval($getTask[$t]->id))->count();

                // Get absensi from active account
                $targetAbsensi = SikExtraBiodata::where('biodata_id', $targetBiodataId)->first();

                $rawAbsensi = $targetAbsensi->current_absensi_json;
                $arrAbsensi = json_decode($rawAbsensi);

                // if ($progressCount < 1) {
                //     $percentage = 0;
                // } else {
                //     $percentage = ($progressSum / ($progressCount * 2)) * 100;
                // }

                // New Calculation == 4 <<
                // Fetch the task_percentage column values from the database

                $taskPercentages = SikKinerjaSubTask::where('task_id', intval($getTask[$t]->id))->pluck('progress_int');

                // Map the values of task_percentage to their respective percentages
                $mappedPercentages = $taskPercentages->map(function ($value) {
                    switch (intval($value)) {
                        // case 0:
                        //     return 0;
                        // case 1:
                        //     return 50;
                        // case 2:
                        //     return 100;
                        default:
                            return $value; // Handle unexpected values, though it's assumed there are none
                    }
                });

                // Calculate the overall percentage
                $percentage = $mappedPercentages->avg();
                // End of new calculation == 4 <<

                $getTask[$t]->percentage_counted = $percentage;
                $getTask[$t]->childTask = $getAllSubTask ? $getAllSubTask : [];
                $getTask[$t]->jabatan_level = $targetJabatan->jabatan_level;

                $payload2 = ["title" => $getTask[$t]->title, "percentage_counted" => $percentage];

                $sumAllPercentage += $percentage;



                // $kpiResult = [$sumAllPercentage, $taskAvailableLength];

                array_push($payload, $payload2);
            }

            $sumAllPercentage = $sumAllPercentage + $arrAbsensi->hadir + $arrAbsensi->terlambat + $arrAbsensi->tidak_hadir;
            $taskAvailableLength = sizeof($getTask) + 3;
            $kpiResult = number_format($sumAllPercentage / $taskAvailableLength, 2);
            $nominalResult = (intval($targetBaseNominal) * floatval($kpiResult)) / 100;
        }

        return response()->json(["kpiValue" => $kpiResult, "nominalFromKpi" => $nominalResult]);
    }

    public function getNameAndBaseNominal(Request $req)
    {
        // $data = SikBiodata::where('active', 1)->select('id', 'fullname', 'nik', 'base_nominal_kpi')->get();

        $perPage = $req->input('perPage', 20); // Default items per page is 10
        $page = $req->input('page', 1); // Default page is 1
        // $search = $req->input('search'); // Search parameter from the request

        $query = SikBiodata::query(); // Start building the query

        // if ($search) {
        // Add a where clause to filter data based on a specific word or substring
        // $query->where('title', 'like', '%' . $search . '%');
        $query->where('active', 1)->select('id', 'fullname', 'nik', 'base_nominal_kpi');
        // You can add more where clauses for other columns as needed
        // }

        $dataQ = $query->paginate($perPage, ['*'], 'page', $page);
        $maxPage = $dataQ->lastPage();

        return response()->json(["table" => $dataQ, "maxPage" => $maxPage]);

        // return response()->json($data);
    }

    //
    public function getNameAndBaseNominalwSearch(Request $req)
    {
        // $data = SikBiodata::where('active', 1)->select('id', 'fullname', 'nik', 'base_nominal_kpi')->get();

        $perPage = $req->input('perPage', 20); // Default items per page is 10
        $page = $req->input('page', 1); // Default page is 1
        $search = $req->input('search'); // Search parameter from the request

        $query = SikBiodata::query(); // Start building the query

        // if ($search) {
        // Add a where clause to filter data based on a specific word or substring
        // $query->where('title', 'like', '%' . $search . '%');
        $query->where('active', 1)->where('fullname', 'like', '%' . $search . '%')->orWhere('nik', 'like', '%' . $search . '%')->select('id', 'fullname', 'nik', 'base_nominal_kpi');
        // You can add more where clauses for other columns as needed
        // }

        $dataQ = $query->paginate($perPage, ['*'], 'page', $page);
        $maxPage = $dataQ->lastPage();

        return response()->json(["table" => $dataQ, "maxPage" => $maxPage]);

        // return response()->json($data);
    }

    // Update Target USer Base Nominal
    public function updateTargetNominal(Request $req)
    {
        $targetId = intval($req->id);
        $newNominal = $req->nominal;
        $targetData = SikBiodata::find($targetId);

        $targetData->base_nominal_kpi = $newNominal;

        if ($targetData->save()) {
            return response()->json(["good" => true]);
        }
        return response()->json(["good" => false]);
    }

    public function getListKaryawan()
    {
        // Uncomment This When Deploy - wdeploy
        // $allTendik = SikBiodata::where('active', 1)->where('status', 1)->select('id', 'nik', 'fullname', 'unit_id', 'jabatan_struktural_id')->get();

        // Comment this when Deploy - wdeploy
        $allTendik = SikBiodata::where('active', 1)->select('id', 'nik', 'fullname', 'unit_id', 'jabatan_struktural_id')->get();

        // Uncomment this to get the unit name and jabatan struktural name -> START
        // for ($i = 0; $i < sizeof($allTendik); $i++) {
        //     $targetUnit = SikUnitKerja::find($allTendik[$i]->unit_id);
        //     $targetJabatan = SikJabatanStruktural::find($allTendik[$i]->jabatan_struktural_id);

        //     $allTendik[$i]->unit_name = $targetUnit->name;
        //     $allTendik[$i]->jabstruk_name = $targetJabatan->name;
        // }
        // -> END



        return response()->json($allTendik);
    }

    public function getTemplateExcel(?string $type = "dosen")
    {
        $templateType = $type;
        $filename = "Template Presensi KIP Dosen UNUSIDA.xlsx";

        if ($templateType === "dosen") {
            $availableUsers = SikBiodata::where('active', 1)->whereIn('status', [2, 3, 4])->get();
        } else if ($templateType === "tendik") {
            $filename = "Template Presensi KIP Tendik UNUSIDA.xlsx";
            $availableUsers = SikBiodata::where('active', 1)->whereIn('status', [1, 5])->get();
        } else {
            return "Template Not Found";
        }

        $allAbsensi = [];

        // for ($i = 0; $i < sizeof($availableUser); $i++) {

        //     // Ambil Nama
        //     $name = $availableUser[$i]->fullname;
        //     $nik = $availableUser[$i]->nik;

        //     $no_urut = $i + 1;

        //     $targetExtra = SikExtraBiodata::where('biodata_id', $availableUser[$i]->id)->first();

        //     // Raw Absensi
        //     $absensi = json_decode($targetExtra->current_absensi_json);

        //     $hadir = $absensi->hadir;
        //     $terlambat = $absensi->terlambat;
        //     $tidak_hadir = $absensi->tidak_hadir;

        //     $retPayload = ["rn_no" => $no_urut, "rn_nama" => $name, "rn_nik" => $nik, "rn_abs_hadir" => $hadir, "rn_abs_terlambat" => $terlambat, "rn_abs_tidak_hadir" => $tidak_hadir,];
        //     array_push($allAbsensi, $retPayload);
        // }

        foreach ($availableUsers as $index => $availableUser) {
            $name = $availableUser->fullname;
            $nik = $availableUser->nik;

            // Assuming $index starts from 1
            $no_urut = $index + 1;

            $targetExtra = SikExtraBiodata::where('biodata_id', $availableUser->id)->first();

            // Raw Absensi
            $absensi = json_decode($targetExtra->current_absensi_json);

            $hadir = $absensi->hadir;
            $terlambat = $absensi->terlambat;
            $tidak_hadir = $absensi->tidak_hadir;

            // Payload with absensi value
            // $retPayload = [
            //     "rn_no" => $no_urut,
            //     "rn_nama" => $name,
            //     "rn_nik" => '\'' . $nik,
            //     "rn_abs_hadir" => $hadir,
            //     "rn_abs_terlambat" => $terlambat,
            //     "rn_abs_tidak_hadir" => $tidak_hadir,
            // ];

            // Payload with 0
            $retPayload = [
                "rn_no" => $no_urut,
                "rn_nama" => $name,
                "rn_nik" => '\'' . $nik,
                "rn_abs_hadir" => 0,
                "rn_abs_terlambat" => 0,
                "rn_abs_tidak_hadir" => 0,
            ];
            $allAbsensi[] = $retPayload;
        }

        // return response()->json($allAbsensi);
        return Excel::download(new ExcelAbsensiExport($allAbsensi), $filename);
    }

    public function downloadKpiSummary($type = "dosen", $periodeStart, $periodeEnd)
    {
        if (!$periodeStart) {
            return "Format URL Tidak Diterima !!!!";
        }
        // $peri
        $filename = "Ringkasan KPI";

        if ($type === "dosen") {
            $filename .= " Dosen";
            $allActiveUser = SikBiodata::where('active', 1)->whereIn('status', [2, 3, 4])->get();
        } else if ($type === "tendik") {
            $filename .= " Tendik";
            $allActiveUser = SikBiodata::where('active', 1)->whereIn('status', [1, 5])->get();
        } else {
            return "Template Not Found";
        }

        // $type = $taskInfo->type;

        // if ($type === "dosen") {
        //     $allActiveUser = SikBiodata::where('active', 1)->whereIn('status', [2, 3, 4])->get();
        // } else if ($type === "tendik") {
        //     $allActiveUser = SikBiodata::where('active', 1)->whereIn('status', [1, 5])->get();
        // } else {
        //     return response()->json(["success" => false]);
        // }

        $filename .= " UNUSIDA {$this->sanitizeString($periodeStart)} {$this->sanitizeString($periodeEnd)}.xlsx";

        // return response()->json($allActiveUser);

        // $startDate = calculatePeriod()[0];
        // $endDate = calculatePeriod()[1];

        $startDate = $periodeStart;
        $endDate = $periodeEnd;

        $allData = [];

        for ($i = 0; $i < sizeof($allActiveUser); $i++) {
            $targetTempId = $allActiveUser[$i]->id;

            $tempData = getKpiAndNominal($startDate, $endDate, $targetTempId);

            array_push($allData, $tempData);
        }

        // return response()->json($allData);

        foreach ($allData as $index => $datakpi) {
            $name = $datakpi['fullname'];
            $nik = $datakpi['nik'];
            $kpi = $datakpi['kpiValue'];
            $nominal = $datakpi['nominalFromKpi'];
            $base = $datakpi['baseNominal'];

            // Assuming $index starts from 1
            $no_urut = $index + 1;

            // Payload with 0
            $retPayload = [
                'rn_no' => $no_urut,
                'rn_nama' => $name,
                'rn_nik' => '\'' . $nik,
                'rn_base_nominal' => $base,
                'rn_nilai_kpi' => $kpi,
                'rn_nominal' => str_replace(",", "", $nominal),
            ];

            $allAbsensi[] = $retPayload;
        }

        // return response()->json($allAbsensi);
        return Excel::download(new KpiSummary($allAbsensi), $filename);
    }

    public function getJabatanBaseNominal(Request $req)
    {
        $validated = $req->validate([
            "by" => "required|in:@rnamikaze"
        ]);

        $allJabatanBase = SikBaseKpi::all();
        // $allJabatanBase = JabatanBaseNominalModels::where('active', 1)->select('name_alias', 'jabatan_level', 'base_nominal_kpi', 'id')->get();

        return response()->json($allJabatanBase);
    }

    public function updateTargetJabatanBaseNominal(Request $target)
    {
        $validated = $target->validate([
            "by" => "required|in:@rnamikaze",
            "id" => "required|numeric|min_digits:1",
            "base" => "required|numeric|min_digits:4"
        ]);

        $targetId = intval($target->id);
        $targetNominal = intval($target->base);

        $targetJabatan = SikBaseKpi::find($targetId);

        $targetJabatan->base = $targetNominal;

        if ($targetJabatan->save()) {
            $allJabatanBase = SikBaseKpi::all();

            return response()->json(["baseNominal" => $allJabatanBase, "good" => true]);
        }
        return response()->json(['good' => false]);
    }

    public function getUserKpi(Request $data)
    {
        $payload = getKpiAndNominal($data->startDate, $data->endDate, $data->id);

        return response()->json($payload);
    }
}
