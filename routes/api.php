<?php

use App\Models\SIK\SikBiodata;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:api')->get('/user-test', function (Request $request) {
    $userId = intval($request->user()->id);

    try {
        $targetBio = SikBiodata::where('master_id', $userId)->first();

        $avatarPath = "/storage/profile/" . $targetBio->nik . "/" . $targetBio->img_storage;
        $request->user()->avatar = $avatarPath;
    } catch (Exception $e) {
        $request->user()->avatar = null;

        return $request->user();
    }

    return $request->user();
});

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });
