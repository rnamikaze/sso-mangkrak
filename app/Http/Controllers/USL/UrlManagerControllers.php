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

function sanitizeWithSpaces($string)
{
    // Convert to UTF-8 to ensure proper character handling
    $string = mb_convert_encoding($string, 'UTF-8', 'auto');

    // Replace different types of dashes with a standard hyphen
    $string = str_replace(["–", "—", "―"], "-", $string);

    // Remove all characters that are not alphanumeric, dash, underscore, dot, or space
    $string = preg_replace('/[^a-zA-Z0-9-_\. ]/', '', $string);

    return $string;
}

function getActiveUserInfo()
{
    $user = User::find(intval(Auth::id()));
    $userInfo = [$user->id, $user->email, $user->name, $user->nik];

    return $userInfo;
}

function getAllActiveShortLinks($selectedId = 0, $isDescending = true)
{
    $allLinks = null;
    $activeUserId = $selectedId === 0 ? Auth::id() : $selectedId;

    if ($isDescending) {
        $allLinks = UslShortLink::where('owned_by', intval($activeUserId))->orderBy('created_at', 'desc')->get();
    } else {
        $allLinks = UslShortLink::where('owned_by', intval($activeUserId))->get();
    }

    for ($i = 0; $i < sizeof($allLinks); $i++) {
        $formattedDate = Carbon::parse($allLinks[$i]['updated_at'])->format('l, d/m/Y');
        $allLinks[$i]['formatted_date'] = $formattedDate;

        $formattedDate = Carbon::parse($allLinks[$i]['created_at'])->format('l, d/m/Y');
        $allLinks[$i]['created_at_formatted'] = $formattedDate;
    }

    return $allLinks;
}

class UrlManagerControllers extends Controller
{
    //
    public function checkAvailability(Request $newUrl)
    {
        $isAlreadyAvailable = UslShortLink::where('url', $newUrl->alias)->exists();

        $allLinks = getAllActiveShortLinks();

        return Inertia::render('USL/Main/Dashboard', [
            "allLinks" => $allLinks,
            "newLinkStatus" => $isAlreadyAvailable,
            "respon" => Str::random(15)
        ]);
    }

    public function checkAvailabilityFromEdit(Request $newUrl)
    {
        $isAlreadyAvailable = UslShortLink::where('url', $newUrl->alias)->exists();

        // $allLinks = getAllActiveShortLinks();
        $editLink = UslShortLink::find(intval($newUrl->id));

        return Inertia::render('USL/Main/Dashboard', [
            "allLinks" => $editLink,
            "newLinkStatus" => $isAlreadyAvailable,
            "selectedView" => 97,
            "editId" => [intval($newUrl->id), $newUrl->alias],
            "respon" => Str::random(15)
        ]);
    }

    // public function createNew(Request $newUrl)
    // {
    //     $isAlreadyAvailable = UslShortLink::where('url', $newUrl->alias)->exists();

    //     $allLinks = UslShortLink::where('active', 1)->get();

    //     for ($i = 0; $i < sizeof($allLinks); $i++) {
    //         $formattedDate = Carbon::parse($allLinks[$i]['updated_at'])->format('l, d/m/Y');
    //         $allLinks[$i]['formatted_date'] = $formattedDate;
    //     }

    //     return Inertia::render('Main/Dashboard', [
    //         "allLinks" => $allLinks,
    //         "newLinkStatus" => $isAlreadyAvailable
    //     ]);
    // }

    // public function createNew(Request $newUrl)
    // {
    //     $newShortUrl = new UslShortLink;

    //     $newShortUrl->url = $newUrl->alias;
    //     $newShortUrl->domain_id = 1;
    //     $newShortUrl->prefix = "none";
    //     $newShortUrl->destination_url = $newUrl->newUrl;
    //     $newShortUrl->visitor_count = 0;
    //     $newShortUrl->active = 0;
    //     $newShortUrl->title = $newUrl->title;
    //     $newShortUrl->owned_by = $newUrl->idUserActive;

    //     $newShortUrl->save();

    //     $allLinks = getAllActiveShortLinks();

    //     return Inertia::render('USL/Main/Dashboard', [
    //         "allLinks" => $allLinks,
    //         "respon" => Str::random(15),
    //     ]);
    // }

    public function createNew(Request $newUrl)
    {
        $newShortUrl = new UslShortLink;

        $newShortUrl->url = $newUrl->alias;
        $newShortUrl->domain_id = 1;
        $newShortUrl->prefix = "none";
        $newShortUrl->destination_url = $newUrl->newUrl;
        $newShortUrl->visitor_count = 0;
        $newShortUrl->active = 0;
        $newShortUrl->title = sanitizeWithSpaces($newUrl->title);
        $newShortUrl->owned_by = $newUrl->idUserActive;

        $newShortUrl->save();

        $allLinks = getAllActiveShortLinks();

        return Inertia::render('USL/Main/Dashboard', [
            "allLinks" => $allLinks,
            "respon" => Str::random(15),
        ]);
    }

