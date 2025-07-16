<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use Inertia\Inertia;
use App\Models\LoginLoger;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\DesaIndonesia;
use App\Models\SKU\SkuSurvey;
use App\Models\SIK\SikBiodata;
use App\Models\SKU\SkuPersonData;
use App\Models\KabupatenIndonesia;
use App\Models\KecamatanIndonesia;
use App\Models\SIK\SikExtraBiodata;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\SIK\SikJabatanStruktural;


class SsoSettingsController extends Controller
{
    public function getAllUsers()
    {
        $allUsers = User::select('nik', 'name', 'id', 'updated_at')->where('username', '<>', 'superadmin')->get();

        if ($allUsers) {
            for ($i = 0; $i < sizeof($allUsers); $i++) {
                $allUsers[$i]->last_edit = Carbon::parse($allUsers[$i]->updated_at)->format('H:i:s d/m/Y');
            }
        }

        $activeUserId = Auth::id();

        $userActive = User::where('id', intval($activeUserId))->get();

        $getSkuLevel3 = null;
        $getSkuLevel2 = null;
        $getSkuLevel1 = null;

        $surveySKUValue = null;

        $getSKU = SkuPersonData::where(
            'nik',
            $userActive[0]['nik']
        )->first();

        if ($getSKU) {
            $getSkuLevel3 = SkuSurvey::where('nama_id', $getSKU->id)->where('level_survey_id', 3)->count();
            $getSkuLevel2 = SkuSurvey::where('nama_id', $getSKU->id)->where('level_survey_id', 2)->count();
            $getSkuLevel1 = SkuSurvey::where('nama_id', $getSKU->id)->where('level_survey_id', 1)->count();
        }

        if ($getSKU) {
            $surveySKUValue = [$getSkuLevel1, $getSkuLevel2, $getSkuLevel3];
        }

        $kab = "";
        $kec = "";
        $desa = "";
        $jastur = "";
        $telepon = "";
        $profile_img = null;

        if ($activeUserId === 1 || $activeUserId === 2 || $activeUserId === 3) {
            $kab = "Sidoarjo";
            $kec = "Sidoarjo";
            $desa = "Sidoarjo";

            $telepon = null;

            if ($activeUserId === 2 || $activeUserId === 3) {
                $jastur = "Admin";
            } else {
                $jastur = "Super Admin [UPT-TI]";
            }
        } else {
            $getBiodata = SikBiodata::where('master_id', intval($activeUserId))->select('id', 'jabatan_struktural_id', 'telepon', 'master_id', 'img_storage')->first();
            $getExtraBiodata = SikExtraBiodata::where('biodata_id', intval($getBiodata->id))->select('desa_kel', 'kecamatan', 'kota_kab')->first();
            $getJabatanStruktural = SikJabatanStruktural::find($getBiodata->jabatan_struktural_id);
            $selectKota = KabupatenIndonesia::where('name_id', intval($getExtraBiodata->kota_kab))->first();
            $selectKecam = KecamatanIndonesia::where('name_id', intval($getExtraBiodata->kecamatan))->first();
            $selectDesa = DesaIndonesia::where('name_id', intval($getExtraBiodata->desa_kel))->first();


            if ($getBiodata['img_storage'] !== null) {

                // $url = Storage::url('public/profile/' . $getBiodata->nik . "_" . $getBiodata->master_id . "/" . $getBiodata->img_storage);

                $url =  $getBiodata->img_storage;
                $profile_img =  $url;
            }

            if ($getExtraBiodata->kota_kab === null) {
                $kab = "Sidoarjo";
                $kec = "Sidoarjo";
                $desa = "Sidoarjo";
                $jastur = "Kosong";
            } else {
                $kab = $selectKota->name === null ? "Kosong" : $selectKota->name;
                $kec = $selectKecam->name === null ? "Kosong" : $selectKecam->name;
                $desa = $selectDesa->name === null ? "Kosong" : $selectDesa->name;
                $jastur = $getJabatanStruktural->name === null ? "Kosong" : $getJabatanStruktural->name;
            }
            // echo "Tes";
            // $telepon = null;
            $telepon = strlen($getBiodata->telepon) < 5 ? null : filterPhoneNumber($getBiodata->telepon);
        }

        $profileLoad = [$jastur, Str::title($kab), Str::title($kec), Str::title($desa), $telepon, $profile_img, $surveySKUValue];
        // $profileLoad = ["", "", "", "", [$selectKota, $selectKecam, $selectDesa, $getExtraBiodata]];

        // $profileLoad = "";

        for ($i = 0; $i < sizeof($userActive); $i++) {
            $userActive[$i]['allowed_app'] = unserialize($userActive[$i]['allowed_app_arr']);
        }

        if ($allUsers) {
            return response()->json([
                "userAccount" => $userActive,
                "userData" => $profileLoad,
                // "notAllowedAlert" => $notAllowed,
                "allUserList" => $allUsers,
                "success" => true
            ]);
        }

        return Inertia::render('Sso/NewSsoDashboard', [
            "userAccount" => $userActive,
            "userData" => $profileLoad,
            // "notAllowedAlert" => $notAllowed,
            "allUserList" => $allUsers,
            "success" => false
        ]);
    }

