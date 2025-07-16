<?php

namespace App\Http\Controllers\SPMB;

use Carbon\Carbon;
use League\Csv\Writer;
use Illuminate\Http\Request;
use App\Charts\FeedbackPieChart;
use App\Models\SPMB\SpmbPollData;
use App\Http\Controllers\Controller;
use App\Models\SpmbSettings;
use Illuminate\Support\Facades\Response;

// namespace App\Http\Controllers;

// use Illuminate\Http\Request;
// use App\Charts\FeedbackPieChart;
// use Carbon\Carbon;
// use App\Models\PollData;
// use Illuminate\Support\Facades\Response;
// use League\Csv\Writer;

function formatDateIndo($inputDate)
{
    // Parse the input date using Carbon
    $carbonDate = Carbon::createFromFormat('Y-m-d', $inputDate);

    // Format the date in yyyy-mm-dd format
    $formattedDate = $carbonDate->format('d/m/Y');

    // Return the formatted date
    return $formattedDate;
}

function getTotalPol($month, $year)
{
    // Filter data based on the specified month and poll_code
    $filteredData = SpmbPollData::whereMonth('created_at', '=', $month)
        ->whereYear('created_at', '=', $year)
        ->where('poll_code', '=', 1)
        ->get();

    // Count the occurrences of poll_code with value 1 in the filtered data
    $kurangPuas = $filteredData->where('poll_code', 1)->count();

    // Filter data based on the specified month and poll_code
    $filteredData = SpmbPollData::whereMonth('created_at', '=', $month)
        ->whereYear('created_at', '=', $year)
        ->where('poll_code', '=', 2)
        ->get();

    // Count the occurrences of poll_code with value 1 in the filtered data
    $puas = $filteredData->where('poll_code', 2)->count();

    // Filter data based on the specified month and poll_code
    $filteredData = SpmbPollData::whereMonth('created_at', '=', $month)
        ->whereYear('created_at', '=', $year)
        ->where('poll_code', '=', 3)
        ->get();

    // Count the occurrences of poll_code with value 1 in the filtered data
    $sangatPuas = $filteredData->where('poll_code', 3)->count();

    return [$kurangPuas, $puas, $sangatPuas];
}

class DashboardControllers extends Controller
{
    public function index(FeedbackPieChart $chart)
    {
        return redirect('/spmb/admin-bulanan');
    }

    public function adminHarian()
    {
        $bulanIndonesia = [
            'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember'
        ];
        $daysOfTheMonthNow = intval(Carbon::now()->timezone('Asia/Jakarta')->daysInMonth);
        $daysNow = Carbon::now()->timezone('Asia/Jakarta')->day;
        $monthNow = Carbon::now()->timezone('Asia/Jakarta')->month;
        $yearNow = Carbon::now()->timezone('Asia/Jakarta')->year;

        $filteredData = SpmbPollData::whereDate('created_at', '=', "$yearNow-$monthNow-$daysNow")
            ->orderBy('created_at', 'desc')
            ->get();

        return view('home.harian', [
            'bulanIndonesia' => $bulanIndonesia,
            'dateNow' => [$daysOfTheMonthNow, $daysNow, $monthNow, $yearNow],
            'harianData' => $filteredData
        ]);
    }

    public function adminHarianFilter(Request $req)
    {
        $bulanIndonesia = [
            'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember'
        ];
        $daysOfTheMonthNow = intval(Carbon::now()->timezone('Asia/Jakarta')->daysInMonth);
        $daysNow = intval($req->input('hari-value'));
        $monthNow = $req->input('bulan-value');
        $yearNow = intval($req->input('tahun-value'));

        $filteredData = SpmbPollData::whereDate('created_at', '=', "$yearNow-$monthNow-$daysNow")
            ->orderBy('created_at', 'desc')
            ->get();

        return view('home.harian', [
            'bulanIndonesia' => $bulanIndonesia,
            'dateNow' => [$daysOfTheMonthNow, $daysNow, $monthNow, $yearNow],
            'harianData' => $filteredData
        ]);
    }

    public function adminBulanan(FeedbackPieChart $chart)
    {
        $bulanIndonesia = [
            'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember'
        ];
        $monthNow = Carbon::now()->timezone('Asia/Jakarta')->month;
        $yearNow = Carbon::now()->timezone('Asia/Jakarta')->year;
        $dataPoll = getTotalPol($monthNow, $yearNow);

        $selectedPeriode = SpmbSettings::where('selected', 1)->first();

        // $selectedPoll = SpmbPollData::all();

        $selectedPoll = SpmbPollData::whereBetween('created_at', [$selectedPeriode->periode_start, $selectedPeriode->periode_end])->get();

        $periode = SpmbSettings::where('selected', 1)->first();

        $periode->periode_text = formatDateIndo($periode->periode_start) . " - " . formatDateIndo($periode->periode_end);

        // return array_sum($dataPoll);

        return view('home.bulanan', [
            'chart' => $chart->build($monthNow, $yearNow, 'Survey Kepuasan Layanan PMB.'),
            'bulanIndonesia' => $bulanIndonesia,
            'dateNow' => [$monthNow, $yearNow],
            'dataPoll' => $dataPoll,
            'periodePoll' => $selectedPoll,
            'periode' => $periode
        ]);
    }

