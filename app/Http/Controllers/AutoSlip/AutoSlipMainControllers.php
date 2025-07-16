<?php

namespace App\Http\Controllers\AutoSlip;

use Carbon\Carbon;
use Inertia\Inertia;
use App\Mail\SendSlip;
use App\Mail\SendSlipGaji;
use App\Mail\MailerSendTest;
use Illuminate\Http\Request;
use App\Imports\AutoSlipImport;
use App\Http\Controllers\Controller;
use App\Models\BpfdsExcelFilesModel;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Storage;
use App\Imports\AutoSlipFirstSheetImport;
use App\Mail\MailerSendProd;
use App\Mail\MailTrapDriver;

function replaceSpacesWithUnderscores($inputString)
{
    // Use str_replace to replace spaces with underscores
    $outputString = str_replace(' ', '_', $inputString);

    return $outputString;
}

function replaceSpacesAndNonAlphabetChars($inputString)
{
    // Replace spaces with underscores
    $outputString = str_replace(' ', '_', $inputString);

    // Remove non-alphabet characters using regex
    $outputString = preg_replace('/[^a-zA-Z_]/', '', $outputString);

    return $outputString;
}

function formatDate(Carbon $date)
{
    return $date->format('d_m_y_H_i_s');
}

class AutoSlipMainControllers extends Controller
{
    //
    public function home()
    {

        return Inertia::render(('AutoSlip/AutoSlipMain'));
    }

    public function upFile(Request $file)
    {
        $tempFile = null;

        $uploadSuccess = false;
        $filename = formatDate(Carbon::now()) . ".xlsx";

        // if ($file->hasFile('excel_file') && $file->file('excel_file')->isValid()) {
        //     $tempFile = $file->excel_file;

        //     // $good = Storage::disk('public')->put('excel/file.xlsx', $tempFile);
        //     $good = Storage::disk('public')->putFileAs('excel/', $tempFile, $filename);

        //     if ($good === false) {
        //         return response()->json(["good" => false]);
        //     } else {
        //         $saveExcelDB = new BpfdsExcelFilesModel;

        //         $saveExcelDB->filename = $filename;
        //         $saveExcelDB->view_count = 0;
        //         $saveExcelDB->active = 1;

        //         $saveExcelDB->save();

        //         $uploadSuccess = true;
        //     }
        // }

        if ($file->hasFile('excel_file') && $file->file('excel_file')->isValid()) {
            $tempFile = $file->excel_file;
            // $filePath = Storage::url('public/excel/' . $filename);
            // $filePath = "http://localhost:8000/storage/excel/26_03_24_04_59_34.xlsx";

            // $sheetName = Excel::getSheetNames($tempFile)[0];
            $data = Excel::toCollection(new AutoSlipFirstSheetImport(), $tempFile);

            // return response()->json($data);
            // $keyPair = [];

            $formatData = [
                'no' => [],
                'nid' => [],
                'nama' => [],
                'jabatan' => [],
                'gol' => [],
                'tmt' => [],
                'gaji_pokok' => [],
                'tunjangan_jabatan' => [],
                'jabfung' => [],
                'subsidi_kesehatan' => [],
                'subsidi_ketenagakerjaan' => [],
                'subsidi_jaminan_pensiun' => [],
                'tunjangan_kpi' => [],
                'insentif_uas' => [],
                'tunjangan_doktor' => [],
                'kelebihan_mengajar' => [],
                'jumlah_gaji' => [],
                'bpjs_kesehatan_1' => [],
                'bpjs_kesehatan_2' => [],
                'bpjs_ketenagakerjaan_1' => [],
                'bpjs_ketenagakerjaan_2' => [],
                'bpjs' => [],
                'koperasi_wajib' => [],
                'pinjaman' => [],
                'koperasi_pinjaman' => [],
                'potongan_20' => [],
                'jpzis' => [],
                'jumlah_potongan' => [],
                'diterimakan' => [],
                'no_rekening' => [],
            ];

            // foreach ($data[0] as $row) {
            //     $formatData['no'][] = $row['no'];
            //     $formatData['nid'][] = $row['nid'];
            //     $formatData['nama'][] = $row['nama'];
            //     $formatData['jabatan'][] = $row['jabatan'];
            //     $formatData['gol'][] = $row['gol'];
            //     $formatData['tmt'][] = $row['tmt'];
            //     $formatData['gaji_pokok'][] = $row['gaji_pokok'];
            //     $formatData['tunjangan_jabatan'][] = $row['tunjangan_jabatan'];
            //     $formatData['jabfung'][] = $row['jabfung'];
            //     $formatData['subsidi_kesehatan'][] = $row['subsidi_kesehatan'];
            //     $formatData['subsidi_ketenagakerjaan'][] = $row['subsidi_ketenagakerjaan'];
            //     $formatData['subsidi_jaminan_pensiun'][] = $row['subsidi_jaminan_pensiun'];
            //     $formatData['tunjangan_kpi'][] = $row['tunjangan_kpi'];
            //     $formatData['insentif_uas'][] = $row['insentif_uas'];
            //     $formatData['tunjangan_doktor'][] = $row['tunjangan_doktor'];
            //     $formatData['kelebihan_mengajar'][] = $row['kelebihan_mengajar'];
            //     $formatData['jumlah_gaji'][] = $row['jumlah_gaji'];
            //     $formatData['bpjs_kesehatan_1'][] = $row['bpjs_kesehatan_1'];
            //     $formatData['bpjs_kesehatan_2'][] = $row['bpjs_kesehatan_2'];
            //     $formatData['bpjs_ketenagakerjaan_1'][] = $row['bpjs_ketenagakerjaan_1'];
            //     $formatData['bpjs_ketenagakerjaan_2'][] = $row['bpjs_ketenagakerjaan_2'];
            //     $formatData['bpjs'][] = $row['bpjs'];
            //     $formatData['koperasi_wajib'][] = $row['koperasi_wajib'];
            //     $formatData['pinjaman'][] = $row['pinjaman'];
            //     $formatData['koperasi_pinjaman'][] = $row['koperasi_pinjaman'];
            //     $formatData['potongan_20'][] = $row['potongan_20'];
            //     $formatData['jpzis'][] = $row['jpzis'];
            //     $formatData['jumlah_potongan'][] = $row['jumlah_potongan'];
            //     $formatData['diterimakan'][] = $row['diterimakan'];
            //     $formatData['no_rekening'][] = $row['no_rekening'];
            // }

            $saveExcelDB = new BpfdsExcelFilesModel;

            $saveExcelDB->filename = $filename;
            $saveExcelDB->view_count = 0;
            $saveExcelDB->active = 1;
            $saveExcelDB->raw_data = json_encode($data);

            $saveExcelDB->save();

            $targetData = BpfdsExcelFilesModel::where('filename', $filename)->first();

            $arrData = json_decode($targetData->raw_data);

            return response()->json(["good" => true, "file" => $arrData, "filename" => $filename]);
        }

        return response(["good" => false]);
    }