    public function saveEdit(Request $newUrl)
    {
        $isSame = false;
        $doCheck = UslShortLink::find(intval($newUrl->id));

        if ($doCheck->url === $newUrl->alias) $isSame = true;

        if ($isSame) {
            $newShortUrl = UslShortLink::find(intval($newUrl->id));

            // $newShortUrl->url = $newUrl->alias;
            $newShortUrl->destination_url = $newUrl->newUrl;
            $newShortUrl->title = $newUrl->title;
            // $newShortUrl->visitor_count = 1;
            // $newShortUrl->active = 1;

            $newShortUrl->save();

            // $editLink = UslShortLink::find(intval($newUrl->id));
            $allLinks = getAllActiveShortLinks();

            return Inertia::render('USL/Main/Dashboard', [
                "allLinks" => $allLinks,
                "selectedView" => 0,
                "respon" => Str::random(15),
                "callPopUp" => [1, ""],
            ]);
        } else {
            $isAlreadyAvailable = UslShortLink::where('url', $newUrl->alias)->exists();

            if ($isAlreadyAvailable === false) {
                $newShortUrl = UslShortLink::find(intval($newUrl->id));

                $newShortUrl->url = $newUrl->alias;
                $newShortUrl->destination_url = $newUrl->newUrl;
                $newShortUrl->title = $newUrl->title;
                // $newShortUrl->visitor_count = 1;
                // $newShortUrl->active = 1;

                $newShortUrl->save();

                $allLinks = getAllActiveShortLinks();
                return Inertia::render('USL/Main/Dashboard', [
                    "allLinks" => $allLinks,
                    "selectedView" => 0,
                    "respon" => Str::random(15),
                    "callPopUp" => [1, ""],
                ]);
            } else {
                $editLink = UslShortLink::find(intval($newUrl->id));

                return Inertia::render('USL/Main/Dashboard', [
                    "allLinks" => $editLink,
                    "selectedView" => 97,
                    "respon" => Str::random(15),
                    "newLinkStatus" => true,
                    "callPopUp" => [2, $newUrl->alias],
                ]);
            }
        }
    }

    public function getEditLink(Request $id)
    {
        $editLink = UslShortLink::find(intval($id->id));
        $userInfo = getActiveUserInfo();

        return Inertia::render('USL/Main/Dashboard', [
            "allLinks" => $editLink,
            "selectedView" => 97,
            "respon" => Str::random(15),
            "userActive" => $userInfo
        ]);
    }

    public function getAllLinks()
    {

        $allLinks = getAllActiveShortLinks();
        $userInfo = getActiveUserInfo();

        // for ($i = 0; $i < sizeof($allLinks) - 1; $i++) {
        //     $tmp = date('d-m-Y', strtotime($allLinks[$i]->created_at));
        //     $allLinks['created_at_formated'] = $tmp;
        // }

        return Inertia::render('USL/Main/Dashboard', [
            "allLinks" => $allLinks,
            "selectedView" => 0,
            "respon" => Str::random(15),
            "userActive" => $userInfo
        ]);
    }

    public function getUserInfo()
    {
        $userInfo = getActiveUserInfo();

        return response()->json($userInfo);
    }

    // INFINITE SCROLL ~~~~
    public function getAllLinksNew(Request $data)
    {
        $activeUserId = Auth::id();
        $perPage = $data->input('perPage', 10); // Default items per page is 10
        $page = $data->input('page', 1); // Default page is 1
        $userId = $data->input('id', null);

        $links = UslShortLink::where('owned_by', intval($userId))->orderBy('created_at', 'desc')->paginate($perPage, ['*'], 'page', $page);
        $maxPage = $links->lastPage();

        return response()->json([
            'links' => $links->items(), // Paginated data
            'maxPage' => $maxPage, // Maximum available page number
        ]);

        // return Inertia::render('USL/Main/Dashboard', [
        //     "allLinks" => $allLinks,
        //     "selectedView" => 0,
        //     "respon" => Str::random(15),
        //     "userActive" => $userInfo
        // ]);
    }

    public function getAllLinksOld()
    {
        $allLinks = getAllActiveShortLinks();

        return response()->json($allLinks);
    }

    public function getSelectedLinks(Request $obj)
    {
        $allLinks = getAllActiveShortLinks(intval($obj->id));
        $userInfo = getActiveUserInfo();

        // for ($i = 0; $i < sizeof($allLinks) - 1; $i++) {
        //     $tmp = date('d-m-Y', strtotime($allLinks[$i]->created_at));
        //     $allLinks['created_at_formated'] = $tmp;
        // }

        return Inertia::render('USL/Main/Dashboard', [
            "allLinks" => $allLinks,
            "selectedView" => 10,
            "respon" => Str::random(15),
            "userActive" => $userInfo,
            "showHistory" => 1
        ]);
    }

    public function deleteLink(Request $delId)
    {
        $deleteLink = UslShortLink::find(intval($delId->id));

        $deleteLink->delete();

        $allLinks = getAllActiveShortLinks();
        $userInfo = getActiveUserInfo();

        return Inertia::render('USL/Main/Dashboard', [
            "allLinks" => $allLinks,
            "selectedView" => 0,
            "respon" => Str::random(15),
            "userActive" => $userInfo
        ]);

        // return $delId;
    }

    public function changeLinkActive(Request $changeId)
    {
        $changeLink = UslShortLink::find($changeId->id);

        $val = intval($changeId->active) === 1 ? 0 : 1;

        $changeLink->active = $val;

        $changeLink->save();

        $allLinks = getAllActiveShortLinks();

        return Inertia::render('USL/Main/Dashboard', [
            "allLinks" => $allLinks,
            "selectedView" => 0,
            "respon" => Str::random(15)
        ]);
    }

    public function getUserActiveInfo(Request $data)
    {
        // Expected Payload
        // {
        //     idUserActive: 0,
        //     email: "",
        // }

        // $targetId = intval($data->id);
        $targetId = Auth::id();

        $userInfo = User::find($targetId);

        $idUser = $userInfo->id;
        $email = $userInfo->email;
        $fullname = $userInfo->name;

        $payload = ["idUserActive" => $idUser, "email" => $email];
        $payload1 = [$idUser, $email, $fullname];

        return response()->json([$payload, $payload1]);
    }
}
