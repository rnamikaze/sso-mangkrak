<?php

namespace App\Http\Controllers\SIK;

use Carbon\Carbon;
use App\Models\SIK\SikProdi;
use Illuminate\Http\Request;
use App\Models\SIK\SikBiodata;
use App\Models\SIK\SikFakultas;
use App\Models\SIK\SikUnitKerja;
use App\Models\SIK\SikKinerjaTask;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\SIK\SikKinerjaSubTask;
use App\Models\SikStaffFakultasModel;
use App\Models\SIK\SikJabatanStrukDos;
use App\Models\SIK\SikKinerjaDosenTask;
use App\Models\SIK\SikJabatanStruktural;
use App\Models\SIK\SikKinerjaSubDosenTask;

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

class KinerjaDosenController extends Controller
{
    //
    public function updatePeriode(Request $data)
    {
        $taskId = $data->task_id;

        $targetDosenTask = SikKinerjaDosenTask::find($taskId);
        $targetDosenSubTask = SikKinerjaSubDosenTask::where('task_id', $taskId)->get();

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

    public function getAssigneName(Request $unit)
    {
        $activeId = intval(Auth::id());
        $unitId = intval($unit->id);
        $struktural_id = intval($unit->struktural_dosen_id);

        // return response()->json($unit);

        // Model Unit Kerja -> SikUnitKerja() -> unit_id dari user
        // Model Struktural -> SikJabatanStruktural() -> unit_id dari user
        // Ambil semua user berdasrkan unit_id
        // Cek level jabatan struktral
        // Ambil level dari sama dengan dan kebawah

        // Determine target Prodi
        $selectedBiodata = SikBiodata::find($activeId);
        if ($selectedBiodata === null) $selectedBiodata = SikBiodata::where('master_id', $activeId)->first();

        $selectedProdiId = $selectedBiodata->prodi_id;

        // Determine Target Fakultas
        $selectedFakultas = SikFakultas::where('id', intval($unit->fakultas_id))->first();
        $fakultasName = $selectedFakultas->name;
        $fakultasId = intval($unit->fakultas_id);

        // Determine Struktural Dosen
        $selectedStruktural = SikJabatanStrukDos::where('id', $struktural_id)->first();

        // Determine Dosen Level
        $currentJabatanLevel = intval($selectedStruktural->level_jsd);

        // return response()->json(["data" => $currentJabatanLevel]);

        $allowedAssigneLevel = [$currentJabatanLevel];

        $assigneList = null;
        $assigneUnitIdList = [];

        // Filter By Fakultas & Prodi Start <==
        // Ambil semua jabatan dibawah jabatan target user
        $allJabatanSameAndBellow = SikJabatanStrukDos::where('level_jsd', '>=', $currentJabatanLevel)->get();
        $strukturalDosenId = [];

        for ($i = 0; $i < sizeof($allJabatanSameAndBellow); $i++) {
            array_push($strukturalDosenId, $allJabatanSameAndBellow[$i]->id);
        }

        // Exclude TU if based rule
        if ($struktural_id >= 3 && $struktural_id <= 5) {
            $allBawahan = SikBiodata::where('fakultas_id', $fakultasId)->where('status', 4)->where('prodi_id', $selectedProdiId)->where('jabatan_strukdos_id', '>=', $struktural_id)->where('jabatan_strukdos_id', '!=', 6)->get();
        } else {
            if ($currentJabatanLevel <= 1) {
                $allBawahan = SikBiodata::where('fakultas_id', $fakultasId)->whereIn('status', [4, 5])->where('jabatan_strukdos_id', '>=', $struktural_id)->get();
            } else {
                $allBawahan = SikBiodata::where('fakultas_id', $fakultasId)->whereIn('status', [4, 5])->where('prodi_id', $selectedProdiId)->where('jabatan_strukdos_id', '>=', $struktural_id)->get();
            }
        }

        for ($i = 0; $i < sizeof($allBawahan); $i++) {
            array_push($assigneUnitIdList, $allBawahan[$i]->id);
        }

        // If active user jabatan level is 5, just get his/her task instead
        if ($currentJabatanLevel === 5 || $currentJabatanLevel === 6) {
            $realAssigneList = [SikBiodata::find(intval($unit->userId))];
        } else {
            $realAssigneList = SikBiodata::whereIn('id', $assigneUnitIdList)->orderBy('jabatan_strukdos_id')->get();
        }

        for ($i = 0; $i < sizeof($realAssigneList); $i++) {
            $targetProdi = SikProdi::where('id', intval($realAssigneList[$i]->prodi_id))->select('name', 'fakultas_id')->first();
            $prodiName = $targetProdi->name;
            $targetStrukdos = SikJabatanStrukDos::where('id', intval($realAssigneList[$i]->jabatan_strukdos_id))->select('name')->first();
            $jabatanStrukdosName = $targetStrukdos->name;

            if (intval($realAssigneList[$i]->status) === 5) {
                // $realAssigneList[$i]->jabatan_strukdos = $jabatanStrukdosName;
                $realAssigneList[$i]->nama_prodi = "Tidak Ada";

                $fakultas = SikFakultas::find(intval($realAssigneList[$i]->fakultas_id));
                $fakultasName = $fakultas->name;
                $realAssigneList[$i]->nama_fakultas = ucwords($fakultasName);

                $targetStaffFakultas = SikStaffFakultasModel::find(intval($realAssigneList[$i]->staff_fakultas_id));
                $realAssigneList[$i]->jabatan_strukdos = $targetStaffFakultas->name;
            } else {
                $realAssigneList[$i]->jabatan_strukdos = $jabatanStrukdosName;
                $realAssigneList[$i]->nama_prodi = ucwords($prodiName);

                $fakultas = SikFakultas::find(intval($targetProdi->fakultas_id));
                $fakultasName = $fakultas->name;
                $realAssigneList[$i]->nama_fakultas = ucwords($fakultasName);
            }
        }

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

        // Define the Periode Date
        $startPeriode = calculatePeriod()[0];
        $endPeriode = calculatePeriod()[1];

        for ($i = 0; $i < sizeof($assigneUserId); $i++) {
            // if ($unit->has('customDate')) {
            //     // Define the Periode Date
            //     $startPeriode = calculatePeriod($unit->customDate)[0];
            //     $endPeriode = calculatePeriod($unit->customDate)[1];
            // }

            // Get The Data within Periode Date
            // $getTask = SikKinerjaTask::where('assigned_biodata_id', $assigneUserId[$i][0])->where('progress_percentage', '<', 100)
            //     ->whereBetween('created_at', [$startPeriode, $endPeriode])->get();

            // $getTask = SikKinerjaTask::where('assigned_biodata_id', $assigneUserId[$i][0])->where('progress_percentage', '<', 100)
            //     ->orWhere('created_at', [$startPeriode, $endPeriode])->get();

            $buffAssigneUnitId = $assigneUserId[$i][0];

            // $getTask = SikKinerjaDosenTask::where(function ($query) use ($buffAssigneUnitId, $startPeriode, $endPeriode) {
            //     $query->where('assigned_biodata_id', $buffAssigneUnitId)
            //         ->whereRaw("DATE(created_at) BETWEEN ? AND ?", [$startPeriode, $endPeriode]);
            // })
            //     ->orWhere(function ($query) use ($buffAssigneUnitId) {
            //         $query->where('assigned_biodata_id', $buffAssigneUnitId)
            //             ->where('progress_percentage', '<', 100);
            //     })
            //     ->get();

            $getTask = SikKinerjaDosenTask::where(function ($query) use ($buffAssigneUnitId, $startPeriode, $endPeriode) {
                $query->where('assigned_biodata_id', $buffAssigneUnitId)
                    ->where("created_at", ">=", $startPeriode)->where('created_at', "<=", $endPeriode);
            })
                ->orWhere(function ($query) use ($buffAssigneUnitId) {
                    $query->where('assigned_biodata_id', $buffAssigneUnitId)
                        ->where('progress_percentage', '<', 100);
                })
                ->get();

            $targetBiodata = SikBiodata::find(intval($assigneUserId[$i][0]));
            $targetProdi = SikProdi::where('id', $targetBiodata->prodi_id)->select('name')->first();

            $targetJabatan = SikJabatanStrukDos::find(intval($targetBiodata->jabatan_strukdos_id));

            if ($getTask) {
                for ($t = 0; $t < sizeof($getTask); $t++) {
                    // Mimic the Task Initial Value in TaskList.jsx
                    $getTask[$t]->assigne_name = $assigneUserId[$i][1];

                    // Ambil Semua Sub Task
                    $getAllSubTask = SikKinerjaSubDosenTask::where('task_id', intval($getTask[$t]->id))->get();

                    // Get the latest due date from Sub Task
                    $getFirstLatestDate = SikKinerjaSubDosenTask::where('task_id', intval($getTask[$t]->id))->latest('due_date')->first();

                    if ($getFirstLatestDate) {
                        $getTask[$t]->latest_date = $getFirstLatestDate->due_date;
                    } else {
                        $getTask[$t]->latest_date = Carbon::now()->format("Y-m-d");
                    }

                    $getTask[$t]->childTask = $getAllSubTask ? $getAllSubTask : [];
                    $getTask[$t]->jabatan_level = $targetJabatan->level_jsd;

                    $getTask[$t]->is_late = false;

                    if (checkTaskLateNow($getTask[$t]->periode_start)) {
                        $getTask[$t]->is_late = true;
                    }
                }
            }

            array_push($arrayUserTask, [$assigneUserId[$i][0] => $getTask]);
        }
        // array_push($arrayUserTask, [$activeId => $getActiveUserTask]);
        $capture = [
            "id" => $strukturalDosenId,
            "userId" => $unit->userId,
            "struktural_dosen_id" => $unit->struktural_dosen_id,
            "fakultas_id" => $unit->fakultas_id,
            "customDate" => $unit->customDate
        ];

        $sendPayload = [
            "unit" => $strukturalDosenId,
            "assigneList" => $realAssigneList,
            "userTask" => $arrayUserTask,
            "periode" => [$startPeriode, $endPeriode],
            "debug" => $allBawahan
        ];

        // $sendPayload = ["unit" => $unit];

        return response()->json($sendPayload);
    }

    public function getAssigneNameOld(Request $unit)
    {
        $activeId = Auth::id();
        $unitId = intval($unit->id);
        $struktural_id = intval($unit->struktural_dosen_id);

        // return response()->json($unit);

        // Model Unit Kerja -> SikUnitKerja() -> unit_id dari user
        // Model Struktural -> SikJabatanStruktural() -> unit_id dari user
        // Ambil semua user berdasrkan unit_id
        // Cek level jabatan struktral
        // Ambil level dari sama dengan dan kebawah

        // Determine Target Fakultas
        $selectedFakultas = SikFakultas::where('id', intval($unit->fakultas_id))->first();
        $fakultasName = $selectedFakultas->name;
        $fakultasId = $selectedFakultas->id;

        // Determine Struktural Dosen
        $selectedStruktural = SikJabatanStrukDos::where('id', $struktural_id)->first();

        // Determine Dosen Level
        $currentJabatanLevel = intval($selectedStruktural->level_jsd);

        $allowedAssigneLevel = [$currentJabatanLevel];

        $assigneList = null;
        $assigneUnitIdList = [];

        if ($currentJabatanLevel === 0) {
            $assigneList = SikJabatanStrukDos::where('fakultas_id', $selectedFakultas)
                ->where(function ($query) {
                    $query->where('jabatan_level', 0)
                        ->orWhere('jabatan_level', 1)
                        ->orWhere('jabatan_level', 2)
                        ->orWhere('jabatan_level', 3)
                        ->orWhere('jabatan_level', 4)
                        ->orWhere('jabatan_level', 5);
                })
                ->get();
        }
        if ($currentJabatanLevel === 1) {
            $assigneList = SikJabatanStrukDos::where('fakultas_id', $selectedFakultas)
                ->where(function ($query) {
                    $query->where('jabatan_level', 1)
                        ->orWhere('jabatan_level', 2)
                        ->orWhere('jabatan_level', 3)
                        ->orWhere('jabatan_level', 4)
                        ->orWhere('jabatan_level', 5);
                })
                ->get();
        }
        if ($currentJabatanLevel === 2) {
            $assigneList = SikJabatanStrukDos::where('fakultas_id', $selectedFakultas)
                ->where(function ($query) {
                    $query->where('jabatan_level', 2)
                        ->orWhere('jabatan_level', 3)
                        ->orWhere('jabatan_level', 4)
                        ->orWhere('jabatan_level', 5);
                })
                ->get();
        }
        if ($currentJabatanLevel === 3) {
            $assigneList = SikJabatanStrukDos::where('fakultas_id', $selectedFakultas)
                ->where(function ($query) {
                    $query->where('jabatan_level', 3)
                        ->orWhere('jabatan_level', 4)
                        ->orWhere('jabatan_level', 5);
                })
                ->get();
        }
        if ($currentJabatanLevel === 4) {
            $assigneList = SikJabatanStrukDos::where('fakultas_id', $selectedFakultas)
                ->where(function ($query) {
                    $query->where('jabatan_level', 3)
                        ->orWhere('jabatan_level', 4)
                        ->orWhere('jabatan_level', 5);
                })
                ->get();
        }
        if ($currentJabatanLevel === 5) {
            // $assigneList = SikJabatanStrukDos::where('unit_id', $unitId)
            //     ->where('jabatan_level', 4)
            //     ->get();
            $assigneList = [SikJabatanStrukDos::where('fakultas_id', $selectedFakultas)->first()];
        }

        // Only for Debug
        // return response()->json($assigneList);

        for ($i = 0; $i < sizeof($assigneList); $i++) {
            array_push($assigneUnitIdList, $assigneList[$i]->id);
        }

        // If active user jabatan level is 4, just get his/her task instead
        if ($currentJabatanLevel === 5) {
            $realAssigneList = [SikBiodata::find(intval($unit->userId))];
        } else {
            $realAssigneList = SikBiodata::whereIn('jabatan_strukdos_id', $assigneUnitIdList)->get();
        }

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

            $getTask = SikKinerjaDosenTask::where(function ($query) use ($buffAssigneUnitId, $startPeriode, $endPeriode) {
                $query->where('assigned_biodata_id', $buffAssigneUnitId)
                    ->whereRaw("DATE(created_at) BETWEEN ? AND ?", [$startPeriode, $endPeriode]);
            })
                ->orWhere(function ($query) use ($buffAssigneUnitId) {
                    $query->where('assigned_biodata_id', $buffAssigneUnitId)
                        ->where('progress_percentage', '<', 100);
                })
                ->get();

            $targetBiodata = SikBiodata::find(intval($assigneUserId[$i][0]));

            $targetJabatan = SikJabatanStrukDos::find(intval($targetBiodata->jabatan_struktural_id));

            if ($getTask) {
                for ($t = 0; $t < sizeof($getTask); $t++) {
                    // Mimic the Task Initial Value in TaskList.jsx
                    $getTask[$t]->assigne_name = $assigneUserId[$i][1];

                    // Ambil Semua Sub Task
                    $getAllSubTask = SikKinerjaSubDosenTask::where('task_id', intval($getTask[$t]->id))->get();

                    // Get the latest due date from Sub Task
                    $getFirstLatestDate = SikKinerjaSubDosenTask::where('task_id', intval($getTask[$t]->id))->latest('due_date')->first();

                    if ($getFirstLatestDate) {
                        $getTask[$t]->latest_date = $getFirstLatestDate->due_date;
                    } else {
                        $getTask[$t]->latest_date = Carbon::now()->format("Y-m-d");
                    }


                    $getTask[$t]->childTask = $getAllSubTask ? $getAllSubTask : [];
                    $getTask[$t]->jabatan_level = $targetJabatan->jabatan_level;
                }
            }

            array_push($arrayUserTask, [$assigneUserId[$i][0] => $getTask]);
        }
        // array_push($arrayUserTask, [$activeId => $getActiveUserTask]);

        $sendPayload = ["assigneList" => $realAssigneList, "userTask" => $arrayUserTask, "periode" => [$startPeriode, $endPeriode]];

        return response()->json($sendPayload);
    }

