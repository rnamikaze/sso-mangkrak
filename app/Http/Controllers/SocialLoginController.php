<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;
use Illuminate\Http\Request;
use App\Models\DesaIndonesia;
use App\Models\SKU\SkuSurvey;
use App\Models\SocialAccount;
use App\Models\SIK\SikBiodata;
use App\Models\SKU\SkuPersonData;
use App\Models\KabupatenIndonesia;
use App\Models\KecamatanIndonesia;
use App\Models\SIK\SikExtraBiodata;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Laravel\Socialite\SocialiteManager;
use App\Models\SIK\SikJabatanStruktural;
use Illuminate\Support\Facades\Redirect;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider;

function filterPhoneNumber2($phoneNumber)
{
    // Check if the first two digits are already "62"
    if (substr($phoneNumber, 0, 2) !== "62") {
        // If not, replace the first digit with "62"
        $phoneNumber = "62" . substr($phoneNumber, 1);
    }
    return $phoneNumber;
}

class SocialLoginController extends Controller
{
    //
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function redirectToGoogleFromLogin()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallbackFromLogin(Request $req)
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $checkAccount = SocialAccount::where('google_email', $googleUser->email)->first();

        if ($checkAccount === null) {
            return Inertia::render('Guest/NewLogin', ['loginStatus' => null, 'warningText' => ['Akun Google Tidak Terdaftar !', 'warning']]);
        }

        $user = User::find(intval($checkAccount->master_id));

        if ($user) {
            // OSC Report-In
            $oscExtLogToken = env("OSC_EXT_LOG_TOKEN");

            $appIdentifier = env("APP_OSC_IDENTIFIER");
            $oscBase = env("OSC_BASE");

            $agent = new Agent();
            $agent->setUserAgent($req->userAgent());

            $extraPayload = [
                'referrer'      => $req->headers->get('origin') ?? $req->headers->get('referer'),
                'utm_source'    => $req->query('utm_source'),
                'utm_medium'    => $req->query('utm_medium'),
                'utm_campaign'  => $req->query('utm_campaign'),
                'platform'      => $agent->platform(),
                'browser'       => $agent->browser(),
                'device'        => $agent->device(),
                'is_mobile'     => $agent->isMobile(),
            ];

            $response = Http::withToken($oscExtLogToken)
                ->post($oscBase . '/api/log/report-in', [
                    "what" => "Auth Success at " . $appIdentifier . " Login [GoogleAuth][$user->mail/$user->name]",
                    "ip" => $req->ip(),
                    "extra" => json_encode($extraPayload)
                ]);

            Auth::login($user);

            return to_route('sso.dashboard');
        }

