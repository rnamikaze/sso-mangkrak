<?php

namespace App\Http\Controllers\SPMB;

use Carbon\Carbon;
use App\Models\SpmbSettings;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

function formatDate($inputDate)
{
    // Parse the input date using Carbon
    $carbonDate = Carbon::createFromFormat('Y-m-d', $inputDate);

    // Format the date in yyyy-mm-dd format
    $formattedDate = $carbonDate->format('d/m/Y');

    // Return the formatted date
    return $formattedDate;
}

class SpmbSettingsControllers extends Controller
{
    //
    public function pengaturan()
    {
        $allPeriode = SpmbSettings::where('active', 1)->get();

        for ($i = 0; $i < sizeof($allPeriode); $i++) {
            $allPeriode[$i]['periode_start_fm'] = formatDate($allPeriode[$i]['periode_start']);
            $allPeriode[$i]['periode_end_fm'] = formatDate($allPeriode[$i]['periode_end']);
        }

        return view('home.pengaturan', [
            'allPeriode' => $allPeriode
        ]);
    }

    public function tambahPeriode(Request $tanggal)
    {
        $newPeriode = new SpmbSettings;

        $newPeriode->periode_start = $tanggal->periode_start;
        $newPeriode->periode_end = $tanggal->periode_end;
        $newPeriode->active = 1;

        $newPeriode->save();

        return to_route('spmb.pengaturan');
    }

    public function setPeriodeActive(Request $set)
    {
        if (intval($set->active) === 0) {
            // $selected = SpmbSettings::find(intval($set->periode_id));
            // $selected->selected = 1;
            // $selected->save();

            // Update all records where id is not equal to the selectedId
            SpmbSettings::where('id', '!=', intval($set->periode_id))->update(['selected' => 0]);

            // Update the record where id is equal to the selectedId
            SpmbSettings::where('id', intval($set->periode_id))->update(['selected' => 1]);
        }

        return to_route('spmb.pengaturan');
        // return $set->active;
    }

    public function deletePeriode(Request $target)
    {
        $deleteId = $target->periode_id;

        SpmbSettings::destroy(intval($deleteId));

        return to_route('spmb.pengaturan');
    }
}
