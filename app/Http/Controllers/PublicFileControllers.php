<?php

namespace App\Http\Controllers;

use App\Models\SIK\SikBiodata;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicFileControllers extends Controller
{
    //
    public function checkItemExist(Request $path)
    {
        // return response()->json($path->filePath);
        $targetPath = 'public/' . $path->filePath; // Change this to your actual folder path

        $activeId = intval($path->id);

        if (Storage::exists($targetPath)) {
            // Item exists
            // return "Item exists!";
            return response()->json(["good" => true, "good_backup" => false, "path" => "null"]);
        } else {
            $targetBiodata = SikBiodata::where('master_id', $activeId)->first();

            if ($targetBiodata === null) {
                return response()->json(["good" => false, "good_backup" => false, "path" => "null"]);
            }

            $img_path = $targetBiodata->img_storage;

            return response()->json(["good" => false, "good_backup" => true, "path" => $img_path]);
            // Item does not exist
            // return "Item does not exist!";
        }
    }
}