    public function adminBulananFilter(FeedbackPieChart $chart, Request $req)
    {

        $bulanIndonesia = [
            'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember'
        ];
        $monthNow = intval($req->input('bulan-value'));
        $yearNow = intval($req->input('tahun-value'));
        $dataPoll = getTotalPol($monthNow, $yearNow);

        $selectedPeriode = SpmbSettings::where('selected', 1)->first();

        // $selectedPoll = SpmbPollData::all();

        $selectedPoll = SpmbPollData::whereBetween('created_at', [$selectedPeriode->periode_start, $selectedPeriode->periode_end])->get();

        $periode = SpmbSettings::where('selected', 1)->first();

        $periode->periode_text = formatDateIndo($periode->periode_start) . " - " . formatDateIndo($periode->periode_end);


        return view('home.bulanan', [
            'chart' => $chart->build($monthNow, $yearNow, 'Survey Kepuasan Layanan PMB.'),
            'bulanIndonesia' => $bulanIndonesia,
            'dateNow' => [$monthNow, $yearNow],
            'dataPoll' => $dataPoll,

            'periodePoll' => $selectedPoll,
            'periode' => $periode
        ]);
    }

    // BACKUP EXPORT HARIAN
    // public function export()
    // {
    //     $bulanIndonesia = [
    //         'Januari',
    //         'Februari',
    //         'Maret',
    //         'April',
    //         'Mei',
    //         'Juni',
    //         'Juli',
    //         'Agustus',
    //         'September',
    //         'Oktober',
    //         'November',
    //         'Desember'
    //     ];
    //     $daysOfTheMonthNow = intval(Carbon::now()->timezone('Asia/Jakarta')->daysInMonth);
    //     $daysNow = Carbon::now()->timezone('Asia/Jakarta')->day;
    //     $monthNow = Carbon::now()->timezone('Asia/Jakarta')->month;
    //     $yearNow = Carbon::now()->timezone('Asia/Jakarta')->year;

    //     $filteredDataCount = SpmbPollData::whereDate('created_at', '=', "$yearNow-$monthNow-$daysNow")
    //         ->orderBy('created_at', 'desc')
    //         ->count();

    //     return view('home.export-bulanan', [
    //         'bulanIndonesia' => $bulanIndonesia,
    //         'dateNow' => [$daysOfTheMonthNow, $daysNow, $monthNow, $yearNow],
    //         'totalDataCount' => $filteredDataCount
    //     ]);
    // }

    // public function exportFilter(Request $req)
    // {
    //     $bulanIndonesia = [
    //         'Januari',
    //         'Februari',
    //         'Maret',
    //         'April',
    //         'Mei',
    //         'Juni',
    //         'Juli',
    //         'Agustus',
    //         'September',
    //         'Oktober',
    //         'November',
    //         'Desember'
    //     ];
    //     $daysOfTheMonthNow = intval(Carbon::now()->timezone('Asia/Jakarta')->daysInMonth);
    //     $daysNow = intval($req->input('hari-value'));
    //     $monthNow = $req->input('bulan-value');
    //     $yearNow = intval($req->input('tahun-value'));

    //     $filteredDataCount = SpmbPollData::whereDate('created_at', '=', "$yearNow-$monthNow-$daysNow")
    //         ->orderBy('created_at', 'desc')
    //         ->count();

    //     return view('home.export-bulanan', [
    //         'bulanIndonesia' => $bulanIndonesia,
    //         'dateNow' => [$daysOfTheMonthNow, $daysNow, $monthNow, $yearNow],
    //         'totalDataCount' => $filteredDataCount
    //     ]);
    // }

    public function export()
    {
        $bulanIndonesia = [
            'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember'
        ];
        $daysOfTheMonthNow = intval(Carbon::now()->timezone('Asia/Jakarta')->daysInMonth);
        $daysNow = Carbon::now()->timezone('Asia/Jakarta')->day;
        $monthNow = Carbon::now()->timezone('Asia/Jakarta')->month;
        $yearNow = Carbon::now()->timezone('Asia/Jakarta')->year;

        // $selectedPeriode = SpmbSettings::where('selected', 1)->first();

        $allPeriode = SpmbSettings::all();

        for ($i = 0; $i < sizeof($allPeriode); $i++) {
            $allPeriode[$i]->periode_text = formatDateIndo($allPeriode[$i]->periode_start) . " - " . formatDateIndo($allPeriode[$i]->periode_end);
        }

        // $selectedPoll = SpmbPollData::all();

        // $selectedPoll = SpmbPollData::whereBetween('created_at', [$selectedPeriode->periode_start, $selectedPeriode->periode_end])->get();

        // $periode = SpmbSettings::where('selected', 1)->first();

        // $periode->periode_text = formatDateIndo($periode->periode_start) . " - " . formatDateIndo($periode->periode_end);

        $filteredDataCount = SpmbPollData::whereMonth('created_at', '=', $monthNow)
            ->whereYear('created_at', '=', $yearNow)
            ->orderBy('created_at', 'desc')
            ->count();

        return view('home.export-bulanan', [
            'bulanIndonesia' => $bulanIndonesia,
            'dateNow' => [$daysOfTheMonthNow, $daysNow, $monthNow, $yearNow],
            'totalDataCount' => $filteredDataCount,
            'listPeriode' => $allPeriode
        ]);
    }

