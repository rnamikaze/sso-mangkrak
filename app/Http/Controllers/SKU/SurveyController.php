<?php

namespace App\Http\Controllers\SKU;

use Carbon\Carbon;
use Inertia\Inertia;
use App\Models\SKU\SkuUnit;
use App\Exports\CsvExporter;
use Illuminate\Http\Request;
use App\Models\SKU\SkuSurvey;
use App\Models\SKU\SkuPersonData;
use App\Models\SKU\SkuLevelSurvey;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class SurveyController extends Controller
{
    //
    public function sendSurvey(Request $req)
    {
        $survey = new SkuSurvey();

        $survey->nama_id = $req->input('nama_id');
        $survey->kode_unit_id = $req->input('kode_unit_id');
        $survey->level_survey_id = $req->input('level_survey_id');
        $komentar = $req->input('komentar') === null ? "Tidak di isi." : $req->input('komentar');
        $survey->komentar = $komentar;

        $survey->save();

        return redirect()->route('sku.guest');
    }

    public function getDataSurvey($nama_id, $startDate = null, $endDate = null)
    {
        if ($startDate === null) {
            $getSurvey = SkuSurvey::where('nama_id', $nama_id)->get();
        } else {
            $startDate = \Carbon\Carbon::createFromFormat('d-m-Y', $startDate)->startOfDay();
            $endDate = \Carbon\Carbon::createFromFormat('d-m-Y', $endDate)->endOfDay();
            $getSurvey = SkuSurvey::where('nama_id', $nama_id)->whereBetween('created_at', [$startDate, $endDate])->get();
        }

        $selectedPerson = SkuPersonData::where('id', $nama_id)->first();
        $satisfyLevel = SkuLevelSurvey::all();
        $personData = SkuPersonData::select('id', 'nama', 'kode_unit_id')->get();;
        $units = SkuUnit::all();
        $units_x = SkuUnit::where('id', $selectedPerson->kode_unit_id)->first();

        // for ($i = 0; $i < sizeof($getSurvey); $i++) {
        //     $tmp = date('H:i:s d-m-Y', strtotime($getSurvey[$i]->updated_at));
        //     $getSurvey[$i]['formated_date'] = $tmp;

        //     // array_push($lalala, $getSurvey[$i]);
        // }

        for ($i = 0; $i < sizeof($getSurvey); $i++) {
            $tmp = date('H:i:s d-m-Y', strtotime($getSurvey[$i]->updated_at));
            $getSurvey[$i]['formated_date'] = $tmp;
            $getSurvey[$i]->nama_f = $selectedPerson->nama;
            $getSurvey[$i]->nama_unit_f = $units_x->nama_unit;

            for ($u = 0; $u < sizeof($satisfyLevel); $u++) {
                if ($getSurvey[$i]->level_survey_id == $satisfyLevel[$u]->id)
                    $getSurvey[$i]->nama_survey_f = $satisfyLevel[$u]->nama_level_survey;
            }

            // array_push($lalala, $getSurvey[$i]);
        }



        return Inertia::render('SKU/Index', [
            'dataSurveyBerdasarkanNama' => $getSurvey,
            'personData' => $personData,
            'units' => $units,
            'personAndSatisfy' => [$selectedPerson, $satisfyLevel]
        ]);
    }
    public function getDataSummary()
    {
        // Get all data from the table
        $data = SkuSurvey::all();

        $units = SkuUnit::all();

        // Count the appearance of each level_survey_id in the entire table
        $totalCount = $data->groupBy('level_survey_id')->map->count();

        return Inertia::render('SKU/Index', ['totalSum' => $totalCount, 'units' => $units]);
    }

    public function getDataSummaryFilter($unit, $startDate, $endDate)
    {
        $startDate = \Carbon\Carbon::createFromFormat('d-m-Y', $startDate)->startOfDay();
        $endDate = \Carbon\Carbon::createFromFormat('d-m-Y', $endDate)->endOfDay();
        $byUnit = intval($unit);

        // Get all data from the table
        $data = SkuSurvey::where('kode_unit_id', $unit)->whereBetween('created_at', [$startDate, $endDate])->get();

        $units = SkuUnit::all();

        // Count the appearance of each level_survey_id in the entire table
        $totalCount = $data->groupBy('level_survey_id')->map->count();

        return Inertia::render('SKU/Index', ['totalSum' => $totalCount, 'units' => $units]);
    }

    public function doExport($selected_unit, $startDate = null, $endDate = null)
    {
        $daysNow = Carbon::now()->timezone('Asia/Jakarta')->day;
        $monthNow = Carbon::now()->timezone('Asia/Jakarta')->month;
        $yearNow = Carbon::now()->timezone('Asia/Jakarta')->year;

        if ($startDate === null) {
            $getSurvey = SkuSurvey::all();
        } else {
            $start_date = Carbon::createFromFormat('d-m-Y', $startDate)->startOfDay();
            $end_date = Carbon::createFromFormat('d-m-Y', $endDate)->endOfDay();

            if (intval($selected_unit) === 7777) {
                $getSurvey = SkuSurvey::whereBetween('created_at', [$start_date, $end_date])->get();
            } else {
                $getSurvey = SkuSurvey::where('kode_unit_id', intval($selected_unit))->whereBetween('created_at', [$start_date, $end_date])->get();
            }
        }


        // $dataNama = PersonData::all();
        // $dataLevel = LevelSurvey::all();


        // // $selectedPerson = PersonData::where('id', $nama_id)->first();
        // // $satisfyLevel = LevelSurvey::all();
        // // $personData = PersonData::select('id', 'nama', 'kode_unit_id')->get();;
        $units = SkuUnit::all();
        // // $units_x = Unit::where('id', $selectedPerson->kode_unit_id)->first();



        // for ($i = 0; $i < sizeof($getSurvey); $i++) {
        //     $getSurvey[$i]['formated_date'] = date('H:i:s d-m-Y', strtotime($getSurvey[$i]['created_at']));
        //     for ($j = 0; $j < sizeof($dataNama); $j++) {
        //         if (intval($dataNama[$j]['id']) === intval($getSurvey[$i]['nama_id'])) {
        //             $getSurvey[$i]['nama_text'] = $dataNama[$j]['nama'];
        //         }
        //     }
        //     for ($k = 0; $k < sizeof($units); $k++) {
        //         if (intval($units[$k]['id']) === intval($getSurvey[$i]['kode_unit_id'])) {
        //             $getSurvey[$i]['nama_unit'] = $units[$k]['nama_unit'];
        //         }
        //     }
        //     for ($m = 0; $m < sizeof($dataLevel); $m++) {
        //         if (intval($dataLevel[$m]['id']) === intval($getSurvey[$i]['level_survey_id'])) {
        //             $getSurvey[$i]['survey_level'] = ucwords($dataLevel[$m]['nama_level_survey']);
        //         }
        //     }
        // }

        return Inertia::render('SKU/Index', [
            'units' => $units,
            'personData' => $getSurvey,
        ]);
    }

    public function downloadExport($selected_unit, $startDate = null, $endDate = null)
    {
        $fileName = "Export_";
        $format = ".xlsx";

        if ($startDate === null) {
            $dateNow = now("Asia/Jakarta")->format('d-m-Y');
            $fileName .= $dateNow .= $format;
        } else {
            // $start_d = \Carbon\Carbon::createFromFormat('d-m-Y', $startDate)->startOfDay();
            // $end_d = \Carbon\Carbon::createFromFormat('d-m-Y', $endDate)->endOfDay();
            $fileName = "Export_" . $startDate . "_" . $endDate . ".xlsx";
        }

        return Excel::download(
            new CsvExporter($selected_unit, $startDate, $endDate),
            $fileName
        );
    }
}
