<?php

namespace App\Http\Controllers\USL;

use Carbon\Carbon;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Support\Str;
use App\Models\UslShortLink;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

function getActiveUserInfo()
{
    $user = User::find(intval(Auth::id()));
    $userInfo = [$user->id, $user->email, $user->name];

    return $userInfo;
}

function getAllActiveShortLinks($isDescending = true)
{
    $allLinks = null;

    if ($isDescending) {
        $allLinks = UslShortLink::orderBy('created_at', 'desc')->get();
    } else {
        $allLinks = UslShortLink::get();
    }

    for ($i = 0; $i < sizeof($allLinks); $i++) {
        $formattedDate = Carbon::parse($allLinks[$i]['updated_at'])->format('l, d/m/Y');
        $allLinks[$i]['formatted_date'] = $formattedDate;

        $formattedDate = Carbon::parse($allLinks[$i]['created_at'])->format('l, d/m/Y');
        $allLinks[$i]['created_at_formatted'] = $formattedDate;
    }

    return $allLinks;
}

function getAllUsersAccount()
{
    $allUser = User::orderBy('created_at', 'desc')->get();

    for ($i = 0; $i < sizeof($allUser); $i++) {
        $allUser[$i]['total_links'] = UslShortLink::where('owned_by', intval($allUser[$i]['id']))->count();
    }

    return $allUser;
}

function isNumericString($input)
{
    // Check if the input is a string and has more than 5 characters
    if (is_string($input) && strlen($input) > 5) {
        // Check if all characters in the string are numbers
        if (ctype_digit($input)) {
            return true; // Return true if it meets the conditions
        }
    }
    return false; // Return false otherwise
}

// PARAMETER callPopUp definition:
// [kode call, extra parameter]
// 1 : edit link success
// 2 : alias already available
// 3 : add user success
// 4 : nik is already registered
// 5 : email is already registered
// 6 : edit user success
// 7 : delete user success
// 8 : reset password success

class AdminControllers extends Controller
{
    //

    public function loginEmail(Request $req)
    {
        $cred = $req->validate([
            'email' => ['min:6', 'required']
        ]);

        $findUser = null;

        if (isNumericString($cred['email'])) {
            $findUser = User::where('nik', $cred['email'])->first();
        } else {
            $findUser = User::where('email', $cred['email'])->first();
        }

        $username = 0;
        $loginPhase = [0, Str::random(10)];
        $isEmailRegistered = 0;

        if ($findUser) {
            $username = $findUser->name;
            $loginPhase = [1, Str::random(10)];
            $isEmailRegistered = 1;
        }

        return Inertia::render('NewLogin', [
            'username' => $username,
            'loginPhase' => $loginPhase,
            'emailWrong' => [$isEmailRegistered, Str::random(10)]
        ]);
    }

    public function login(Request $req)
    {
        $credentials = $req->validate([
            'email' => ['min:5'],
            'password' => ['required'],
        ]);


        if (isNumericString($credentials['email'])) {
            if (Auth::attempt(['nik' => $credentials['email'], 'password' => $credentials['password']])) {
                $req->session()->regenerate();

                return redirect('/admin/dashboard');
            }
        } else {
            if (Auth::attempt($credentials)) {
                $req->session()->regenerate();

                return redirect('/admin/dashboard');
            }
        }

        $findUser = null;

        if (isNumericString($credentials['email'])) {
            $findUser = User::where('nik', $credentials['email'])->first();
        } else {
            $findUser = User::where('email', $credentials['email'])->first();
        }

        $username = $findUser->name;

        // return Inertia::render('Login', [
        //     'loginStatus' => false,
        //     "respon" => Str::random(15)
        // ]);


        return Inertia::render('NewLogin', [
            'username' => $username,
            'loginPhase' => $loginPhase = [1, Str::random(10)],
            'passwordTest' => Str::random(15)
        ]);
    }

    public function loginGet()
    {
        return Inertia::render('NewLogin', ['loginStatus' => null]);
    }

    public function dashboard()
    {
        $allLinks = getAllActiveShortLinks();
        $userInfo = getActiveUserInfo();

        for ($i = 0; $i < sizeof($allLinks); $i++) {
            $formattedDate = Carbon::parse($allLinks[$i]['updated_at'])->format('l, d/m/Y');
            $allLinks[$i]['formatted_date'] = $formattedDate;
        }

        return Inertia::render('USL/Main/Dashboard', [
            "allLinks" => $allLinks,
            "respon" => Str::random(15),
            "userActive" => $userInfo
        ]);
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/admin/login');
    }

    public function getUserList()
    {
        $allUser = getAllUsersAccount();
        $userInfo = getActiveUserInfo();

        return Inertia::render('USL/Main/Dashboard', [
            "allLinks" => $allUser,
            "selectedView" => 10,
            "respon" => Str::random(15),
            "userActive" => $userInfo
        ]);
    }