    public function exportFilter(Request $req)
    {
        $bulanIndonesia = [
            'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember'
        ];
        $daysOfTheMonthNow = intval(Carbon::now()->timezone('Asia/Jakarta')->daysInMonth);
        $daysNow = intval($req->input('hari-value'));
        $monthNow = $req->input('bulan-value');
        $yearNow = intval($req->input('tahun-value'));

        $filteredDataCount = SpmbPollData::whereMonth('created_at', '=', $monthNow)
            ->whereYear('created_at', '=', $yearNow)
            ->orderBy('created_at', 'desc')
            ->count();

        return view('home.export-bulanan', [
            'bulanIndonesia' => $bulanIndonesia,
            'dateNow' => [$daysOfTheMonthNow, $daysNow, $monthNow, $yearNow],
            'totalDataCount' => $filteredDataCount
        ]);
    }

    public function do_export(Request $req)
    {
        // $daysNow = Carbon::now()->timezone('Asia/Jakarta')->day;
        // $monthNow = Carbon::now()->timezone('Asia/Jakarta')->month;
        // $yearNow = Carbon::now()->timezone('Asia/Jakarta')->year;
        $dateFormatDownload = $req->input('export-btn');

        $filteredData = SpmbPollData::whereDate('created_at', '=', $dateFormatDownload)
            ->orderBy('created_at', 'desc')
            ->get();

        // Create a CSV writer
        $csv = Writer::createFromString('');

        // Add headers
        $csv->insertOne(['Tanggal', 'Nama', 'Respon']); // Add other column names

        // Add data with custom formatting
        foreach ($filteredData as $item) {
            // Format date as needed
            $formattedDate = date('H:i:s d-m-Y', strtotime($item->created_at));

            // Add custom formatted data
            $csv->insertOne([$formattedDate, "Tanpa Nama", ucwords($item->poll)]); // Add other custom formatted columns
        }

        // Set headers for CSV download
        $headers = array(
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $dateFormatDownload . '.csv"',
        );

        // Return CSV as a download response
        return Response::make($csv->getContent(), 200, $headers);
        // return $req->input('export-btn');
    }

    public function do_export_bulanan(Request $req)
    {
        // $daysNow = Carbon::now()->timezone('Asia/Jakarta')->day;
        // $monthNow = Carbon::now()->timezone('Asia/Jakarta')->month;
        // $yearNow = Carbon::now()->timezone('Asia/Jakarta')->year;
        // $dateFormatDownload = $req->input('export-btn');

        $month = $req->input('month-filter');
        $year = $req->input('year-filter');

        $filteredData = SpmbPollData::whereMonth('created_at', '=', $month)
            ->whereYear('created_at', '=', $year)
            ->orderBy('created_at', 'desc')
            ->get();

        // Create a CSV writer
        $csv = Writer::createFromString('');

        // Add headers
        $csv->insertOne(['Tanggal', 'Nama', 'Respon']); // Add other column names

        // Add data with custom formatting
        foreach ($filteredData as $item) {
            // Format date as needed
            $formattedDate = date('H:i:s d-m-Y', strtotime($item->created_at));

            // Add custom formatted data
            $csv->insertOne([$formattedDate, "Tanpa Nama", ucwords($item->poll)]); // Add other custom formatted columns
        }

        // Set headers for CSV download
        $headers = array(
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $year . '-' . $month . '.csv"',
        );

        // Return CSV as a download response
        return Response::make($csv->getContent(), 200, $headers);
        // return $req->input('export-btn');
    }

    public function do_export_periode(Request $periode)
    {
        $selectedPeriode = SpmbSettings::find(intval($periode->id_periode));

        $startDate = $selectedPeriode->periode_start;
        $endDate = $selectedPeriode->periode_end;

        $selectedPoll = SpmbPollData::whereBetween('created_at', [$startDate, $endDate])->get();

        // Create a CSV writer
        $csv = Writer::createFromString('');

        // Add headers
        $csv->insertOne(['Tanggal', 'Nama', 'Respon']); // Add other column names

        // Add data with custom formatting
        foreach ($selectedPoll as $item) {
            // Format date as needed
            $formattedDate = date('H:i:s d-m-Y', strtotime($item->created_at));

            // Add custom formatted data
            $csv->insertOne([$formattedDate, "Tanpa Nama", ucwords($item->poll)]); // Add other custom formatted columns
        }

        $periode_text = formatDateIndo($selectedPeriode->periode_start) . " - " . formatDateIndo($selectedPeriode->periode_end);

        // Set headers for CSV download
        $headers = array(
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $periode_text . '.csv"',
        );

        // Return CSV as a download response
        return Response::make($csv->getContent(), 200, $headers);
    }
}