    public function getUserAdditionalInfo(Request $payload)
    {
        $targetStruktural = SikJabatanStrukDos::find(intval($payload->struktural_dosen_id));

        $targetJabatanLevel = $targetStruktural->level_jsd;

        return response()->json(['jabatan_level' => $targetJabatanLevel]);
    }

    public function createNewTask(Request $task)
    {
        $createNewTask = new SikKinerjaDosenTask();

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
        $doDelete = SikKinerjaDosenTask::destroy(intval($id->id));

        if ($doDelete === 0) {
            return response()->json(["deleteSuccess" => false]);
        }
        return response()->json(["deleteSuccess" => true]);
    }

    public function createSubTask(Request $payload)
    {
        // return response()->json($payload);

        $newSubTask = new SikKinerjaSubDosenTask();

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

        $targetTask = SikKinerjaDosenTask::find(intval($payload->taskId));

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
            // $progressSum = SikKinerjaSubDosenTask::where('task_id', intval($payload->taskId))->sum('progress_int');
            // $progressCount = SikKinerjaSubDosenTask::where('task_id', intval($payload->taskId))->count();

            // Assign the sum to a variable
            // $percentage = ($progressSum / ($progressCount * 2)) * 100;

            // New Calculation == 4 <<
            // Fetch the task_percentage column values from the database

            $taskPercentages = SikKinerjaSubDosenTask::where('task_id', intval($payload->taskId))->pluck('progress_int');

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

            // return response()->json($percentage);
            $targetTask = SikKinerjaDosenTask::find(intval($payload->taskId));
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

        $targetSubTask = SikKinerjaSubDosenTask::find(intval($subTask->id));

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
            // $progressSum = SikKinerjaSubDosenTask::where('task_id', intval($subTask->task_id))->sum('progress_int');
            // $progressCount = SikKinerjaSubDosenTask::where('task_id', intval($subTask->task_id))->count();

            // Assign the sum to a variable
            // $percentage = ($progressSum / ($progressCount * 2)) * 100;

            // New Calculation == 4 <<
            // Fetch the task_percentage column values from the database

            $taskPercentages = SikKinerjaSubDosenTask::where('task_id', intval($subTask->task_id))->pluck('progress_int');

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

            $targetTask = SikKinerjaDosenTask::find(intval($subTask->task_id));
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
        $good = SikKinerjaSubDosenTask::destroy(intval($subTaskId->id));
        // $good = 1;

        if ($good > 0) {
            // $progressSum = SikKinerjaSubDosenTask::where('task_id', intval($subTaskId->taskId))->sum('progress_int');
            // $progressCount = SikKinerjaSubDosenTask::where('task_id', intval($subTaskId->taskId))->count();

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

            $taskPercentages = SikKinerjaSubDosenTask::where('task_id', intval($subTaskId->taskId))->pluck('progress_int');

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

            $targetTask = SikKinerjaDosenTask::find(intval($subTaskId->taskId));
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

    public function getDashSummaryNew(Request $unit)
    {
        $activeId = intval(Auth::id());
        // $unitId = intval($unit->id);
        $struktural_id = intval($unit->struktural_id);

        $startPeriode = calculatePeriod()[0];
        $endPeriode = calculatePeriod()[1];

        // return response()->json($unit);

        // Model Unit Kerja -> SikUnitKerja() -> unit_id dari user
        // Model Struktural -> SikJabatanStruktural() -> unit_id dari user
        // Ambil semua user berdasrkan unit_id
        // Cek level jabatan struktral
        // Ambil level dari sama dengan dan kebawah

        // Determine target Prodi
        $selectedBiodata = SikBiodata::find($activeId);
        if ($selectedBiodata === null) $selectedBiodata = SikBiodata::where('master_id', $activeId)->first();

        $selectedProdiId = $selectedBiodata->prodi_id;

        // Determine Target Fakultas
        $selectedFakultas = SikFakultas::where('id', intval($selectedBiodata->fakultas_id))->first();
        $fakultasName = $selectedFakultas->name;
        $fakultasId = intval($selectedBiodata->fakultas_id);

        // Determine Struktural Dosen
        $selectedStruktural = SikJabatanStrukDos::where('id', $struktural_id)->first();

        // Determine Dosen Level
        $currentJabatanLevel = intval($selectedStruktural->level_jsd);

        $allowedAssigneLevel = [$currentJabatanLevel];

        $assigneList = null;
        $assigneUnitIdList = [];

        // Filter By Fakultas & Prodi Start <==
        // Ambil semua jabatan dibawah jabatan target user
        $allJabatanSameAndBellow = SikJabatanStrukDos::where('level_jsd', '>=', $currentJabatanLevel)->get();
        $strukturalDosenId = [];

        for ($i = 0; $i < sizeof($allJabatanSameAndBellow); $i++) {
            array_push($strukturalDosenId, $allJabatanSameAndBellow[$i]->id);
        }

        if ($struktural_id >= 3 && $struktural_id <= 5) {
            $allBawahan = SikBiodata::where('fakultas_id', $fakultasId)->where('status', 4)->where('prodi_id', $selectedProdiId)->where('jabatan_strukdos_id', '>=', $struktural_id)->where('jabatan_strukdos_id', '!=', 6)->get();
        } else {
            if ($currentJabatanLevel <= 1) {
                $allBawahan = SikBiodata::where('fakultas_id', $fakultasId)->whereIn('status', [4, 5])->where('jabatan_strukdos_id', '>=', $struktural_id)->get();
            } else {
                $allBawahan = SikBiodata::where('fakultas_id', $fakultasId)->whereIn('status', [4, 5])->where('prodi_id', $selectedProdiId)->where('jabatan_strukdos_id', '>=', $struktural_id)->get();
            }
        }

        //

        // Only for Debug
        // return response()->json(["al" => $fakultasId]);

        for ($i = 0; $i < sizeof($allBawahan); $i++) {
            array_push($assigneUnitIdList, $allBawahan[$i]->id);
        }

        // If active user jabatan level is 4, just get his/her task instead
        if ($currentJabatanLevel === 5 || $currentJabatanLevel === 6) {
            $realAssigneList = [SikBiodata::find(intval($unit->id))];
        } else {
            $realAssigneList = SikBiodata::whereIn('id', $assigneUnitIdList)->orderBy('jabatan_strukdos_id')->get();
        }

        // return response()->json([
        //     "debug" => $realAssigneList
        // ]);

        for ($i = 0; $i < sizeof($realAssigneList); $i++) {
            if ($currentJabatanLevel === 5) {
                // $targetProdi = SikProdi::where('id', intval($realAssigneList[$i]->prodi_id))->select('name')->first();
                // $prodiName = $targetProdi->name;
                // $targetStrukdos = SikJabatanStrukDos::where('id', intval($realAssigneList[$i]->jabatan_strukdos_id))->select('name')->first();
                // $jabatanStrukdosName = $targetStrukdos->name;

                $targetFakultas = SikFakultas::where('id', intval($realAssigneList[$i]->fakultas_id))->select('name')->first();
                $targetStaffFakultas = SikStaffFakultasModel::find(intval($realAssigneList[$i]->staff_fakultas_id));
                // $realAssigneList[$i]->jabatan_strukdo = $targetStaffFakultas->name . ", Fakultas " . ucwords($targetFakultas->name);

                $realAssigneList[$i]->jabatan_strukdos = $targetStaffFakultas->name;
                $realAssigneList[$i]->nama_prodi = $targetFakultas->name;
            } else {
                $targetProdi = SikProdi::where('id', intval($realAssigneList[$i]->prodi_id))->select('name')->first();
                $prodiName = $targetProdi->name;
                $targetStrukdos = SikJabatanStrukDos::where('id', intval($realAssigneList[$i]->jabatan_strukdos_id))->select('name')->first();
                $jabatanStrukdosName = $targetStrukdos->name;

                $realAssigneList[$i]->jabatan_strukdos = $jabatanStrukdosName;
                $realAssigneList[$i]->nama_prodi = ucwords($prodiName);
            }
        }

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

            $totalTaskUndone = SikKinerjaDosenTask::where("assigned_biodata_id", $currentId)
                ->whereNot('progress_percentage', 100)->count();

            $allTaskCount = SikKinerjaDosenTask::where(function ($query) use ($currentId, $startPeriode, $endPeriode) {
                $query->where('assigned_biodata_id', $currentId)
                    ->whereRaw("DATE(created_at) BETWEEN ? AND ?", [$startPeriode, $endPeriode]);
            })
                ->orWhere(function ($query) use ($currentId) {
                    $query->where('assigned_biodata_id', $currentId)
                        ->where('progress_percentage', '<', 100);
                })
                ->count();

            // Count Total Ketercapaian

            $allTaskPercentageSum = SikKinerjaDosenTask::where(function ($query) use ($currentId, $startPeriode, $endPeriode) {
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

            // Get Biodata
            $currentBiodata = SikBiodata::find(intval($currentId));

            $fakultas_id = $currentBiodata->fakultas_id;
            $prodi_id = $currentBiodata->prodi_id;

            if (intval($currentBiodata->status) === 5) {
                $targetStaffFakultas = SikStaffFakultasModel::find(intval($currentBiodata->staff_fakultas_id));

                $prodiName = "Staff Fakultas";
                $jabatanName = ucwords($targetStaffFakultas->name);
            } else {
                $prodi = SikProdi::find(intval($prodi_id));
                $prodiName = ucwords($prodi->name);

                $jabatan = SikJabatanStrukDos::find(intval($currentBiodata->jabatan_strukdos_id));
                $jabatanName = $jabatan->name;
            }

            $fakultas = SikFakultas::find(intval($fakultas_id));
            $fakultasName = ucwords($fakultas->name);

            array_push($payload, [
                "id" => $assigneUserId[$i][0],
                "name" => $assigneUserId[$i][1],
                "undoneTask" => $totalTaskUndone,
                "TotalTask" => $allTaskCount,
                "ketercapaian" => $percentage,
                "startPeriode" => $startPeriode,
                "endPeriode" => $endPeriode,
                'nama_fakultas' => $fakultasName,
                'nama_prodi' => $prodiName,
                'jabatan_id' => $currentBiodata->jabatan_strukdos_id,
                'nama_jabatan' => $jabatanName
            ]);
        }
        return response()->json([
            "success" => true,
            "dashSummary" => $payload
        ]);
    }

    public function getDashSummaryNewOld(Request $unit)
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

        for ($i = 0; $i < sizeof($assigneList); $i++) {
            array_push($assigneUnitIdList, $assigneList[$i]->id);
        }

        // If active user jabatan level is 4, just get his/her task instead
        if ($currentJabatanLevel === 4) {
            $realAssigneList = [SikBiodata::find(intval($userId))];
        } else {
            $realAssigneList = SikBiodata::whereIn('jabatan_struktural_id', $assigneUnitIdList)->get();
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

    public function getTaskHistory(Request $unit)
    {
        $activeId = intval(Auth::id());
        // $unitId = intval($unit->id);
        $struktural_id = intval($unit->struktural_id);

        // Use frontend start and end dates
        $startPeriode = Carbon::parse($unit->startDate)->format('Y-m-d');
        $endPeriode = Carbon::parse($unit->endDate)->format('Y-m-d');
        // $currentDate = Carbon::now()->format('Y-m-d');

        $isCurrentPeriode = Carbon::now()->between($startPeriode, $endPeriode);
        // Check if the current date is within the selected period

        $currentDate = Carbon::now()->format('Y-m-d');

        $isCurrentPeriode = isDateInRange($startPeriode, $endPeriode, $currentDate);

        // return response()->json($unit);

        // Model Unit Kerja -> SikUnitKerja() -> unit_id dari user
        // Model Struktural -> SikJabatanStruktural() -> unit_id dari user
        // Ambil semua user berdasrkan unit_id
        // Cek level jabatan struktral
        // Ambil level dari sama dengan dan kebawah

        // Determine target Prodi
        $selectedBiodata = SikBiodata::find($activeId);
        if ($selectedBiodata === null) $selectedBiodata = SikBiodata::where('master_id', $activeId)->first();

        $selectedProdiId = $selectedBiodata->prodi_id;

        // Determine Target Fakultas
        $selectedFakultas = SikFakultas::where('id', intval($selectedBiodata->fakultas_id))->first();
        $fakultasName = $selectedFakultas->name;
        $fakultasId = intval($selectedBiodata->fakultas_id);

        // Determine Struktural Dosen
        $selectedStruktural = SikJabatanStrukDos::where('id', $struktural_id)->first();

        // Determine Dosen Level
        $currentJabatanLevel = intval($selectedStruktural->level_jsd);

        $allowedAssigneLevel = [$currentJabatanLevel];

        $assigneList = null;
        $assigneUnitIdList = [];

        // Filter By Fakultas & Prodi Start <==
        // Ambil semua jabatan dibawah jabatan target user
        $allJabatanSameAndBellow = SikJabatanStrukDos::where('level_jsd', '>=', $currentJabatanLevel)->get();
        $strukturalDosenId = [];

        for ($i = 0; $i < sizeof($allJabatanSameAndBellow); $i++) {
            array_push($strukturalDosenId, $allJabatanSameAndBellow[$i]->id);
        }

        if ($struktural_id >= 3 && $struktural_id <= 5) {
            $allBawahan = SikBiodata::where('fakultas_id', $fakultasId)->where('status', 4)->where('prodi_id', $selectedProdiId)->where('jabatan_strukdos_id', '>=', $struktural_id)->where('jabatan_strukdos_id', '!=', 6)->get();
        } else {
            if ($currentJabatanLevel <= 1) {
                $allBawahan = SikBiodata::where('fakultas_id', $fakultasId)->whereIn('status', [4, 5])->where('jabatan_strukdos_id', '>=', $struktural_id)->get();
            } else {
                $allBawahan = SikBiodata::where('fakultas_id', $fakultasId)->whereIn('status', [4, 5])->where('prodi_id', $selectedProdiId)->where('jabatan_strukdos_id', '>=', $struktural_id)->get();
            }
        }

        //

        // Only for Debug
        // return response()->json(["al" => $fakultasId]);

        for ($i = 0; $i < sizeof($allBawahan); $i++) {
            array_push($assigneUnitIdList, $allBawahan[$i]->id);
        }

        // If active user jabatan level is 5, just get his/her task instead
        if ($currentJabatanLevel === 5 || $currentJabatanLevel === 6) {
            $realAssigneList = [SikBiodata::find(intval($unit->userId))];
        } else {
            $realAssigneList = SikBiodata::whereIn('id', $assigneUnitIdList)->orderBy('jabatan_strukdos_id')->get();
        }

        for ($i = 0; $i < sizeof($realAssigneList); $i++) {
            $targetProdi = SikProdi::where('id', intval($realAssigneList[$i]->prodi_id))->select('name', 'fakultas_id')->first();
            $prodiName = $targetProdi->name;
            $targetStrukdos = SikJabatanStrukDos::where('id', intval($realAssigneList[$i]->jabatan_strukdos_id))->select('name')->first();
            // $jabatanStrukdosName = $targetStrukdos->name;

            $targetStaffFakultas = SikStaffFakultasModel::find(intval($realAssigneList[$i]->staff_fakultas_id));
            $targetFakultas = SikFakultas::find(intval($realAssigneList[$i]->fakultas_id));

            $realAssigneList[$i]->jabatan_strukdos = $targetStaffFakultas->name;
            $realAssigneList[$i]->nama_fakultas = ucwords($targetFakultas->name);
            $realAssigneList[$i]->nama_prodi = ucwords($targetStaffFakultas->name);
        }

        $assigneUserId = [];

        for ($i = 0; $i < sizeof($realAssigneList); $i++) {
            // if (intval($realAssigneList[$i]->id) !== $activeId) {
            //     array_push($assigneUserId, $realAssigneList[$i]->id);
            // }
            array_push($assigneUserId, [$realAssigneList[$i]->id, $realAssigneList[$i]->fullname]);
        }

        // return response()->json(["test" => $assigneUserId]);

        // $getActiveUserTask = SikKinerjaTask::where('assigned_biodata_id', $activeId)->get();

        $arrayUserTask = [];

        // $startPeriode = calculatePeriod($unit->customDate)[0];

        for ($i = 0; $i < sizeof($assigneUserId); $i++) {
            // if ($isCurrentPeriode) {
            //     $getTask = SikKinerjaDosenTask::where('assigned_biodata_id', $assigneUserId[$i][0])->where('progress_percentage', '<', 100)
            //         ->whereBetween('created_at', ["2024-01-01", $endPeriode])->get();
            // } else {
            //     $getTask = SikKinerjaDosenTask::where('assigned_biodata_id', $assigneUserId[$i][0])->where('progress_percentage', 100)
            //         ->whereBetween('realize_date', [$startPeriode, $endPeriode])->get();
            // }

            $getTask = null;

            //
            $assigneId = $assigneUserId[$i][0];

            if ($isCurrentPeriode) {
                $getTask = SikKinerjaDosenTask::where(function ($query) use ($startPeriode, $endPeriode, $assigneId) {
                    $query->where('assigned_biodata_id', $assigneId)->where('progress_percentage', '<', 100)
                        ->whereBetween('created_at', ["2024-01-01", $endPeriode]);
                })->orWhere(function ($query) use ($startPeriode, $endPeriode, $assigneId) {
                    $query->where('assigned_biodata_id', $assigneId)->where('progress_percentage', 100)
                        ->whereBetween('created_at', [$startPeriode, $endPeriode]);
                })->get();
            } else {
                // $isHere = true;
                $getTask = SikKinerjaDosenTask::where('assigned_biodata_id', $assigneId)->where('progress_percentage', 100)->whereBetween('realize_date', [$startPeriode, $endPeriode])->get();
            }
            //

            $targetBiodata = SikBiodata::find(intval($assigneUserId[$i][0]));

            $targetJabatan = SikJabatanStrukDos::find(intval($targetBiodata->jabatan_strukdos_id));

            if ($getTask) {
                for ($t = 0; $t < sizeof($getTask); $t++) {
                    // Mimic the Task Initial Value in TaskList.jsx
                    $getTask[$t]->assigne_name = $assigneUserId[$i][1];

                    // Ambil Semua Sub Task
                    $getAllSubTask = SikKinerjaSubDosenTask::where('task_id', intval($getTask[$t]->id))->get();

                    $getTask[$t]->childTask = $getAllSubTask ? $getAllSubTask : [];
                    $getTask[$t]->jabatan_level = $targetJabatan->jabatan_level;
                }
            }

            array_push($arrayUserTask, [$assigneUserId[$i][0] => $getTask]);
        }
        // array_push($arrayUserTask, [$activeId => $getActiveUserTask]);

        $sendPayload = ["userTask" => $arrayUserTask, "period" => [$startPeriode, $endPeriode], "assigneList" => $realAssigneList];

        return response()->json($sendPayload);
    }

    public function getTaskFakultyByPeriode(Request $fakultas)
    {
        // Define the Periode Date
        $startPeriode = $fakultas->startd;
        $endPeriode = $fakultas->endd;

        // return response()->json(["periode" => [$startPeriode, $endPeriode, $fakultas->id]]);
        // $activeId = intval(Auth::id());
        // $fakultasId = intval($fakultas->id);
        // $struktural_id = intval($fakultas->struktural_dosen_id);

        // // return response()->json($fakultas);

        // // Model Unit Kerja -> SikUnitKerja() -> unit_id dari user
        // // Model Struktural -> SikJabatanStruktural() -> unit_id dari user
        // // Ambil semua user berdasrkan unit_id
        // // Cek level jabatan struktral
        // // Ambil level dari sama dengan dan kebawah

        // // Determine target Prodi
        // $selectedBiodata = SikBiodata::find($activeId);
        // $selectedProdiId = $selectedBiodata->prodi_id;

        // // Determine Target Fakultas
        // $selectedFakultas = SikFakultas::where('id', intval($fakultas->fakultas_id))->first();
        // $fakultasName = $selectedFakultas->name;
        // $fakultasId = intval($fakultas->fakultas_id);

        // // Determine Struktural Dosen
        // $selectedStruktural = SikJabatanStrukDos::where('id', $struktural_id)->first();

        // // Determine Dosen Level
        // $currentJabatanLevel = intval($selectedStruktural->level_jsd);

        // // return response()->json(["data" => $currentJabatanLevel]);

        // $allowedAssigneLevel = [$currentJabatanLevel];

        // $assigneList = null;
        // $assigneUnitIdList = [];

        // // Filter By Fakultas & Prodi Start <==
        // // Ambil semua jabatan dibawah jabatan target user
        // $allJabatanSameAndBellow = SikJabatanStrukDos::where('level_jsd', '>=', $currentJabatanLevel)->get();
        // $strukturalDosenId = [];

        // for ($i = 0; $i < sizeof($allJabatanSameAndBellow); $i++) {
        //     array_push($strukturalDosenId, $allJabatanSameAndBellow[$i]->id);
        // }

        // // Exclude TU if based rule
        // if ($struktural_id >= 3 && $struktural_id <= 5) {
        //     $allBawahan = SikBiodata::where('fakultas_id', $fakultasId)->where('status', 4)->where('prodi_id', $selectedProdiId)->where('jabatan_strukdos_id', '>=', $struktural_id)->where('jabatan_strukdos_id', '!=', 6)->get();
        // } else {
        //     if ($currentJabatanLevel <= 1) {
        //         $allBawahan = SikBiodata::where('fakultas_id', $fakultasId)->where('status', 4)->where('jabatan_strukdos_id', '>=', $struktural_id)->get();
        //     } else {
        //         $allBawahan = SikBiodata::where('fakultas_id', $fakultasId)->where('status', 4)->where('prodi_id', $selectedProdiId)->where('jabatan_strukdos_id', '>=', $struktural_id)->get();
        //     }
        // }

        // //

        // // Only for Debug
        // // return response()->json($assigneList);

        // for ($i = 0; $i < sizeof($allBawahan); $i++) {
        //     array_push($assigneUnitIdList, $allBawahan[$i]->id);
        // }

        // // If active user jabatan level is 5, just get his/her task instead
        // if ($currentJabatanLevel === 5 || $currentJabatanLevel === 6) {
        //     $realAssigneList = [SikBiodata::find(intval($fakultas->userId))];
        // } else {
        //     $realAssigneList = SikBiodata::whereIn('id', $assigneUnitIdList)->orderBy('jabatan_strukdos_id')->get();
        // }

        $fakultasId = intval($fakultas->id);
        $realAssigneList = SikBiodata::where('fakultas_id', $fakultasId)->where('active', 1)->whereIn('status', [2, 4, 5])->orderBy('jabatan_strukdos_id')->get();

        $realAssigneList = $realAssigneList->sortBy(function ($item) {
            return $item->status == 5 ? 1 : 0;
        })->values();

        for ($i = 0; $i < sizeof($realAssigneList); $i++) {
            if ($realAssigneList[$i]->status == 5) {
                // $targetProdi = SikProdi::where('id', intval($realAssigneList[$i]->prodi_id))->select('name', 'fakultas_id')->first();
                // $prodiName = $targetProdi->name;
                // $targetStrukdos = SikJabatanStrukDos::where('id', intval($realAssigneList[$i]->jabatan_strukdos_id))->select('name')->first();
                // $jabatanStrukdosName = "TU Fakultas";

                $fakultas = SikFakultas::find(intval($realAssigneList[$i]->fakultas_id));
                $fakultasName = $fakultas->name;
                $realAssigneList[$i]->nama_fakultas = ucwords($fakultasName);

                // Special Requirement, for status = 5, bellow code has to be this
                $targetStaffFakultas = SikStaffFakultasModel::find(intval($realAssigneList[$i]->staff_fakultas_id));

                $realAssigneList[$i]->jabatan_strukdos = $targetStaffFakultas->name;
                $realAssigneList[$i]->nama_prodi = $fakultas->name;
            } else {
                $targetProdi = SikProdi::where('id', intval($realAssigneList[$i]->prodi_id))->select('name', 'fakultas_id')->first();
                $prodiName = $targetProdi->name;
                $targetStrukdos = SikJabatanStrukDos::where('id', intval($realAssigneList[$i]->jabatan_strukdos_id))->select('name')->first();
                $jabatanStrukdosName = $targetStrukdos->name;

                $realAssigneList[$i]->jabatan_strukdos = $jabatanStrukdosName;
                $realAssigneList[$i]->nama_prodi = ucwords($prodiName);

                $fakultas = SikFakultas::find(intval($targetProdi->fakultas_id));
                $fakultasName = $fakultas->name;
                $realAssigneList[$i]->nama_fakultas = ucwords($fakultasName);
            }
        }

        // return response()->json($fakultas->userId);

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
            // if ($fakultas->has('customDate')) {
            //     // Define the Periode Date
            //     $startPeriode = calculatePeriod($fakultas->customDate)[0];
            //     $endPeriode = calculatePeriod($fakultas->customDate)[1];
            // }

            // Get The Data within Periode Date
            // $getTask = SikKinerjaTask::where('assigned_biodata_id', $assigneUserId[$i][0])->where('progress_percentage', '<', 100)
            //     ->whereBetween('created_at', [$startPeriode, $endPeriode])->get();

            // $getTask = SikKinerjaTask::where('assigned_biodata_id', $assigneUserId[$i][0])->where('progress_percentage', '<', 100)
            //     ->orWhere('created_at', [$startPeriode, $endPeriode])->get();

            $buffAssigneUnitId = $assigneUserId[$i][0];

            // $getTask = SikKinerjaDosenTask::where(function ($query) use ($buffAssigneUnitId, $startPeriode, $endPeriode) {
            //     $query->where('assigned_biodata_id', $buffAssigneUnitId)
            //         ->whereRaw("DATE(created_at) BETWEEN ? AND ?", [$startPeriode, $endPeriode]);
            // })
            //     ->orWhere(function ($query) use ($buffAssigneUnitId) {
            //         $query->where('assigned_biodata_id', $buffAssigneUnitId)
            //             ->where('progress_percentage', '<', 100);
            //     })
            //     ->get();

            // $getTask = SikKinerjaDosenTask::where('assigned_biodata_id', $buffAssigneUnitId)->where('progress_percentage', 100)
            //     ->where("created_at", "<=", $startPeriode)->get();

            $getTask = SikKinerjaDosenTask::where('assigned_biodata_id', $buffAssigneUnitId)->whereRaw("DATE(created_at) BETWEEN ? AND ?", [$startPeriode, $endPeriode])->get();

            $targetBiodata = SikBiodata::find(intval($assigneUserId[$i][0]));
            $targetProdi = SikProdi::where('id', $targetBiodata->prodi_id)->select('name')->first();

            $targetJabatan = SikJabatanStrukDos::find(intval($targetBiodata->jabatan_strukdos_id));

            if ($getTask) {
                for ($t = 0; $t < sizeof($getTask); $t++) {
                    // Mimic the Task Initial Value in TaskList.jsx
                    $getTask[$t]->assigne_name = $assigneUserId[$i][1];

                    // Ambil Semua Sub Task
                    $getAllSubTask = SikKinerjaSubDosenTask::where('task_id', intval($getTask[$t]->id))->get();

                    // Get the latest due date from Sub Task
                    $getFirstLatestDate = SikKinerjaSubDosenTask::where('task_id', intval($getTask[$t]->id))->latest('due_date')->first();

                    if ($getFirstLatestDate) {
                        $getTask[$t]->latest_date = $getFirstLatestDate->due_date;
                    } else {
                        $getTask[$t]->latest_date = Carbon::now()->format("Y-m-d");
                    }

                    $getTask[$t]->childTask = $getAllSubTask ? $getAllSubTask : [];
                    $getTask[$t]->jabatan_level = $targetJabatan->level_jsd;

                    $getTask[$t]->is_late = false;

                    if (checkTaskLateNow($getTask[$t]->periode_start)) {
                        $getTask[$t]->is_late = true;
                    }
                }
            }

            array_push($arrayUserTask, [$assigneUserId[$i][0] => $getTask]);
        }
        // array_push($arrayUserTask, [$activeId => $getActiveUserTask]);
        // $capture = [
        //     "id" => $strukturalDosenId,
        //     "userId" => $fakultas->userId,
        //     "struktural_dosen_id" => $fakultas->struktural_dosen_id,
        //     "fakultas_id" => $fakultas->fakultas_id,
        //     "customDate" => $fakultas->customDate
        // ];

        $sendPayload = [
            "success" => true,
            "unit" => $fakultasId,
            "assigneList" => $realAssigneList,
            "userTask" => $arrayUserTask,
            "periode" => [$startPeriode, $endPeriode],
            "debug" => $realAssigneList
        ];
        // $sendPayload = ["unit" => $fakultas];

        return response()->json($sendPayload);
    }
}
