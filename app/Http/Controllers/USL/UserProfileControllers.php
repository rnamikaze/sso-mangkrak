<?php

namespace App\Http\Controllers\USL;

use App\Models\User;
use Inertia\Inertia;
use Illuminate\Support\Str;
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
// 9 : change password failed
// 10 : change password success


class UserProfileControllers extends Controller
{
    //
    public function showProfile()
    {
        $userId = getActiveUserInfo();
        $getUserProfile = User::find(intval($userId[0]));

        return Inertia::render('USL/Main/Dashboard', [
            "allLinks" => $getUserProfile,
            "respon" => Str::random(15),
            "userActive" => $userId,
            "selectedView" => 2,
        ]);
    }

    public function changePassword(Request $req)
    {
        $userId = getActiveUserInfo();

        $findUser = User::find(intval($userId[0]));

        if (Hash::check($req->oldPassword, $findUser->password)) {
            $findUser->password = Hash::make($req->newPassword);
            $findUser->save();

            $getUserProfile = User::find(intval($userId[0]));

            return Inertia::render('Main/Dashboard', [
                "allLinks" => $getUserProfile,
                "respon" => Str::random(15),
                "userActive" => $userId,
                "selectedView" => 2,
                "extraContainer" => [1],
                "callPopUp" => [10, ""]
            ]);
        }

        $getUserProfile = User::find(intval($userId[0]));

        return Inertia::render('Main/Dashboard', [
            "allLinks" => $getUserProfile,
            "respon" => Str::random(15),
            "userActive" => $userId,
            "selectedView" => 2,
            "extraContainer" => [1],
            "callPopUp" => [9, ""]
        ]);
    }
}
