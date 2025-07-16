<?php

namespace App\Http\Controllers\SPMB;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\SPMB\SpmbPollData;
use App\Http\Controllers\Controller;

// namespace App\Http\Controllers;

// use App\Models\PollData;
// use Carbon\Carbon;
// use Illuminate\Http\Request;

function idFormatedDate()
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
    // $daysOfTheMonthNow = intval(Carbon::now()->timezone('Asia/Jakarta')->daysInMonth);
    $daysNow = Carbon::now()->timezone('Asia/Jakarta')->day;
    $monthNow = intval(Carbon::now()->timezone('Asia/Jakarta')->month) - 1;
    $yearNow = Carbon::now()->timezone('Asia/Jakarta')->year;

    return $idFormatedDate = "$daysNow $bulanIndonesia[$monthNow] $yearNow";
}

class HomePollControllers extends Controller
{
    //
    public function home()
    {
        // $nowDate = now()->timezone('Asia/Jakarta')->format('d-m-Y');
        $totalPoll = SpmbPollData::all()->count();

        $idFormatedDate = idFormatedDate();

        return view('homepoll.index', [
            'nowDate' => $idFormatedDate,
            'totalPoll' => $totalPoll
        ]);
    }

    public function dopoll(Request $req)
    {
        $pollData = new SpmbPollData();
        $unique_str = Str::random(10);
        $pollData->poll = $req->input('confirm-poll');

        $poll_code = 2;
        if ($req->input('confirm-poll') === 'kurang puas') {
            $poll_code = 1;
        } else if ($req->input('confirm-poll') === 'sangat puas') {
            $poll_code = 3;
        }
        $pollData->poll_code = $poll_code;
        $pollData->unique_string = $unique_str;

        $pollData->save();

        // $nowDate = now()->timezone('Asia/Jakarta')->format('d-m-Y');
        $idFormatedDate = idFormatedDate();

        $selectedData = SpmbPollData::where('unique_string', $unique_str)->first();
        $selectedId = $selectedData->id;
        // $totalPoll = SpmbPollData::all()->count();

        return view('homepoll.thanks', [
            'nowDate' => $idFormatedDate,
            'selectedId' => $selectedId
            // 'totalPoll' => $totalPoll
        ]);
        // return $req->input('confirm-poll');
    }

    public function updatePollInfo(Request $info)
    {
        $selectedPoll = SpmbPollData::find(intval($info->selected_id));

        $selectedPoll->sumber_info = $info->survey_info;

        $selectedPoll->save();

        return to_route('spmb.homepoll');
    }

    public function debug()
    {
        $idFormatedDate = idFormatedDate();
        return view('homepoll.thanks', [
            'nowDate' => $idFormatedDate,
        ]);
    }
}