    public function aksesSelectedUser(Request $person)
    {
        $selectedUser = User::find(intval($person->id));

        $selectedUser->allowed_app = unserialize($selectedUser->allowed_app_arr);

        $activeUserId = Auth::id();

        $userActive = User::where('id', intval($activeUserId))->get();

        // User Data ========
        $getSkuLevel3 = null;
        $getSkuLevel2 = null;
        $getSkuLevel1 = null;

        $surveySKUValue = null;

        $getSKU = SkuPersonData::where(
            'nik',
            $userActive[0]['nik']
        )->first();

        if ($getSKU) {
            $getSkuLevel3 = SkuSurvey::where('nama_id', $getSKU->id)->where('level_survey_id', 3)->count();
            $getSkuLevel2 = SkuSurvey::where('nama_id', $getSKU->id)->where('level_survey_id', 2)->count();
            $getSkuLevel1 = SkuSurvey::where('nama_id', $getSKU->id)->where('level_survey_id', 1)->count();
        }

        if ($getSKU) {
            $surveySKUValue = [$getSkuLevel1, $getSkuLevel2, $getSkuLevel3];
        }

        $kab = "";
        $kec = "";
        $desa = "";
        $jastur = "";
        $telepon = "";
        $profile_img = null;

        if ($activeUserId === 1 || $activeUserId === 2 || $activeUserId === 3) {
            $kab = "Sidoarjo";
            $kec = "Sidoarjo";
            $desa = "Sidoarjo";

            $telepon = null;

            if ($activeUserId === 2 || $activeUserId === 3) {
                $jastur = "Admin";
            } else {
                $jastur = "Super Admin [UPT-TI]";
            }
        } else {
            $getBiodata = SikBiodata::where('master_id', intval($activeUserId))->select('id', 'jabatan_struktural_id', 'telepon', 'master_id', 'img_storage')->first();
            $getExtraBiodata = SikExtraBiodata::where('biodata_id', intval($getBiodata->id))->select('desa_kel', 'kecamatan', 'kota_kab')->first();
            $getJabatanStruktural = SikJabatanStruktural::find($getBiodata->jabatan_struktural_id);
            $selectKota = KabupatenIndonesia::where('name_id', intval($getExtraBiodata->kota_kab))->first();
            $selectKecam = KecamatanIndonesia::where('name_id', intval($getExtraBiodata->kecamatan))->first();
            $selectDesa = DesaIndonesia::where('name_id', intval($getExtraBiodata->desa_kel))->first();


            if ($getBiodata['img_storage'] !== null) {

                // $url = Storage::url('public/profile/' . $getBiodata->nik . "_" . $getBiodata->master_id . "/" . $getBiodata->img_storage);

                $url =  $getBiodata->img_storage;
                $profile_img =  $url;
            }

            if ($getExtraBiodata->kota_kab === null) {
                $kab = "Sidoarjo";
                $kec = "Sidoarjo";
                $desa = "Sidoarjo";
                $jastur = "Kosong";
            } else {
                $kab = $selectKota->name === null ? "Kosong" : $selectKota->name;
                $kec = $selectKecam->name === null ? "Kosong" : $selectKecam->name;
                $desa = $selectDesa->name === null ? "Kosong" : $selectDesa->name;
                $jastur = $getJabatanStruktural->name === null ? "Kosong" : $getJabatanStruktural->name;
            }
            // echo "Tes";
            // $telepon = null;
            $telepon = strlen($getBiodata->telepon) < 5 ? null : filterPhoneNumber($getBiodata->telepon);
        }



        $profileLoad = [$jastur, Str::title($kab), Str::title($kec), Str::title($desa), $telepon, $profile_img, $surveySKUValue];
        // User Data End =========

        return Inertia::render('Sso/NewSsoDashboard', [
            "userAccount" => $userActive,
            "userData" => $profileLoad,
            // "notAllowedAlert" => $notAllowed,
            // "allUserList" => $allUsers,
            'aksesSelectedUser' => $selectedUser
        ]);
    }

