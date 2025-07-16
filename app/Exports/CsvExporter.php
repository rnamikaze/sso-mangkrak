<?php

namespace App\Exports;

use App\Models\SKU\SkuLevelSurvey;
use App\Models\SKU\SkuPersonData;
use App\Models\SKU\SkuSurvey;
use App\Models\SKU\SkuUnit;
use Carbon\Carbon;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CsvExporter implements FromCollection, WithHeadings
{
    public $selected_unit;
    public $start_date;
    public $end_date;
    public $komentar;

    function __construct($selected_unit, $start_date, $end_date)
    {
        $this->selected_unit = $selected_unit;
        $this->start_date = $start_date;
        $this->end_date = $end_date;
    }

    public function collection()
    {
        $start_date = Carbon::createFromFormat('d-m-Y', $this->start_date)->startOfDay();
        $end_date = Carbon::createFromFormat('d-m-Y', $this->end_date)->endOfDay();

        if (intval($this->selected_unit) === 7777) {
            $getSurvey = SkuSurvey::whereBetween('created_at', [$start_date, $end_date])->get();
        } else {
            $getSurvey = SkuSurvey::where('kode_unit_id', intval($this->selected_unit))->whereBetween('created_at', [$start_date, $end_date])->get();
        }
        // $getSurvey = Survey::all();

        $dataNama = SkuPersonData::all();
        $dataLevel = SkuLevelSurvey::all();

        $units = SkuUnit::all();


        for ($i = 0; $i < sizeof($getSurvey); $i++) {
            $getSurvey[$i]['formated_date'] = date('H:i:s d-m-Y', strtotime($getSurvey[$i]['created_at']));
            for ($j = 0; $j < sizeof($dataNama); $j++) {
                if (intval($dataNama[$j]['id']) === intval($getSurvey[$i]['nama_id'])) {
                    $getSurvey[$i]['nama_text'] = $dataNama[$j]['nama'];
                }
            }
            for ($k = 0; $k < sizeof($units); $k++) {
                if (intval($units[$k]['id']) === intval($getSurvey[$i]['kode_unit_id'])) {
                    $getSurvey[$i]['nama_unit'] = $units[$k]['nama_unit'];
                }
            }
            for ($m = 0; $m < sizeof($dataLevel); $m++) {
                if (intval($dataLevel[$m]['id']) === intval($getSurvey[$i]['level_survey_id'])) {
                    $getSurvey[$i]['survey_level'] = ucwords($dataLevel[$m]['nama_level_survey']);
                }
            }
        }

        // If you want to transform the data, you can use the map function
        return $getSurvey->map(function ($dataSurvey) {
            return [
                'Custom Column 1' => $dataSurvey->formated_date,
                'Custom Column 2' => $dataSurvey->nama_unit,
                'Custom Column 3' => $dataSurvey->nama_text,
                'Custom Column 4' => $dataSurvey->survey_level,
                'Custom Column 5' => $dataSurvey->komentar,
            ];
        });
    }

    public function headings(): array
    {
        // Custom headers for each column
        return [
            'Waktu/Tanggal',
            'Unit',
            'Nama',
            'Respon',
            'Komentar'
        ];
    }
}