    public function loadFile(Request $file)
    {
        $filename = $file->validate([
            'filename' => 'string'
        ]);

        $getFIle = BpfdsExcelFilesModel::where('filename', $filename['filename'])->first();

        if ($getFIle) {
            $arrData = json_decode($getFIle->raw_data);

            return response()->json(['good' => true, "file" => $arrData]);
        }

        return response()->json(["good" => false]);
    }

    // Bug with send PDF Attacment
    public function sendMail(Request $request)
    {
        if ($request->hasFile('filerr') && $request->file('filerr')->isValid()) {
            return response()->json(["haha" => "good"]);
        } else {
            response()->json(["haha" => "false"]);
        }
        // return response()->json(["haha" => $request->filerr]);
        // $pdf = $request->file;

        // Debug =====
        // if ($request->hasFile('file') && $request->file('file')->isValid()) {
        //     return response()->json(["good" => true]);
        // }
        // $peopleName = replaceSpacesAndNonAlphabetChars($request->name);

        // // $file = $request->file('file');
        // $fileName = time() . '_' . $peopleName . ".pdf";

        // $folder = "public/";
        // $attachment = $request->file('file')->storeAs($folder . 'attachments', $fileName);
        // $attachmentPath = 'storage/attachments/' . $fileName;


        // return response()->json($attachmentPath);
        // End

        $email = $request->email;
        $name = $request->name;
        $bank_rek = $request->bank_rek;
        $no_rek = $request->no_rek;
        $jabatan = $request->jabatan;

        $arrData = [$name, $bank_rek, $no_rek, $email, $jabatan];

        $envMailDebug = env('MAIL_DEBUG', true);

        $good = false;

        // sleep(5);

        if ($envMailDebug) {
            if ($request->hasFile('file') && $request->file('file')->isValid()) {
                return response()->json(["good" => true, 'message' => 'File Included, Email sent successfully'], 200);
            }
            return response()->json(["good" => true, 'message' => 'Email sent successfully'], 200);
        }

        if ($request->hasFile('file') && $request->file('file')->isValid()) {

            $theFile = $request->file('file');

            $peopleName = replaceSpacesAndNonAlphabetChars($request->name);

            // $file = $request->file('file');
            $fileName = time() . '_' . $peopleName . ".pdf";

            if ($envMailDebug === false) {

                // $folder = "public/";
                // $attachment = $request->file('file')->storeAs($folder . 'attachments', $fileName);
                // $attachmentPath = 'storage/attachments/' . $fileName;

                Storage::disk('local')->put('pdfs/' . $fileName, file_get_contents($theFile[0]));

                $filePath = Storage::disk('local')->path('pdfs/' . $fileName);

                try {
                    // MailerSend Driver
                    // Mail::to($email)
                    //     ->send(new MailerSendProd($attachmentPath, $arrData));

                    // MailTrap Driver
                    Mail::to($email)
                        ->send(new MailTrapDriver($filePath, $arrData));
                    return response()->json(["good" => true, 'message' => 'Email sent successfully'], 200);
                } catch (\Exception $e) {
                    $errorMessage = $e->getMessage();
                    return response()->json(["good" => false, 'error' => $errorMessage], 500);
                }
                // $good = Mail::to($email)
                //     ->send(new MailerSendTest($attachmentPath, $arrData));

                // if ($good) {
                //     return response()->json(["good" => true, "data" => $arrData]);
                // }
            }

            return response()->json(["good" => false, "mail_debug" => "true"]);
        }

        return response()->json(["good" => false, "file" => "not detected"]);
    }

    public function sendMailBAK(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf|max:10240', // Adjust max file size as needed
            'name' => 'string'
        ]);

        // Store the uploaded file
        if ($request->hasFile('file') && $request->file('file')->isValid()) {

            $peopleName = replaceSpacesAndNonAlphabetChars($request->name);

            $file = $request->file('file');
            $fileName = time() . '_' . $peopleName . ".pdf";

            // Store the file in the storage/app/public directory
            $filePath = $file->storeAs('public', $fileName);

            // Optionally, you can store the file path in the database or perform other actions

            return response()->json(['success' => true, 'message' => 'File uploaded successfully', 'file_path' => $filePath]);
        } else {
            return response()->json(['success' => false, 'message' => 'Invalid file or file upload failed'], 400);
        }
    }
}