    public function saveAksesSelectedUser(Request $person)
    {
        $selectedUserEdit = User::find(intval($person->id));

        $payJson = json_decode($person->allowed_app);
        $arrayPayload = serialize($payJson);

        $selectedUserEdit->allowed_app_arr = $arrayPayload;
        $selectedUserEdit->save();

        // Back
        $selectedUser = User::find(intval($person->id));

        $selectedUser->allowed_app = unserialize($selectedUser->allowed_app_arr);

        $activeUserId = Auth::id();

        $userActive = User::where('id', intval($activeUserId))->get();

        return Inertia::render('Sso/NewSsoDashboard', [
            "userAccount" => $userActive,
            // "userData" => $profileLoad,
            // "notAllowedAlert" => $notAllowed,
            // "allUserList" => $allUsers,
            'aksesSelectedUser' => $selectedUser,
            'showToastParam' => [true, 'success', 'Berhasil Disimpan', $payJson],
            'refreshToken' => Str::random(5),
        ]);
    }

    public function getLoger()
    {
        $allLoger = LoginLoger::orderBy('id', 'desc')->get();

        for ($i = 0; $i < sizeof($allLoger); $i++) {
            $allLoger[$i]->formated_created_at = Carbon::parse($allLoger[$i]->created_at)->format('H:i d/m/Y');
        }

        $activeUserId = Auth::id();

        $userActive = User::where('id', intval($activeUserId))->get();

        // User Data ========
        $getSkuLevel3 = null;
        $getSkuLevel2 = null;
        $getSkuLevel1 = null;

        $surveySKUValue = null;

        $getSKU = SkuPersonData::where(
            'nik',
            $userActive[0]['nik']
        )->first();

        if ($getSKU) {
            $getSkuLevel3 = SkuSurvey::where('nama_id', $getSKU->id)->where('level_survey_id', 3)->count();
            $getSkuLevel2 = SkuSurvey::where('nama_id', $getSKU->id)->where('level_survey_id', 2)->count();
            $getSkuLevel1 = SkuSurvey::where('nama_id', $getSKU->id)->where('level_survey_id', 1)->count();
        }

        if ($getSKU) {
            $surveySKUValue = [$getSkuLevel1, $getSkuLevel2, $getSkuLevel3];
        }

        $kab = "";
        $kec = "";
        $desa = "";
        $jastur = "";
        $telepon = "";
        $profile_img = null;

        if ($activeUserId === 1 || $activeUserId === 2 || $activeUserId === 3) {
            $kab = "Sidoarjo";
            $kec = "Sidoarjo";
            $desa = "Sidoarjo";

            $telepon = null;

            if ($activeUserId === 2 || $activeUserId === 3) {
                $jastur = "Admin";
            } else {
                $jastur = "Super Admin [UPT-TI]";
            }
        } else {
            $getBiodata = SikBiodata::where('master_id', intval($activeUserId))->select('id', 'jabatan_struktural_id', 'telepon', 'master_id', 'img_storage')->first();
            $getExtraBiodata = SikExtraBiodata::where('biodata_id', intval($getBiodata->id))->select('desa_kel', 'kecamatan', 'kota_kab')->first();
            $getJabatanStruktural = SikJabatanStruktural::find($getBiodata->jabatan_struktural_id);
            $selectKota = KabupatenIndonesia::where('name_id', intval($getExtraBiodata->kota_kab))->first();
            $selectKecam = KecamatanIndonesia::where('name_id', intval($getExtraBiodata->kecamatan))->first();
            $selectDesa = DesaIndonesia::where('name_id', intval($getExtraBiodata->desa_kel))->first();


            if ($getBiodata['img_storage'] !== null) {

                // $url = Storage::url('public/profile/' . $getBiodata->nik . "_" . $getBiodata->master_id . "/" . $getBiodata->img_storage);

                $url =  $getBiodata->img_storage;
                $profile_img =  $url;
            }

            if ($getExtraBiodata->kota_kab === null) {
                $kab = "Sidoarjo";
                $kec = "Sidoarjo";
                $desa = "Sidoarjo";
                $jastur = "Kosong";
            } else {
                $kab = $selectKota->name === null ? "Kosong" : $selectKota->name;
                $kec = $selectKecam->name === null ? "Kosong" : $selectKecam->name;
                $desa = $selectDesa->name === null ? "Kosong" : $selectDesa->name;
                $jastur = $getJabatanStruktural->name === null ? "Kosong" : $getJabatanStruktural->name;
            }
            // echo "Tes";
            // $telepon = null;
            $telepon = strlen($getBiodata->telepon) < 5 ? null : filterPhoneNumber($getBiodata->telepon);
        }



        $profileLoad = [$jastur, Str::title($kab), Str::title($kec), Str::title($desa), $telepon, $profile_img, $surveySKUValue];
        // User Data End ======

        return Inertia::render('Sso/NewSsoDashboard', [
            "userAccount" => $userActive,
            "userData" => $profileLoad,
            // "notAllowedAlert" => $notAllowed,
            "allUserList" => $allLoger,
            // 'aksesSelectedUser' => $selectedUser,
            // 'showToastParam' => [true, 'success', 'Berhasil Disimpan'],
            // 'refreshToken' => Str::random(5)
        ]);
    }
}
