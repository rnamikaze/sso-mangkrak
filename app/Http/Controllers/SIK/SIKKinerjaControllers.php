<?php

namespace App\Http\Controllers\SIK;

use App\Http\Controllers\Controller;
use App\Models\SIK\SikBiodata;
use App\Models\SIK\SikFakultas;
use App\Models\SIK\SikJabatanStruktural;
use App\Models\SIK\SikKinerjaDosenTask;
use App\Models\SIK\SikKinerjaSubDosenTask;
use App\Models\SIK\SikKinerjaSubTask;
use App\Models\SIK\SikKinerjaTask;
use App\Models\SIK\SikUnitKerja;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

$rawActiveBio = '
            {
                "id": 161,
                "created_at": "2024-03-07T01:16:08.000000Z",
                "updated_at": "2024-03-13T04:52:46.000000Z",
                "nik": "17081945",
                "nik_ktp": "",
                "master_id": 468,
                "fullname": "superadmin",
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

function checkTaskLate($firstDate, $secondDate)
{
    // Parse the first date
    $firstDate = Carbon::parse($firstDate);

    // Parse the second date and set the day to 19, then increment by one month
    $endPeriode = Carbon::parse($secondDate)->addMonth()->day(19);

    // Compare the first date with the modified second date
    $result = $firstDate->greaterThan($endPeriode);

    // Return the result as a boolean
    return $result;
}

function checkTaskLateNow($secondDate)
{
    // Parse the first date
    // $firstDate = Carbon::parse('2024-11-11');
    $firstDate = Carbon::now();

    // Parse the second date and set the day to 19, then increment by one month
    $endPeriode = Carbon::parse($secondDate)->addMonth()->day(19);

    // Compare the first date with the modified second date
    $result = $firstDate->greaterThan($endPeriode);

    // Return the result as a boolean
    return $result;
}

function getCurrentMontYear($formated = false, $custom = null)
{
    $inputDate = Carbon::now();
    if ($custom !== null) {
        // Input date in "YYYY-MM-DD" format
        // $inputDate = "2023-07-22";

        // Parse the input date using Carbon
        $inputDate = Carbon::createFromFormat('Y-m-d', $custom);

        // Get the month from the custom date
        // $month = $customDate->month
    }

    $month = "0";
    if ($formated) {
        $month = $inputDate->month < 10 ? "0" . $inputDate->month : $inputDate->month;
    } else {
        $month = $inputDate->month;
    }
    $year = $inputDate->year;

    return [$month, $year];
}

function getPeriodeDate($custom = null)
{
    // Periode Assigner
    $inputDate = Carbon::now();

    $month = getCurrentMontYear(true)[0];
    $year = getCurrentMontYear()[1];

    if ($custom !== null) {
        $month = getCurrentMontYear(true, $custom)[0];
        $year = getCurrentMontYear(false, $custom)[1];
    }

    $currentDay = $inputDate->day;

    $periodeStart = Carbon::parse($year . "-" . $month . "-20");

    $initialDate = Carbon::parse($year . "-" . $month . "-19");

    $nextPeriode = $initialDate->copy()->addMonth();

    if ($currentDay < 20) {
        $buff = Carbon::parse($year . "-" . $month . "-19");
        $periodeStart = $buff->copy()->subMonth();

        // $initialDate = Carbon::parse($year . "-" . $month . "-19");

        $nextPeriode = Carbon::parse($year . "-" . $month . "-20");
    }

    return [$periodeStart, $nextPeriode];
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

function isDateInRange($startDate, $endDate, $inputDate)
{
    // Convert the dates to Carbon instances for easy comparison
    $start = \Carbon\Carbon::parse($startDate);
    $end = \Carbon\Carbon::parse($endDate);
    $input = \Carbon\Carbon::parse($inputDate);

    // Check if the input date is within the range
    return $input->between($start, $end);
}


class SIKKinerjaControllers extends Controller
{
    //
    public function updatePeriode(Request $data)
    {
        $taskId = $data->task_id;

        $targetDosenTask = SikKinerjaTask::find($taskId);
        $targetDosenSubTask = SikKinerjaSubTask::where('task_id', $taskId)->get();

        $currentDateTime = Carbon::now();
        // Format the current date and time to match the original format
        $formattedCurrentDateTime = $currentDateTime->format('Y-m-d\TH:i:s.u\Z');

        $startPeriode = calculatePeriod()[0];
        $endPeriode = calculatePeriod()[1];

        if ($targetDosenTask) {
            $targetDosenTask->created_at = $formattedCurrentDateTime;
            $targetDosenTask->periode_start = $startPeriode;
            $targetDosenTask->due_date = $endPeriode;

            for ($i = 0; $i < sizeof($targetDosenSubTask); $i++) {
                $targetDosenSubTask[$i]->created_at = $formattedCurrentDateTime;
                $targetDosenSubTask[$i]->periode_start = $startPeriode;
                $targetDosenSubTask[$i]->due_date = $endPeriode;

                $targetDosenSubTask[$i]->save();
            }

            $targetDosenTask->save();

            return response()->json(["good" => true]);
        }

        return response()->json(["good" => false]);
    }

    public function homeKinerja()
    {
        // $activeId = Auth::id();

        // $getBiodata = SikBiodata::where('master_id', intval($activeId))->first();

        // $getStrukturalInfo = SikJabatanStruktural::find(intval($getBiodata->jabatan_struktural_id));

        // $getListNames = SikBiodata::where('unit_id', intval($getBiodata->unit_id))->where('active', 1)->select('id', 'master_id', 'fullname', 'jabatan_struktural_id', 'unit_id')->get();

        $activeBio = SikBiodata::where('master_id', Auth::id())->first();

        if ($activeBio === null) {
            global $rawActiveBio;

            $activeBio = json_decode($rawActiveBio);
        } else {
            $getTargetStruktural = SikJabatanStruktural::find(intval($activeBio->jabatan_struktural_id));

            $activeBio->nama_jabatan = $getTargetStruktural->name;
            $activeBio->jabatan_level = $getTargetStruktural->jabatan_level;
        }

        // return response()->json($activeBio);

        // return $getBiodata;

        return Inertia::render('SimpegUnusida/SIKMain', [
            // 'dataDash' => $dashPayload,
            'viewId' => [2, 0],
            'activeBio' => $activeBio,
        ]);
    }

    public function getAssigneName(Request $unit)
    {
        $activeId = Auth::id();
        $unitId = intval($unit->id);
        $struktural_id = intval($unit->struktural_id);

        // return response()->json($unit);

        // Model Unit Kerja -> SikUnitKerja() -> unit_id dari user
        // Model Struktural -> SikJabatanStruktural() -> unit_id dari user
        // Ambil semua user berdasrkan unit_id
        // Cek level jabatan struktral
        // Ambil level dari sama dengan dan kebawah

        // $selectedUnit = SikUnitKerja::where('id', $unitId)->first();
        $selectedStruktural = SikJabatanStruktural::where('id', $struktural_id)->first();

        $currentJabatanLevel = intval($selectedStruktural->jabatan_level);
        $currentDivisi = $selectedStruktural->divisi;

        // $currentJabatanLevel = 4;

        $allowedAssigneLevel = [$currentJabatanLevel];

        $assigneList = null;
        $assigneUnitIdList = [];

        if ($currentJabatanLevel === 2) {
            $assigneList = SikJabatanStruktural::where('unit_id', $unitId)
                ->where(function ($query) {
                    $query->where('jabatan_level', 2)
                        ->orWhere('jabatan_level', 3)
                        ->orWhere('jabatan_level', 4);
                })
                ->get();
        }
        if ($currentJabatanLevel === 3) {
            $assigneList = SikJabatanStruktural::where('unit_id', $unitId)
                ->where(function ($query) {
                    $query->where('jabatan_level', 3)
                        ->orWhere('jabatan_level', 4);
                })
                ->where('divisi', $currentDivisi)
                ->get();
        }

        if ($currentJabatanLevel === 4) {
            // $assigneList = SikJabatanStruktural::where('unit_id', $unitId)
            //     ->where('jabatan_level', 4)
            //     ->get();
            $assigneList = [SikJabatanStruktural::where('id', $struktural_id)->first()];
        }

        // Only for Debug
        // return response()->json($assigneList);

        // for ($i = 0; $i < sizeof($assigneList); $i++) {
        //     array_push($assigneUnitIdList, $assigneList[$i]->id);
        // }

        // If active user jabatan level is 4, just get his/her task instead
        if ($currentJabatanLevel === 4) {
            $realAssigneList = [SikBiodata::find(intval($unit->userId))];
        } else {
            $targetBio = SikBiodata::where('master_id', $activeId)->first();

            if (intval($targetBio->status) === 5) {
                $realAssigneList = [SikBiodata::where('master_id', $activeId)->first()];
            } else {
                for ($i = 0; $i < sizeof($assigneList); $i++) {
                    array_push($assigneUnitIdList, $assigneList[$i]->id);
                }

                $realAssigneList = SikBiodata::whereIn('jabatan_struktural_id', $assigneUnitIdList)->get();
            }
        }

        // else {
        //     $realAssigneList = SikBiodata::whereIn('jabatan_struktural_id', $assigneUnitIdList)->get();
        // }

        // return response()->json($unit->userId);

        $assigneUserId = [];

        for ($i = 0; $i < sizeof($realAssigneList); $i++) {
            // if (intval($realAssigneList[$i]->id) !== $activeId) {
            //     array_push($assigneUserId, $realAssigneList[$i]->id);
            // }
            array_push($assigneUserId, [$realAssigneList[$i]->id, $realAssigneList[$i]->fullname]);
        }

        // $getActiveUserTask = SikKinerjaTask::where('assigned_biodata_id', $activeId)->get();

        $arrayUserTask = [];

        for ($i = 0; $i < sizeof($assigneUserId); $i++) {

            // Define the Periode Date
            $startPeriode = calculatePeriod()[0];
            $endPeriode = calculatePeriod()[1];
            if ($unit->has('customDate')) {
                // Define the Periode Date
                $startPeriode = calculatePeriod($unit->customDate)[0];
                $endPeriode = calculatePeriod($unit->customDate)[1];
            }

            // Get The Data within Periode Date
            // $getTask = SikKinerjaTask::where('assigned_biodata_id', $assigneUserId[$i][0])->where('progress_percentage', '<', 100)
            //     ->whereBetween('created_at', [$startPeriode, $endPeriode])->get();

            // $getTask = SikKinerjaTask::where('assigned_biodata_id', $assigneUserId[$i][0])->where('progress_percentage', '<', 100)
            //     ->orWhere('created_at', [$startPeriode, $endPeriode])->get();

            $buffAssigneUnitId = $assigneUserId[$i][0];

            $getTask = SikKinerjaTask::where(function ($query) use ($buffAssigneUnitId, $startPeriode, $endPeriode) {
                $query->where('assigned_biodata_id', $buffAssigneUnitId)
                    ->whereRaw("DATE(created_at) BETWEEN ? AND ?", [$startPeriode, $endPeriode]);
            })
                ->orWhere(function ($query) use ($buffAssigneUnitId) {
                    $query->where('assigned_biodata_id', $buffAssigneUnitId)
                        ->where('progress_percentage', '<', 100);
                })
                ->get();

            $targetBiodata = SikBiodata::find(intval($assigneUserId[$i][0]));

            $targetJabatan = SikJabatanStruktural::find(intval($targetBiodata->jabatan_struktural_id));

            if ($getTask) {
                for ($t = 0; $t < sizeof($getTask); $t++) {
                    // Mimic the Task Initial Value in TaskList.jsx
                    $getTask[$t]->assigne_name = $assigneUserId[$i][1];

                    // Ambil Semua Sub Task
                    $getAllSubTask = SikKinerjaSubTask::where('task_id', intval($getTask[$t]->id))->get();

                    // Get the latest due date from Sub Task
                    $getFirstLatestDate = SikKinerjaSubTask::where('task_id', intval($getTask[$t]->id))->latest('due_date')->first();

                    if ($getFirstLatestDate) {
                        $getTask[$t]->latest_date = $getFirstLatestDate->due_date;
                    } else {
                        $getTask[$t]->latest_date = Carbon::now()->format("Y-m-d");
                    }

                    $getTask[$t]->childTask = $getAllSubTask ? $getAllSubTask : [];
                    $getTask[$t]->jabatan_level = $targetJabatan->jabatan_level;
                    $getTask[$t]->progress_percentage = round($getTask[$t]->progress_percentage, 2);

                    $getTask[$t]->is_late = false;

                    if (checkTaskLateNow($getTask[$t]->periode_start)) {
                        $getTask[$t]->is_late = true;
                    }
                }
            }

            array_push($arrayUserTask, [$assigneUserId[$i][0] => $getTask]);
        }
        // array_push($arrayUserTask, [$activeId => $getActiveUserTask]);

        $sendPayload = [
            "assigneList" => $realAssigneList,
            "userTask" => $arrayUserTask,
            "periode" => [$startPeriode, $endPeriode],
            "debug" => $assigneUserId
        ];

        return response()->json($sendPayload);
    }

    public function createNewTask(Request $task)
    {
        // return response()->json($task);

        $createNewTask = new SikKinerjaTask;

        $createNewTask->title = $task->title;
        $createNewTask->due_date = $task->due_date;
        // $createNewTask->realize_date = $task->realize_date;
        $createNewTask->assigned_biodata_id = intval($task->assigned_biodata_id);
        // $createNewTask->progress_percentage = $task->progress_percentage;

        $createNewTask->comment = $task->comment;
        $createNewTask->assigner_level = intval($task->assigner_level);

        // Periode Assigner
        $createNewTask->periode_start = strval(getPeriodeDate()[0]);

        $good = $createNewTask->save();

        // $buff = json_decode($task->task);
        if ($good) {
            return response()->json(['good' => true]);
        }
        return response()->json(['good' => false]);
    }

    public function deleteTask(Request $id)
    {
        $doDelete = SikKinerjaTask::destroy(intval($id->id));

        if ($doDelete === 0) {
            return response()->json(["deleteSuccess" => false]);
        }
        return response()->json(["deleteSuccess" => true]);
    }

    public function createSubTask(Request $payload)
    {
        // return response()->json($payload);

        $newSubTask = new SikKinerjaSubTask;

        $newSubTask->title = $payload->title;
        $newSubTask->task_id = $payload->taskId;
        $newSubTask->progress_int = $payload->progress_int;
        $newSubTask->due_date = $payload->due_date;
        $newSubTask->comment = $payload->comment;
        $newSubTask->assigner_level = intval($payload->assigner_level);


        $idList = [$payload->assigne];
        $newSubTask->collab_list_biodata_id = json_encode($idList);

        $newSubTask->periode_start = strval(getPeriodeDate()[0]);

        $good = $newSubTask->save();

        $targetTask = SikKinerjaTask::find(intval($payload->taskId));

        $subTaskListId = $targetTask->sub_task_list_id;

        if ($subTaskListId === null) {
            $subTaskListId = [];

            array_push($subTaskListId, $newSubTask->id);

            $targetTask->sub_task_list_id = json_encode($subTaskListId);
        } else {
            $arrayBuff = json_decode($subTaskListId);

            array_push($arrayBuff, $newSubTask->id);
            $targetTask->sub_task_list_id = json_encode($arrayBuff);
        }

        $targetTask->progress_percentage = $payload->progress_percentage;
        $targetTask->save();

        if ($good) {
            // $progressSum = SikKinerjaSubTask::where('task_id', intval($payload->taskId))->sum('progress_int');
            // $progressCount = SikKinerjaSubTask::where('task_id', intval($payload->taskId))->count();

            // New Calculation == 4 <<
            // Fetch the task_percentage column values from the database

            $taskPercentages = SikKinerjaSubTask::where('task_id', intval($payload->taskId))->pluck('progress_int');

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

            // Assign the sum to a variable
            // $percentage = ($progressSum / ($progressCount * 2)) * 100;
            // return response()->json($percentage);
            // $targetTask = SikKinerjaTask::find(intval($payload->taskId));
            $targetTask->progress_percentage = $percentage;
            $targetTask->realize_date = null;

            if (intval($percentage) >= 100) {
                $targetTask->realize_date = now('Asia/Jakarta');
            }

            $goodAgain = $targetTask->save();

            if ($goodAgain) {
                return response()->json(['good' => true]);
            }

            return response()->json(['good' => false]);
        }

        return response()->json(['good' => false]);
        // return response()->json($payload->subTask);
    }

    public function updateSubTask(Request $subTask)
    {
        // return response()->json($subTask);
        /*
        id: 0,
        title: "Tunggu",
        assigne: "",
        userId: 0,
        progress_int: 1,
        progress_percentage: 0,
        comment: "",
        start_date: "",
        end_date: "",
        active: false,
        */

        $targetSubTask = SikKinerjaSubTask::find(intval($subTask->id));

        $targetSubTask->title = $subTask->title;
        $targetSubTask->progress_int = intval($subTask->progress_int);
        $targetSubTask->due_date = $subTask->due_date;
        $targetSubTask->comment = $subTask->comment;

        if (intval($targetSubTask->progress_int) === 2) {
            $targetSubTask->realize_date = now();
        } else {
            $targetSubTask->realize_date = null;
        }

        $good = $targetSubTask->save();

        if ($good) {
            // $progressSum = SikKinerjaSubTask::where('task_id', intval($subTask->task_id))->sum('progress_int');
            // $progressCount = SikKinerjaSubTask::where('task_id', intval($subTask->task_id))->count();

            // Assign the sum to a variable
            // $percentage = ($progressSum / ($progressCount * 2)) * 100;

            // New Calculation == 4 <<
            // Fetch the task_percentage column values from the database

            $taskPercentages = SikKinerjaSubTask::where('task_id', intval($subTask->task_id))->pluck('progress_int');

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

            $targetTask = SikKinerjaTask::find(intval($subTask->task_id));
            $targetTask->progress_percentage = $percentage;
            $targetTask->realize_date = null;

            if (intval($percentage) >= 100) {
                $targetTask->realize_date = now('Asia/Jakarta');
            }

            $goodAgain = $targetTask->save();

            if ($goodAgain) {
                return response()->json(['good' => true]);
            }

            return response()->json(['good' => false]);
        }

        return response()->json(['good' => false]);
    }

    public function deleteSubTask(Request $subTaskId)
    {
        // return response()->json($subTaskId);
        $good = SikKinerjaSubTask::destroy(intval($subTaskId->id));
        // $good = 1;

        if ($good > 0) {
            // $progressSum = SikKinerjaSubTask::where('task_id', intval($subTaskId->taskId))->sum('progress_int');
            // $progressCount = SikKinerjaSubTask::where('task_id', intval($subTaskId->taskId))->count();

            // return response()->json([
            //     $progressSum,
            //     $progressCount,
            //     $subTaskId->taskId
            // ]);
            // Assign the sum to a variable
            // if ($progressCount < 1) {
            //     $percentage = 0;
            // } else {
            //     $percentage = ($progressSum / ($progressCount * 2)) * 100;
            // }

            // New Calculation == 4 <<
            // Fetch the task_percentage column values from the database

            $taskPercentages = SikKinerjaSubTask::where('task_id', intval($subTaskId->taskId))->pluck('progress_int');

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

            $targetTask = SikKinerjaTask::find(intval($subTaskId->taskId));
            $targetTask->progress_percentage = $percentage;
            $targetTask->realize_date = null;

            if (intval($percentage) >= 100) {
                $targetTask->realize_date = now('Asia/Jakarta');
            }

            $goodAgain = $targetTask->save();

            if ($goodAgain) {
                return response()->json(["good" => true]);
            }
            return response()->json(['good' => false]);
            // return response()->json($targetTask);
        }

        return response()->json(['good' => false]);
    }

    public function getUserAdditionalInfo(Request $payload)
    {
        $targetStruktural = SikJabatanStruktural::find(intval($payload->struktural_id));

        $targetJabatanLevel = $targetStruktural->jabatan_level;

        return response()->json(['jabatan_level' => $targetJabatanLevel]);
    }

    public function getTaskHistory(Request $unit)
    {
        $activeId = Auth::id();
        $unitId = intval($unit->id);
        $struktural_id = intval($unit->struktural_id);

        // Use frontend start and end dates
        $startPeriode = Carbon::parse($unit->startDate)->format('Y-m-d');
        $endPeriode = Carbon::parse($unit->endDate)->format('Y-m-d');
        // $currentDate = Carbon::now()->format('Y-m-d');

        $isCurrentPeriode = Carbon::now()->between($startPeriode, $endPeriode);
        // Check if the current date is within the selected period
        // $isCurrentPeriode = isDateInRange($startPeriode, $endPeriode, $currentDate);

        // Fetch struktural details
        $selectedStruktural = SikJabatanStruktural::find($struktural_id);
        $currentJabatanLevel = $selectedStruktural->jabatan_level;
        $currentDivisi = $selectedStruktural->divisi;

        // Get the list of assignees based on level and unit
        $assigneQuery = SikJabatanStruktural::where('unit_id', $unitId);

        if ($currentJabatanLevel == 2) {
            $assigneQuery->whereIn('jabatan_level', [2, 3, 4]);
        } elseif ($currentJabatanLevel == 3) {
            $assigneQuery->whereIn('jabatan_level', [3, 4])->where('divisi', $currentDivisi);
        } elseif ($currentJabatanLevel == 4) {
            $assigneList = collect([SikJabatanStruktural::find($struktural_id)]); // Ensure it's a collection
        }
        $assigneList = $assigneQuery->get();

        $assigneUnitIdList = $assigneList->pluck('id')->toArray();

        // Fetch assignee biodata
        if ($currentJabatanLevel === 4) {
            $realAssigneList = collect([SikBiodata::find(intval($unit->userId))]); // Ensure it's a collection
        } else {
            $targetBio = SikBiodata::where('master_id', $activeId)->first();
            if ($targetBio && $targetBio->status == 5) {
                $realAssigneList = collect([$targetBio]);
            } else {
                $realAssigneList = SikBiodata::whereIn('jabatan_struktural_id', $assigneUnitIdList)->get();
            }
        }

        // Ensure $realAssigneList is a collection before calling map()
        $assigneUserId = collect($realAssigneList)->map(fn($bio) => [$bio->id, $bio->fullname])->toArray();

        // $isHere = false;
        // Fetch tasks efficiently
        $arrayUserTask = [];
        foreach ($assigneUserId as [$bioId, $bioName]) {
            $tasks = null;

            if ($isCurrentPeriode) {
                $tasks = SikKinerjaTask::where(function ($query) use ($startPeriode, $endPeriode, $bioId) {
                    $query->where('assigned_biodata_id', $bioId)->where('progress_percentage', '<', 100)
                        ->whereBetween('created_at', ["2024-01-01", $endPeriode]);
                })->orWhere(function ($query) use ($startPeriode, $endPeriode, $bioId) {
                    $query->where('assigned_biodata_id', $bioId)->where('progress_percentage', 100)
                        ->whereBetween('created_at', [$startPeriode, $endPeriode]);
                })->get();
            } else {
                // $isHere = true;
                $tasks = SikKinerjaTask::where('assigned_biodata_id', $bioId)->where('progress_percentage', 100)->whereBetween('realize_date', [$startPeriode, $endPeriode])->get();
            }
            if ($tasks->isNotEmpty()) {
                $targetJabatan = SikJabatanStruktural::find(optional(SikBiodata::find($bioId))->jabatan_struktural_id);

                foreach ($tasks as $task) {
                    $task->assigne_name = $bioName;
                    $task->childTask = SikKinerjaSubTask::where('task_id', $task->id)->get();
                    $task->jabatan_level = optional($targetJabatan)->jabatan_level;
                }
            }

            $arrayUserTask[] = [$bioId => $tasks];
        }

        // Prepare response payload
        return response()->json([
            "userTask" => $arrayUserTask,
            "period" => [$startPeriode, $endPeriode],
            "assigneList" => $realAssigneList,
            // "debug" => [$isHere]
        ]);
    }


    public function getUnitList(Request $payload)
    {
        $allStructural = SikUnitKerja::whereNotIn('name', ['rektorat'])->select('id', 'name')->get();
        $allFakultas = SikFakultas::where('active', 1)->get();

        return response()->json(["unitList" => $allStructural, "fakultasList" => $allFakultas]);
    }

    public function getTaskByUnit(Request $unit)
    {
        $allUserByUnit = SikBiodata::where('unit_id', intval($unit->id))->get();

        // return response()->json($allUserByUnit);

        $assigneUnitIdList = [];

        for ($i = 0; $i < sizeof($allUserByUnit); $i++) {
            array_push($assigneUnitIdList, $allUserByUnit[$i]->id);
        }


        $realAssigneList = SikBiodata::whereIn('id', $assigneUnitIdList)->get();


        // return response()->json($realAssigneList);

        $assigneUserId = [];

        for ($i = 0; $i < sizeof($realAssigneList); $i++) {
            // if (intval($realAssigneList[$i]->id) !== $activeId) {
            //     array_push($assigneUserId, $realAssigneList[$i]->id);
            // }
            array_push($assigneUserId, [$realAssigneList[$i]->id, $realAssigneList[$i]->fullname]);
        }

        // return response()->json($assigneUserId);

        // $getActiveUserTask = SikKinerjaTask::where('assigned_biodata_id', $activeId)->get();

        $arrayUserTask = [];

        $startPeriode = Carbon::now()->format('Y-m-d');

        for ($i = 0; $i < sizeof($assigneUserId); $i++) {

            $getTask = SikKinerjaTask::where('assigned_biodata_id', $assigneUserId[$i][0])
                ->whereRaw("DATE(created_at) BETWEEN ? AND ?", ["2014-01-01", $startPeriode])->get();

            $targetBiodata = SikBiodata::find(intval($assigneUserId[$i][0]));

            $targetJabatan = SikJabatanStruktural::find(intval($targetBiodata->jabatan_struktural_id));

            if ($getTask) {
                for ($t = 0; $t < sizeof($getTask); $t++) {
                    // Mimic the Task Initial Value in TaskList.jsx
                    $getTask[$t]->assigne_name = $assigneUserId[$i][1];

                    // Ambil Semua Sub Task
                    $getAllSubTask = SikKinerjaSubTask::where('task_id', intval($getTask[$t]->id))->get();

                    $getTask[$t]->childTask = $getAllSubTask ? $getAllSubTask : [];
                    $getTask[$t]->jabatan_level = $targetJabatan->jabatan_level;
                }
            }

            array_push($arrayUserTask, [$assigneUserId[$i][0] => $getTask]);
        }
        // array_push($arrayUserTask, [$activeId => $getActiveUserTask]);

        $sendPayload = ["userTask" => $arrayUserTask, "period" => ["2014-01-01", $startPeriode], "listAssigne" => $allUserByUnit];

        return response()->json($sendPayload);
    }

    public function getTaskByUnitPeriode(Request $unit)
    {
        $allUserByUnit = SikBiodata::where('unit_id', intval($unit->id))->get();

        $startDate = $unit->startDate;
        $endDate = $unit->endDate;

        // return response()->json($allUserByUnit);

        $assigneUnitIdList = [];

        for ($i = 0; $i < sizeof($allUserByUnit); $i++) {
            array_push($assigneUnitIdList, $allUserByUnit[$i]->id);
        }


        $realAssigneList = SikBiodata::whereIn('id', $assigneUnitIdList)->get();


        // return response()->json($realAssigneList);

        $assigneUserId = [];

        for ($i = 0; $i < sizeof($realAssigneList); $i++) {
            // if (intval($realAssigneList[$i]->id) !== $activeId) {
            //     array_push($assigneUserId, $realAssigneList[$i]->id);
            // }
            array_push($assigneUserId, [$realAssigneList[$i]->id, $realAssigneList[$i]->fullname]);
        }

        // return response()->json($assigneUserId);

        // $getActiveUserTask = SikKinerjaTask::where('assigned_biodata_id', $activeId)->get();

        $arrayUserTask = [];

        $startPeriode = Carbon::now()->format('Y-m-d');

        for ($i = 0; $i < sizeof($assigneUserId); $i++) {

            $getTask = SikKinerjaTask::where('assigned_biodata_id', $assigneUserId[$i][0])
                ->whereRaw("DATE(created_at) BETWEEN ? AND ?", [$startDate, $endDate])->get();

            $targetBiodata = SikBiodata::find(intval($assigneUserId[$i][0]));

            $targetJabatan = SikJabatanStruktural::find(intval($targetBiodata->jabatan_struktural_id));

            if ($getTask) {
                for ($t = 0; $t < sizeof($getTask); $t++) {
                    // Mimic the Task Initial Value in TaskList.jsx
                    $getTask[$t]->assigne_name = $assigneUserId[$i][1];

                    // Ambil Semua Sub Task
                    $getAllSubTask = SikKinerjaSubTask::where('task_id', intval($getTask[$t]->id))->get();

                    $getTask[$t]->childTask = $getAllSubTask ? $getAllSubTask : [];
                    $getTask[$t]->jabatan_level = $targetJabatan->jabatan_level;
                }
            }

            array_push($arrayUserTask, [$assigneUserId[$i][0] => $getTask]);
        }
        // array_push($arrayUserTask, [$activeId => $getActiveUserTask]);

        $sendPayload = ["userTask" => $arrayUserTask, "period" => [$startDate, $endDate], "listAssigne" => $allUserByUnit];

        return response()->json($sendPayload);
    }

    public function getDemoStatus()
    {
        $demoStatus = env("APP_DEMO", false);

        return response()->json(['demoStatus' => $demoStatus]);
    }

    public function getDashSummary(Request $dashData)
    {
        $userId = intval($dashData->id);

        $startPeriode = calculatePeriod()[0];
        $endPeriode = calculatePeriod()[1];

        // $totalTask = SikKinerjaTask::where("assigned_biodata_id", $userId)
        //     ->whereRaw("DATE(created_at) BETWEEN ? AND ?", [$startPeriode, $endPeriode])
        //     ->where('progress_percentage', 100)->count();

        $totalTaskUndone = SikKinerjaTask::where("assigned_biodata_id", $userId)
            ->whereNot('progress_percentage', 100)->count();

        $allTaskCount = SikKinerjaTask::where(function ($query) use ($userId, $startPeriode, $endPeriode) {
            $query->where('assigned_biodata_id', $userId)
                ->whereRaw("DATE(created_at) BETWEEN ? AND ?", [$startPeriode, $endPeriode]);
        })
            ->orWhere(function ($query) use ($userId) {
                $query->where('assigned_biodata_id', $userId)
                    ->where('progress_percentage', '<', 100);
            })
            ->count();

        // Count Total Ketercapaian

        $allTaskPercentageSum = SikKinerjaTask::where(function ($query) use ($userId, $startPeriode, $endPeriode) {
            $query->where('assigned_biodata_id', $userId)
                ->whereRaw("DATE(created_at) BETWEEN ? AND ?", [$startPeriode, $endPeriode]);
        })
            ->orWhere(function ($query) use ($userId) {
                $query->where('assigned_biodata_id', $userId)
                    ->where('progress_percentage', '<', 100);
            })
            ->sum("progress_percentage");

        // Assign the sum to a variable
        $percentage = $allTaskCount < 1 ? 0 : ($allTaskPercentageSum / $allTaskCount);

        return response()->json([
            "undoneTask" => $totalTaskUndone,
            "TotalTask" => $allTaskCount,
            "ketercapaian" => $percentage,
            "startPeriode" => $startPeriode,
            "endPeriode" => $endPeriode
        ]);
    }

    public function getDashSummaryNew(Request $unit)
    {
        $activeId = Auth::id();
        $userId = intval($unit->id);
        $unitId = intval($unit->unitId);
        $struktural_id = intval($unit->struktural_id);

        $startPeriode = $unit->input("start_periode", calculatePeriod()[0]);
        $endPeriode = $unit->input("end_periode", calculatePeriod()[1]);

        // return response()->json($unit);

        // Model Unit Kerja -> SikUnitKerja() -> unit_id dari user
        // Model Struktural -> SikJabatanStruktural() -> unit_id dari user
        // Ambil semua user berdasrkan unit_id
        // Cek level jabatan struktral
        // Ambil level dari sama dengan dan kebawah

        // $selectedUnit = SikUnitKerja::where('id', $unitId)->first();
        $selectedStruktural = SikJabatanStruktural::where('id', $struktural_id)->first();

        $currentJabatanLevel = intval($selectedStruktural->jabatan_level);
        $currentDivisi = $selectedStruktural->divisi;

        // $currentJabatanLevel = 4;

        $allowedAssigneLevel = [$currentJabatanLevel];

        $assigneList = null;
        $assigneUnitIdList = [];

        if ($currentJabatanLevel === 2) {
            $assigneList = SikJabatanStruktural::where('unit_id', $unitId)
                ->where(function ($query) {
                    $query->where('jabatan_level', 2)
                        ->orWhere('jabatan_level', 3)
                        ->orWhere('jabatan_level', 4);
                })
                ->get();
        }
        if ($currentJabatanLevel === 3) {
            $assigneList = SikJabatanStruktural::where('unit_id', $unitId)
                ->where(function ($query) {
                    $query->where('jabatan_level', 3)
                        ->orWhere('jabatan_level', 4);
                })
                ->where('divisi', $currentDivisi)
                ->get();
        }

        if ($currentJabatanLevel === 4) {
            // $assigneList = SikJabatanStruktural::where('unit_id', $unitId)
            //     ->where('jabatan_level', 4)
            //     ->get();
            $assigneList = [SikJabatanStruktural::where('id', $struktural_id)->first()];
        }

        // Only for Debug
        // return response()->json($unitId);

        // If active user jabatan level is 4, just get his/her task instead
        if ($currentJabatanLevel === 4) {
            $realAssigneList = [SikBiodata::find(intval($userId))];
        } else {
            $targetBio = SikBiodata::find($userId);

            if (intval($targetBio->status) === 5) {
                $realAssigneList = [SikBiodata::find($userId)];
            } else {
                for ($i = 0; $i < sizeof($assigneList); $i++) {
                    array_push($assigneUnitIdList, $assigneList[$i]->id);
                }

                $realAssigneList = SikBiodata::whereIn('jabatan_struktural_id', $assigneUnitIdList)->get();
            }
        }

        // return response()->json($realAssigneList);

        $assigneUserId = [];

        for ($i = 0; $i < sizeof($realAssigneList); $i++) {
            // if (intval($realAssigneList[$i]->id) !== $activeId) {
            //     array_push($assigneUserId, $realAssigneList[$i]->id);
            // }
            array_push($assigneUserId, [$realAssigneList[$i]->id, $realAssigneList[$i]->fullname]);
        }

        $payload = [];

        for ($i = 0; $i < sizeof($assigneUserId); $i++) {
            // $totalTask = SikKinerjaTask::where("assigned_biodata_id", $userId)
            //     ->whereRaw("DATE(created_at) BETWEEN ? AND ?", [$startPeriode, $endPeriode])
            //     ->where('progress_percentage', 100)->count();

            $currentId = $assigneUserId[$i][0];

            $totalTaskUndone = SikKinerjaTask::where("assigned_biodata_id", $currentId)->whereRaw("DATE(created_at) BETWEEN ? AND ?", [$startPeriode, $endPeriode])
                ->whereNot('progress_percentage', 100)->count();

            $allTaskCount = SikKinerjaTask::where(function ($query) use ($currentId, $startPeriode, $endPeriode) {
                $query->where('assigned_biodata_id', $currentId)
                    ->whereRaw("DATE(created_at) BETWEEN ? AND ?", [$startPeriode, $endPeriode]);
            })
                ->orWhere(function ($query) use ($currentId, $startPeriode, $endPeriode) {
                    $query->where('assigned_biodata_id', $currentId)->whereRaw("DATE(created_at) BETWEEN ? AND ?", [$startPeriode, $endPeriode])
                        ->where('progress_percentage', '<', 100);
                })
                ->count();


            // Count Total Ketercapaian

            $allTaskPercentageSum = SikKinerjaTask::where(function ($query) use ($currentId, $startPeriode, $endPeriode) {
                $query->where('assigned_biodata_id', $currentId)
                    ->whereRaw("DATE(created_at) BETWEEN ? AND ?", [$startPeriode, $endPeriode]);
            })
                ->orWhere(function ($query) use ($currentId, $startPeriode, $endPeriode) {
                    $query->where('assigned_biodata_id', $currentId)->whereRaw("DATE(created_at) BETWEEN ? AND ?", [$startPeriode, $endPeriode])
                        ->where('progress_percentage', '<', 100);
                })
                ->sum("progress_percentage");

            // Assign the sum to a variable
            $percentage = $allTaskCount < 1 ? 0 : ($allTaskPercentageSum / $allTaskCount);

            array_push($payload, [
                "id" => $assigneUserId[$i][0],
                "name" => $assigneUserId[$i][1],
                "undoneTask" => $totalTaskUndone,
                "TotalTask" => $allTaskCount,
                "ketercapaian" => $percentage,
                "startPeriode" => $startPeriode,
                "endPeriode" => $endPeriode
            ]);
        }
        return response()->json($payload);
    }

    public function getDashSummaryAdmin(Request $unit)
    {
        $activeId = Auth::id();
        $userId = intval($unit->id);
        $unitId = intval($unit->unitId);
        $struktural_id = intval($unit->struktural_id);

        $startPeriode = calculatePeriod()[0];
        $endPeriode = calculatePeriod()[1];

        // return response()->json($unit);

        // Model Unit Kerja -> SikUnitKerja() -> unit_id dari user
        // Model Struktural -> SikJabatanStruktural() -> unit_id dari user
        // Ambil semua user berdasrkan unit_id
        // Cek level jabatan struktral
        // Ambil level dari sama dengan dan kebawah

        // $selectedUnit = SikUnitKerja::where('id', $unitId)->first();
        $selectedStruktural = SikJabatanStruktural::where('id', $struktural_id)->first();

        // $currentJabatanLevel = intval($selectedStruktural->jabatan_level);
        // $currentDivisi = $selectedStruktural->divisi;

        // // $currentJabatanLevel = 4;

        // $allowedAssigneLevel = [$currentJabatanLevel];

        $assigneList = null;
        $assigneUnitIdList = [];

        $assigneList = SikJabatanStruktural::where('unit_id', $unitId)
            ->where(function ($query) {
                $query->where('jabatan_level', 2)
                    ->orWhere('jabatan_level', 3)
                    ->orWhere('jabatan_level', 4);
            })
            ->get();

        // if ($currentJabatanLevel === 3) {
        //     $assigneList = SikJabatanStruktural::where('unit_id', $unitId)
        //         ->where(function ($query) {
        //             $query->where('jabatan_level', 3)
        //                 ->orWhere('jabatan_level', 4);
        //         })
        //         ->where('divisi', $currentDivisi)
        //         ->get();
        // }

        // if ($currentJabatanLevel === 4) {
        //     // $assigneList = SikJabatanStruktural::where('unit_id', $unitId)
        //     //     ->where('jabatan_level', 4)
        //     //     ->get();
        //     $assigneList = [SikJabatanStruktural::where('id', $struktural_id)->first()];
        // }

        // Only for Debug
        // return response()->json($unitId);

        for ($i = 0; $i < sizeof($assigneList); $i++) {
            array_push($assigneUnitIdList, $assigneList[$i]->id);
        }


        $realAssigneList = SikBiodata::whereIn('jabatan_struktural_id', $assigneUnitIdList)->get();


        // return response()->json($realAssigneList);

        $assigneUserId = [];

        for ($i = 0; $i < sizeof($realAssigneList); $i++) {
            // if (intval($realAssigneList[$i]->id) !== $activeId) {
            //     array_push($assigneUserId, $realAssigneList[$i]->id);
            // }
            array_push($assigneUserId, [$realAssigneList[$i]->id, $realAssigneList[$i]->fullname]);
        }

        $payload = [];

        for ($i = 0; $i < sizeof($assigneUserId); $i++) {
            // $totalTask = SikKinerjaTask::where("assigned_biodata_id", $userId)
            //     ->whereRaw("DATE(created_at) BETWEEN ? AND ?", [$startPeriode, $endPeriode])
            //     ->where('progress_percentage', 100)->count();

            $currentId = $assigneUserId[$i][0];

            $totalTaskUndone = SikKinerjaTask::where("assigned_biodata_id", $currentId)
                ->whereNot('progress_percentage', 100)->count();

            $allTaskCount = SikKinerjaTask::where(function ($query) use ($currentId, $startPeriode, $endPeriode) {
                $query->where('assigned_biodata_id', $currentId)
                    ->whereRaw("DATE(created_at) BETWEEN ? AND ?", [$startPeriode, $endPeriode]);
            })
                ->orWhere(function ($query) use ($currentId) {
                    $query->where('assigned_biodata_id', $currentId)
                        ->where('progress_percentage', '<', 100);
                })
                ->count();

            // Count Total Ketercapaian

            $allTaskPercentageSum = SikKinerjaTask::where(function ($query) use ($currentId, $startPeriode, $endPeriode) {
                $query->where('assigned_biodata_id', $currentId)
                    ->whereRaw("DATE(created_at) BETWEEN ? AND ?", [$startPeriode, $endPeriode]);
            })
                ->orWhere(function ($query) use ($currentId) {
                    $query->where('assigned_biodata_id', $currentId)
                        ->where('progress_percentage', '<', 100);
                })
                ->sum("progress_percentage");

            // Assign the sum to a variable
            $percentage = $allTaskCount < 1 ? 0 : ($allTaskPercentageSum / $allTaskCount);

            array_push($payload, [
                "id" => $assigneUserId[$i][0],
                "name" => $assigneUserId[$i][1],
                "undoneTask" => $totalTaskUndone,
                "TotalTask" => $allTaskCount,
                "ketercapaian" => $percentage,
                "startPeriode" => $startPeriode,
                "endPeriode" => $endPeriode
            ]);
        }
        return response()->json($payload);
    }
}
