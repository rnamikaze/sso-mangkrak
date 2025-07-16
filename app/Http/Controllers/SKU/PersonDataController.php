<?php

namespace App\Http\Controllers\SKU;

use Inertia\Inertia;
use App\Models\PersonData;
use App\Models\SKU\SkuUnit;
use Illuminate\Http\Request;
use App\Models\SKU\SkuSurvey;
use App\Models\SKU\SkuPersonData;
use App\Http\Controllers\Controller;

class PersonDataController extends Controller
{
    //
    public function getDataSummary()
    {
        // Get all data from the table
        $data = SkuSurvey::all();

        $units = SkuUnit::all();

        // Count the appearance of each level_survey_id in the entire table
        $totalCount = $data->groupBy('level_survey_id')->map->count();

        return Inertia::render('SKU/Index', ['totalSum' => $totalCount, 'units' => $units]);
    }
    public function getPersonData()
    {
        $personData = SkuPersonData::all();
        $units = SkuUnit::all();

        // Get all data from the table
        $data = SkuSurvey::all();

        // // Count the appearance of each level_survey_id in the entire table
        // $totalCount = $data->groupBy('level_survey_id')->map->count();

        // Count the appearance of each level_survey_id based on nama_id
        $individualCounts = $data->groupBy('nama_id')->map(function ($group, $namaId) {
            $counts = [intval($namaId)]; // Start the array with nama_id

            // Loop through values 1, 2, and 3
            foreach (range(1, 3) as $value) {
                $count = $group->where('level_survey_id', $value)->count();
                $counts[] = $count;
            }

            return $counts;
        })->values()->all();

        // $surveyResult = [$totalCount, $individualCounts];

        $resultTemp = [[0, 0], [0, 0], [0, 0]];

        for ($i = 0; $i < sizeof($personData); $i++) {
            $nama_unit = "";
            for ($u = 0; $u < sizeof($units); $u++) {
                if (intval($personData[$i]->kode_unit_id) === intval($units[$u]->id)) {
                    $nama_unit = $units[$u]->nama_unit;
                }
            }
            $personData[$i]['nama_unit'] = $nama_unit;
            $personData[$i]['kelamin_x'] = [
                $personData[$i]->kelamin === 0 ? "perempuan" : "laki - laki",
                $personData[$i]->kelamin === 0 ? "p" : "l",
            ];
            $tmp = date('d-m-Y', strtotime($personData[$i]->updated_at));
            $personData[$i]['formated_date'] = $tmp;
            $personData[$i]['numbering'] = $i + 1;
            $personData[$i]['adder'] = "adder";
            // $personData[$i]['survey_results'] = $surveyResult;

            $found = false;
            for ($z = 0; $z < sizeof($individualCounts); $z++) {
                if ($individualCounts[$z][0] === $personData[$i]->id) {
                    $found = true;
                    // Calculate the total sum of the array
                    $array = [
                        intval($individualCounts[$z][1]),
                        intval($individualCounts[$z][2]),
                        intval($individualCounts[$z][3])
                    ];

                    $total = array_sum($array);

                    // Calculate the percentage for each element
                    $percentageArray = array_map(function ($value) use ($total) {
                        return ($value / $total) * 100;
                    }, $array);

                    $personData[$i]['survey_results'] = [
                        [$individualCounts[$z][1], $percentageArray[0]],
                        [$individualCounts[$z][2], $percentageArray[1]],
                        [$individualCounts[$z][3], $percentageArray[2]]
                    ];
                }
                if ($found === false) {
                    $personData[$i]['survey_results'] = $resultTemp;
                }
            }

            // for ($sv = 0; $sv < $surveyResult; $sv++) {
            //     if ($surveyResult[$sv][0] === $personData->id) {
            //         $personData[$i]['survey_results'] = $surveyResult[$sv];
            //     }
            // }
        }

        // return response()->json($personData);
        return Inertia::render("SKU/Index", ['personData' => $personData, 'units' => $units]);
    }

    public function getPersonDataFilter($startDate, $endDate)
    {

        $startDate = \Carbon\Carbon::createFromFormat('d-m-Y', $startDate)->startOfDay();
        $endDate = \Carbon\Carbon::createFromFormat('d-m-Y', $endDate)->endOfDay();

        $personData = SkuPersonData::all();

        // $personData = PersonData::all();
        $units = SkuUnit::all();

        // Get all data from the table
        $data = SkuSurvey::whereBetween('created_at', [$startDate, $endDate])->get();

        // Count the appearance of each level_survey_id in the entire table
        $totalCount = $data->groupBy('level_survey_id')->map->count();

        // Count the appearance of each level_survey_id based on nama_id
        $individualCounts = $data->groupBy('nama_id')->map(function ($group, $namaId) {
            $counts = [intval($namaId)]; // Start the array with nama_id

            // Loop through values 1, 2, and 3
            foreach (range(1, 3) as $value) {
                $count = $group->where('level_survey_id', $value)->count();
                $counts[] = $count;
            }

            return $counts;
        })->values()->all();

        // $surveyResult = [$totalCount, $individualCounts];

        $resultTemp = [[0, 0], [0, 0], [0, 0]];

        for ($i = 0; $i < sizeof($personData); $i++) {
            $nama_unit = "";
            for ($u = 0; $u < sizeof($units); $u++) {
                if (intval($personData[$i]->kode_unit_id) === intval($units[$u]->id)) {
                    $nama_unit = $units[$u]->nama_unit;
                }
            }
            $personData[$i]['nama_unit'] = $nama_unit;
            $personData[$i]['kelamin_x'] = [
                $personData[$i]->kelamin === 0 ? "perempuan" : "laki - laki",
                $personData[$i]->kelamin === 0 ? "p" : "l",
            ];
            $tmp = date('d-m-Y', strtotime($personData[$i]->updated_at));
            $personData[$i]['formated_date'] = $tmp;
            $personData[$i]['numbering'] = $i + 1;
            // $personData[$i]['survey_results'] = $surveyResult;

            $found = false;
            for ($z = 0; $z < sizeof($individualCounts); $z++) {
                if ($individualCounts[$z][0] === $personData[$i]->id) {
                    $found = true;
                    // Calculate the total sum of the array
                    $array = [
                        intval($individualCounts[$z][1]),
                        intval($individualCounts[$z][2]),
                        intval($individualCounts[$z][3])
                    ];

                    $total = array_sum($array);

                    // Calculate the percentage for each element
                    $percentageArray = array_map(function ($value) use ($total) {
                        return ($value / $total) * 100;
                    }, $array);

                    $personData[$i]['survey_results'] = [
                        [$individualCounts[$z][1], $percentageArray[0]],
                        [$individualCounts[$z][2], $percentageArray[1]],
                        [$individualCounts[$z][3], $percentageArray[2]]
                    ];
                }
                if ($found === false) {
                    $personData[$i]['survey_results'] = $resultTemp;
                }
            }
        }

        // return response()->json($personData);
        return Inertia::render("SKU/Index", ['personData' => $personData, 'units' => $units]);
    }

    public function getNamaPersonDanUnit()
    {
        $personData = SkuPersonData::select('id', 'nama', 'kode_unit_id')->get();;
        $units = SkuUnit::all();

        // return response()->json($personData);
        return Inertia::render("SKU/Index", ['personData' => $personData, 'units' => $units]);
    }
}