    public function createUser(Request $userInfo)
    {
        $isNikRegistered = User::where('nik', $userInfo->addUserNik)->exists();
        $allUser = getAllUsersAccount();
        $userAcc = getActiveUserInfo();

        // $isEmailRegistered = User::where('email')

        if ($isNikRegistered) {
            return Inertia::render('Main/Dashboard', [
                "allLinks" => $allUser,
                "selectedView" => 10,
                "respon" => Str::random(15),
                "callPopUp" => [4, ""],
                "showAddUserBox" => 1,
                "userActive" => $userAcc
            ]);
        }

        $isEmailRegistered = User::where('email', $userInfo->addUserEmail)->exists();

        if ($isEmailRegistered) {
            return Inertia::render('Main/Dashboard', [
                "allLinks" => $allUser,
                "selectedView" => 10,
                "respon" => Str::random(15),
                "callPopUp" => [5, ""],
                "showAddUserBox" => 1,
                "userActive" => $userAcc
            ]);
        }

        $newUser = new User;

        // VALIDATE LATER
        // $userInfo = $userInfo->validate([
        //     'addUser'
        // ]);

        $newUser->nik = $userInfo->addUserNik;
        $newUser->name = $userInfo->addUserName;
        $newUser->email = $userInfo->addUserEmail;
        $newUser->password = Hash::make($userInfo->addUserPassword);
        $newUser->email_verified_at = now();
        $newUser->remember_token = Str::random(10);
        $newUser->save();

        $allUser = getAllUsersAccount();

        return Inertia::render('Main/Dashboard', [
            "allLinks" => $allUser,
            "selectedView" => 10,
            "respon" => Str::random(15),
            "callPopUp" => [3, ""],
            "showAddUserBox" => 0,
            "userActive" => $userAcc
        ]);
    }

    public function saveEditUser(Request $userInfo)
    {
        // Check if user submiting the same value
        $checkUser = User::find(intval($userInfo->id));
        $allUser = getAllUsersAccount();
        $userAcc = getActiveUserInfo();

        if ($checkUser->nik !== $userInfo->addUserNik) {
            // Check if Nik is registered
            $isNikRegistered = User::where('nik', $userInfo->addUserNik)->exists();

            if ($isNikRegistered) {
                return Inertia::render('Main/Dashboard', [
                    "allLinks" => $allUser,
                    "selectedView" => 10,
                    "respon" => Str::random(15),
                    "callPopUp" => [4, ""],
                    "showAddUserBox" => 1,
                    "userActive" => $userAcc
                ]);
            }
        }

        if ($checkUser->email !== $userInfo->addUserEmail) {
            // Check if Email is registered
            $isEmailRegistered = User::where('email', $userInfo->addUserEmail)->exists();

            if ($isEmailRegistered) {
                return Inertia::render('Main/Dashboard', [
                    "allLinks" => $allUser,
                    "selectedView" => 10,
                    "respon" => Str::random(15),
                    "callPopUp" => [5, ""],
                    "showAddUserBox" => 1,
                    "userActive" => $userAcc
                ]);
            }
        }


        // IF all godd proced to save
        // $newUser = new User;
        $doEdit = User::find(intval($userInfo->id));

        // VALIDATE LATER
        // $userInfo = $userInfo->validate([
        //     'addUser'
        // ]);

        if ($checkUser->nik !== $userInfo->addUserNik) {
            $doEdit->nik = $userInfo->addUserNik;
        }
        if ($checkUser->email !== $userInfo->addUserEmail) {
            $doEdit->email = $userInfo->addUserEmail;
        }

        $doEdit->name = $userInfo->addUserName;
        // $doEdit->password = Hash::make($userInfo->addUserPassword);
        // $doEdit->email_verified_at = now();
        // $doEdit->remember_token = Str::random(10);
        $doEdit->save();

        $allUser = getAllUsersAccount();
        $userAcc = getActiveUserInfo();

        return Inertia::render('Main/Dashboard', [
            "allLinks" => $allUser,
            "selectedView" => 10,
            "respon" => Str::random(15),
            "callPopUp" => [6, ""],
            "userActive" => $userAcc,
            "showAddUserBox" => 0,
        ]);
    }

    public function getEditUser(request $userId)
    {
        $getUser = User::find(intval($userId->id));

        $allUser = getAllUsersAccount();
        $userAcc = getActiveUserInfo();

        return Inertia::render('Main/Dashboard', [
            "allLinks" => $allUser,
            "selectedView" => 10,
            "respon" => Str::random(15),
            // "callPopUp" => [6, ""],
            "showAddUserBox" => 1,
            "showAddUserBoxisEditing" => 1,
            "editedUser" => $getUser,
            "userActive" => $userAcc
        ]);
    }

    public function checkNik(Request $value)
    {
        $nik = $value->nik;
        $isAlready = User::where('nik', $nik)->first();

        return response()->json(['nikRegistered' => $isAlready !== null, 'haha' => $value->nik]);
    }

    public function deleteUser(Request $obj)
    {
        $deleteUser = User::find(intval($obj->id));

        $deleteUser->delete();

        $allUser = getAllUsersAccount();
        $userInfo = getActiveUserInfo();


        return Inertia::render('Main/Dashboard', [
            "allLinks" => $allUser,
            "selectedView" => 10,
            "respon" => Str::random(15),
            "userActive" => $userInfo,
            "callPopUp" => [7, ""],
        ]);
    }

    public function resetPassword(Request $user)
    {
        $resetPassword = User::find(intval($user->id));

        $resetPassword->password = Hash::make($user->newPassword);
        $resetPassword->save();

        $allUser = getAllUsersAccount();
        $userInfo = getActiveUserInfo();
        $getUser = User::find(intval($user->id));

        return Inertia::render('Main/Dashboard', [
            "allLinks" => $allUser,
            "selectedView" => 10,
            "respon" => Str::random(15),
            "userActive" => $userInfo,
            "callPopUp" => [8, ""],
            "showAddUserBox" => 1,
            "showAddUserBoxisEditing" => 1,
            "editedUser" => $getUser,
        ]);
    }
}
