<?php

namespace App\Http\Controllers\SKU;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\SKU\SkuPersonData;
use App\Models\SKU\SkuUnit;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

function strip_non_alpha($input)
{
    // Use preg_replace to replace all non-alphabetic characters with an empty string
    return preg_replace('/[^a-zA-Z]/', '', $input);
}

class HomeControllers extends Controller
{
    public function home()
    {
        return Inertia::render('SKU/Index', ['key1' => 'test value']);
    }

    public function login()
    {
        return Inertia::render('SKU/Login', ['loginStatus' => "wait"]);
    }

    public function doLogin(Request $req)
    {
        $credentials = $req->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $req->session()->regenerate();

            return redirect('/home');


            // return redirect()->intended('dashboard');
        }
        return Inertia::render('SKU/Login', ['loginStatus' => "failed"]);
        // return Inertia::render('Index', ['emailLog' => "Bagus"]);

        // return response()->json(['text' => $email]);

        // return to_route("home", ['emailLog' => 'dandun', "passLog" => $password]);
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/login');
    }

    public function guest(Request $req, ?string $unitId = null)
    {
        $fullUrl = $req->getSchemeAndHttpHost();

        // Unit Alias
        $unitIdClean = strip_non_alpha($unitId);

        $unitIdPass = null;

        if ($unitId) {
            $checkAlias = SkuUnit::where('alias', $unitIdClean)->first();

            if ($checkAlias) {
                $unitIdPass = [$checkAlias->id, $checkAlias->nama_unit, $checkAlias->alias];
            }
        }

        $personDataGuest = SkuPersonData::select('id', 'nama', 'kode_unit_id')->get();;
        $unitsGuest = SkuUnit::all();

        return Inertia::render('SKU/Guest/Index', ['personDataGuest' => $personDataGuest, 'unitsGuest' => $unitsGuest, 'filterUnitId' => $unitIdPass, 'currentHost' => $fullUrl]);
    }

    public function guestWithException(Request $data)
    {
        $excludedId = Auth::id();

        $getUser = User::find($excludedId);
        $targetNik = $getUser->nik;

        $personDataGuest = SkuPersonData::select('id', 'nama', 'kode_unit_id')
            ->where('nik', '!=', $targetNik)
            ->get();

        $unitsGuest = SkuUnit::all();

        return Inertia::render('SKU/Guest/Index', ['personDataGuest' => $personDataGuest, 'unitsGuest' => $unitsGuest]);
    }
}
