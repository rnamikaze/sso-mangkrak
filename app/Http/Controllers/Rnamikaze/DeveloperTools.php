<?php

namespace App\Http\Controllers\Rnamikaze;

use Inertia\Inertia;
use App\Models\LoginLoger;
use Jenssegers\Agent\Agent;
use Illuminate\Http\Request;
use App\Models\FailedLoginAttempt;
use App\Http\Controllers\Controller;
use App\Models\StrangerCounter;
use App\Models\SupabaseVisitorLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DeveloperTools extends Controller
{
    //
    public function home()
    {
        return Inertia::render('SuperDev/MainSuperDev');
    }

    public function parkedLogin()
    {
        if (Auth::check() === false) {
            return to_route('sso.ssoLoginWarning');
        }

        // Retrieve the authenticated user
        $user = Auth::user();

        // Get the username
        $username = $user->nik;
        if ($username === "3515132411980001") {
            return Inertia::render('SuperDev/ParkedLogin');
        }

        Auth::logout();
        return to_route('sso.login');
    }

    public function showFailedLoginAttempt(Request $req, ?string $secret = null)
    {
        $filterMatch = null;

        if ($req->has('filterMatch')) {
            $filterMatchVal = intval($req->query('filterMatch'));

            if ($filterMatchVal === 1) {
                $filterMatch = false;
            } else if ($filterMatchVal === 0) {
                $filterMatch = true;
            } else {
                $filterMatch = null;
            }
        }

        // Temporary Secret Code Access
        if ($secret === "sooyaaa__") {
            $allData = FailedLoginAttempt::select('login_dump', 'date_id')->get();

            $dump = [];

            for ($i = 0; $i < sizeof($allData); $i++) {
                $dataDump = json_decode($allData[$i]->login_dump);
                $removeIndex = [];

                if ($filterMatch !== null) {
                    for ($jj = 0; $jj < sizeof($dataDump); $jj++) {
                        if (boolval($dataDump[$jj]->match) === $filterMatch) {
                            // unset($dataDump[$jj]);
                            // $removeIndex = $jj;
                            array_push($removeIndex, $jj);
                        }
                    }
                }

                foreach ($removeIndex as $index) {
                    unset($dataDump[$index]);
                }

                $buff = [
                    "date_id" => $allData[$i]->date_id,
                    "login_dump" => $dataDump,
                ];

                array_push($dump, $buff);
            }

            return response()->json($dump);
        }

        return response()->json(["message" => "Secret Code Incorrect !"]);
    }

    public function getPostFailedLoginAttempt(Request $data)
    {
        $secretToken = "sooyaaa__";

        $payload = [
            'success' => false
        ];

        if ($data->has('token') === false) {
            $payload['reason'] = "Secret Token Not Found";

            return response()->json($payload);
        } else {
            if ($data->token !== $secretToken) {
                $payload['reason'] = "Secret Token Incorrect";

                return response()->json($payload);
            }
        }

        $filterMatch = null;

        if ($data->has('filterMatch')) {
            if (!is_null($data->filterMatch)) $filterMatch = !boolval($data->filterMatch);
        }

        $allData = FailedLoginAttempt::select('login_dump', 'date_id')->orderBy('updated_at', 'desc')->get();

        $dump = [];

        for ($i = 0; $i < sizeof($allData); $i++) {
            $dataDump = json_decode($allData[$i]->login_dump);
            $removeIndex = [];

            if ($filterMatch !== null) {
                for ($jj = 0; $jj < sizeof($dataDump); $jj++) {
                    if (boolval($dataDump[$jj]->match) === $filterMatch) {
                        // unset($dataDump[$jj]);
                        // $removeIndex = $jj;
                        array_push($removeIndex, $jj);
                    }
                }
            }

            foreach ($removeIndex as $index) {
                unset($dataDump[$index]);
            }

            if ($filterMatch !== null) {
                $dataDump = array_values($dataDump);
            }

            usort($dataDump, function ($a, $b) {
                return strtotime($b->attempt) - strtotime($a->attempt);
            });

            $buff = [
                "date_id" => $allData[$i]->date_id,
                "login_dump" => $dataDump,
            ];

            array_push($dump, $buff);
        }

        $payload['success'] = true;
        $payload['loger'] = $dump;

        // $rawJson = '
        // {"email_username":"3515132411980001","attempt":"15:43:03 2024-06-08","password":"NULL","match":true,"browser":"Firefox","device_type":"Desktop","ip":"127.0.0.1","port":8000,"lat":-7.2484,"lon":112.7419}
        // ';

        return response()->json($payload);
    }

    public function getSuccessLoger(Request $data)
    {
        $secretToken = "sooyaaa__";

        $payload = [
            'success' => false
        ];

        if ($data->has('token') === false) {
            $payload['reason'] = "Secret Token Not Found";

            return response()->json($payload);
        } else {
            if ($data->token !== $secretToken) {
                $payload['reason'] = "Secret Token Incorrect";

                return response()->json($payload);
            }
        }

        $sort = null;
        $limitRow = 25;

        if ($data->has('sort')) {
            if (!is_null($data->sort)) $sort = boolval($data->sort);
        }

        if ($data->has('limit')) {
            $limitRow = 25 * intval($data->limit);
        }

        $sortMode = $sort ? 'desc' : 'asc';

        $allLoger = LoginLoger::orderBy('id', $sortMode)
            ->select('created_at', 'master_id', 'action', 'ip_log', 'user_agent', 'device_name', 'browser_name')
            ->take($limitRow)->get();

        for ($i = 0; $i < sizeof($allLoger); $i++) {
            $allLoger[$i]->fomated_date = Carbon::parse($allLoger[$i]->created_at)->format('H:i:s Y-m-d');
        }

        // Group the data by the formatted 'created_at' date
        $groupData = $allLoger->groupBy(function ($item) {
            return Carbon::parse($item->created_at)->format('Y-m-d');
        });

        // Format the grouped data to the desired structure
        $groupDataArray = $groupData->map(function ($data, $date) {
            return [
                'date_id' => $date,
                'data' => $data,
            ];
        })->values()->toArray();

        $payload['success'] = true;
        $payload['reason'] = "Data has been successfully retrieved.";
        $payload['loger'] = $groupDataArray;
        // $payload['debug'] = $data->limit;

        return response()->json($payload);
    }

    public function getStrangerLog(Request $data)
    {
        $secretToken = "sooyaaa__";

        $payload = [
            'success' => false
        ];

        if ($data->has('token') === false) {
            $payload['reason'] = "Secret Token Not Found";

            return response()->json($payload);
        } else {
            if ($data->token !== $secretToken) {
                $payload['reason'] = "Secret Token Incorrect";

                return response()->json($payload);
            }
        }

        $sort = null;
        $limitRow = 25;

        if ($data->has('sort')) {
            if (!is_null($data->sort)) $sort = boolval($data->sort);
        }

        if ($data->has('limit')) {
            $limitRow = 25 * intval($data->limit);
        }

        $sortMode = $sort ? 'desc' : 'asc';

        $allLoger = SupabaseVisitorLog::orderBy('created_at', $sortMode)
            ->select('created_at', 'ip_address', 'user_agent', 'browser', 'device_type', 'is_robot', 'is_mobile', 'platform')
            ->take($limitRow)->get();

        for ($i = 0; $i < sizeof($allLoger); $i++) {
            if ($allLoger[$i]->is_robot === 'false') {
                $allLoger[$i]->is_robot_bool = false;
            } else {
                $allLoger[$i]->is_robot_bool = true;
            }

            if ($allLoger[$i]->is_mobile === 'false') {
                $allLoger[$i]->is_mobile_bool = false;
            } else {
                $allLoger[$i]->is_mobile_bool = true;
            }

            $allLoger[$i]->fomated_date = Carbon::parse($allLoger[$i]->created_at)->format('H:i:s Y-m-d');
        }

        // Group the data by the formatted 'created_at' date
        $groupData = $allLoger->groupBy(function ($item) {
            return Carbon::parse($item->created_at)->format('Y-m-d');
        });

        // Format the grouped data to the desired structure
        $groupDataArray = $groupData->map(function ($data, $date) {
            return [
                'date_id' => $date,
                'data' => $data,
            ];
        })->values()->toArray();

        $payload['success'] = true;
        $payload['reason'] = "Data has been successfully retrieved.";
        $payload['loger'] = $groupDataArray;
        // $payload['debug'] = $data->limit;

        return response()->json($payload);
    }

    public function supabaseHandleData(Request $data)
    {
        $validated = $data->validate([
            "raw_data" => "required|json"
        ]);

        $logData = json_decode($validated['raw_data']);

        $allLoger = collect($logData);

        // return response()->json($logData);

        for ($i = 0; $i < sizeof($allLoger); $i++) {
            if ($allLoger[$i]->is_robot === 'false') {
                $allLoger[$i]->is_robot_bool = false;
            } else {
                $allLoger[$i]->is_robot_bool = true;
            }

            if ($allLoger[$i]->is_mobile === 'false') {
                $allLoger[$i]->is_mobile_bool = false;
            } else {
                $allLoger[$i]->is_mobile_bool = true;
            }

            $allLoger[$i]->fomated_date = Carbon::parse($allLoger[$i]->created_at)->format('H:i:s Y-m-d');
        }

        // Group the data by the formatted 'created_at' date
        $groupData = $allLoger->groupBy(function ($item) {
            return Carbon::parse($item->created_at)->format('Y-m-d');
        });

        // Format the grouped data to the desired structure
        $groupDataArray = $groupData->map(function ($data, $date) {
            return [
                'date_id' => $date,
                'data' => $data,
            ];
        })->values()->toArray();

        $payload['success'] = true;
        $payload['reason'] = "Data has been successfully retrieved.";
        $payload['loger'] = $groupDataArray;
        // $payload['debug'] = $data->limit;

        return response()->json($payload);
    }
}