        return Inertia::render('Guest/NewLogin', ['loginStatus' => null, 'warningText' => ['Akun Google Tidak Terdaftar !', 'warning']]);
    }

    public function handleGoogleCallback(Request $req)
    {
        $activeUserId = Auth::id();

        if ($activeUserId === null) {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $checkAccount = SocialAccount::where('google_email', $googleUser->email)->first();

            if ($checkAccount === null) {
                return Inertia::render('Guest/NewLogin', ['loginStatus' => null, 'warningText' => ['Akun Google Tidak Terdaftar !', 'warning']]);
            }

            $user = User::find(intval($checkAccount->master_id));

            if ($user) {
                // OSC Report-In
                $oscExtLogToken = env("OSC_EXT_LOG_TOKEN");

                $appIdentifier = env("APP_OSC_IDENTIFIER");
                $oscBase = env("OSC_BASE");

                $agent = new Agent();
                $agent->setUserAgent($req->userAgent());

                $extraPayload = [
                    'referrer'      => $req->headers->get('origin') ?? $req->headers->get('referer'),
                    'utm_source'    => $req->query('utm_source'),
                    'utm_medium'    => $req->query('utm_medium'),
                    'utm_campaign'  => $req->query('utm_campaign'),
                    'platform'      => $agent->platform(),
                    'browser'       => $agent->browser(),
                    'device'        => $agent->device(),
                    'is_mobile'     => $agent->isMobile(),
                ];

                $response = Http::withToken($oscExtLogToken)
                    ->post($oscBase . '/api/log/report-in', [
                        "what" => "Auth Success at " . $appIdentifier . " Login [GoogleAuth][$user->mail/$user->name]",
                        "ip" => $req->ip(),
                        "extra" => json_encode($extraPayload)
                    ]);

                Auth::login($user);

                return to_route('sso.dashboard');
            }

            return Inertia::render('Guest/NewLogin', ['loginStatus' => null, 'warningText' => ['Akun Google Tidak Terdaftar !', 'warning']]);
        }

        $isSocialTableAvailable = SocialAccount::where('master_id', $activeUserId)->first();

        $displayEmail = "";

        if ($isSocialTableAvailable === null) {
            $createNew = new SocialAccount;

            $createNew->master_id = intval($activeUserId);
            // return response()->json($isSocialTableAvailable);

            $createNew->save();
        }

        $isSocialTableAvailable = SocialAccount::where('master_id', $activeUserId)->first();

        if ($isSocialTableAvailable) {
            // return response()->json($isSocialTableAvailable);
            $googleUser = Socialite::driver('google')->stateless()->user();

            $isSocialTableAvailable->google_id = $googleUser->id;
            $isSocialTableAvailable->google_email = $googleUser->email;
            $isSocialTableAvailable->google_avatar_url = $googleUser->avatar;
            $isSocialTableAvailable->google_json = json_encode($googleUser);
            $displayEmail = $googleUser->email;

            $isSocialTableAvailable->save();

            return Redirect::route('sso.gauth.redirect', ["emailDisplay" => $displayEmail, "authStatus" => true]);
        }

        // return response()->json(false);
        return Redirect::route('sso.gauth.redirect', ["emailDisplay" => "Kosong", "authStatus" => false]);

        // $user = User::where('email', $googleUser->email)->first();
        // if(!$user)
        // {
        //     $user = User::create(['name' => $googleUser->name, 'email' => $googleUser->email, 'password' => \Hash::make(rand(100000,999999))]);
        // }

        // Auth::login($user);

        // return redirect(RouteServiceProvider::HOME);

        // return response()->json($googleUser);
    }

    public function redirectBack(Request $req)
    {

        $displayEmail = $req->emailDisplay;

        $activeUserId = Auth::id();

        $userActive = User::where('id', intval($activeUserId))->get();

        $getSkuLevel3 = null;
        $getSkuLevel2 = null;
        $getSkuLevel1 = null;

        $step_1 = 0;
        $step_2 = 0;
        $SkuFinalValue = 0;

        $surveySKUValue = null;

        $getSKU = SkuPersonData::where(
            'nik',
            $userActive[0]['nik']
        )->first();

        if ($getSKU) {
            $getSkuLevel3 = SkuSurvey::where('nama_id', $getSKU->id)->where('level_survey_id', 3)->count();
            $getSkuLevel2 = SkuSurvey::where('nama_id', $getSKU->id)->where('level_survey_id', 2)->count();
            $getSkuLevel1 = SkuSurvey::where('nama_id', $getSKU->id)->where('level_survey_id', 1)->count();

            // Calculate Rating SKU Value
            $step_1 = ($getSkuLevel2 + $getSkuLevel1 + $getSkuLevel3) * 3;
            $step_2 = ($getSkuLevel1 * 1) + ($getSkuLevel2 * 2) + ($getSkuLevel3 * 3);

            if ($step_1 < 1) {
                $SkuFinalValue = 0;
            } else {
                $SkuFinalValue = $step_2 * (100 / $step_1);
            }
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
            $telepon = strlen($getBiodata->telepon) < 5 ? null : filterPhoneNumber2($getBiodata->telepon);
        }



        $profileLoad = [$jastur, Str::title($kab), Str::title($kec), Str::title($desa), $telepon, $profile_img, $surveySKUValue, $SkuFinalValue];
        // $profileLoad = ["", "", "", "", [$selectKota, $selectKecam, $selectDesa, $getExtraBiodata]];

        // $profileLoad = "";

        for ($i = 0; $i < sizeof($userActive); $i++) {
            $userActive[$i]['allowed_app'] = unserialize($userActive[$i]['allowed_app_arr']);
        }

        if ($req->authStatus === false) {
            return Inertia::render('Sso/NewSsoDashboard', [
                "userAccount" => $userActive,
                "userData" => $profileLoad,
                "showToastParam" => [true, "warning", "G-Auth Gagal!", 10000]
            ]);
        }

        return Inertia::render('Sso/NewSsoDashboard', [
            "userAccount" => $userActive,
            "userData" => $profileLoad,
            "showToastParam" => [true, "success", $displayEmail . " Terhubung", 7000]
        ]);
    }

    public function checkSocialAccount(Request $req)
    {
        $id = intval($req->id);

        $checkAccount = SocialAccount::where('master_id', $id)->select('google_id', 'google_email', 'google_avatar_url')->first();

        $rawJson = '{
            "status":"false"
        }';

        if ($checkAccount) {
            $checkAccount->status = true;
            return response()->json($checkAccount);
        }
        return response()->json(json_decode($rawJson));
    }
}
