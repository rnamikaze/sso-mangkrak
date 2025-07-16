<?php

namespace App\Http\Controllers;

use App\Models\PengajuanIzin;
use App\Models\SIK\SikBiodata;
use App\Models\SIK\SikJabatanStrukDos;
use App\Models\SIK\SikJabatanStruktural;
use App\Models\SIK\SikPengajuanCuti;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

function determineSdm()
{
    // Get the authenticated user
    $user = Auth::user();

    // Assuming your user model has a 'username' attribute
    $username = $user->username;

    if ($username == "adminsdmunusida") {
        return 93;
    }
    return Auth::id();
}

class PengajuanCutiControllers extends Controller
{
    //
    public function determineUserType()
    {
        // Status 1 atau 3
        // Satu Unit
        // 0 : Pegawai -> jabatan_level: not 2
        // 1 : Kepala Biro -> jabatan_level: 2

        // Status 2 atau 4
        // Satu Fakultas
        // 0 : Pegawai -> strukdos_id: not 1 -> level_jsd: not 0
        // 1 : Dekan -> strukdos_id: 1 level_jsd: 0

        // 2 : Kepala SDM -> struktural_id: 10

        $userType = 0;

        $activeId = determineSdm();

        // Get the authenticated user
        $user = Auth::user();

        // Assuming your user model has a 'username' attribute
        $username = $user->username;

        if ($username == "adminsdmunusida") {
            $userType = 2;

            return response()->json(["success" => true, "userType" => $userType]);
        }

        $targetBio = SikBiodata::where('master_id', $activeId)->first();

        $userStatus = intval($targetBio->status);
        $struktural_id = intval($targetBio->jabatan_struktural_id);

        // 10 is jabatan_struktural_id for kepala SDM
        if ($struktural_id === 10) {
            $userType = 2;

            return response()->json(["success" => true, "userType" => $userType]);
        }

        $strukdos_id = intval($targetBio->jabatan_strukdos_id);

        if ($userStatus === 1 || $userStatus === 3) {
            $targetStruktural = SikJabatanStruktural::where('id', $struktural_id)->first();

            $jabatan_level = intval($targetStruktural->jabatan_level);

            if ($jabatan_level === 2) {
                $userType = 1;
            }
        } else if ($userStatus === 2 || $userStatus === 4) {
            $targetStrukdos = SikJabatanStrukDos::where('id', $strukdos_id)->first();

            $level_jsd = intval($targetStrukdos->level_jsd);

            if ($level_jsd === 0) {
                $userType = 1;
            }
        }

        return response()->json(["success" => true, "userType" => $userType]);
    }

    public function getKawanSeunit()
    {
        $activeId = intval(determineSdm());

        $targetBio = SikBiodata::where('master_id', $activeId)->first();

        $statusKep = intval($targetBio->status);

        // $unitId = null;
        if ($statusKep === 1 || $statusKep === 3) {
            $unitId = $targetBio->unit_id;

            $allKawan = SikBiodata::where('unit_id', $unitId)->whereIn('status', [1, 3])->where('active', 1)->get();

            return response()->json(["success" => true, "allKawan" => $allKawan, $targetBio]);
        } else if ($statusKep === 2 || $statusKep === 4) {
            $unitId = $targetBio->fakultas_id;

            $allKawan = SikBiodata::where('fakultas_id', $unitId)->whereIn('status', [2, 4])->where('active', 1)->get();

            return response()->json(["success" => true, "allKawan" => $allKawan, $targetBio]);
        }

        return response()->json(["success" => false, "allKawan" => [], $targetBio]);
    }

    // 0 : Diterima Kepala / Menunggu Persetujuan Kepala
    // 1 : Ditolak Kepala

    // 2 : Diterima SDM / Menunggu Persetujuan SDM
    // 3 : Ditolak SDM / Revisi Kepala / Menunggu Keputusan Kepala
    // 8 : Ditolak SDM (Pengajuan Ulang) / Revisi Kepala / Menunggu Keputusan SDM

    // 4 : Ditolak SDM / Ditolak Kepala
    // 5 : Ditolak SDM / Revisi Pengaju

    // 6 : Disetujui SDM / Menunggu Konfirmasi Kepala
    // 7 : Disetujui SDM / Pengaju Cuti

    public function sendPengajuan(Request $req)
    {
        $activeId = determineSdm();

        $validated = $req->validate([
            'cutiType' => 'required',
            'cutiDateArr' => 'required|json',
            'pengajuanType' => 'required',
            'idPegawaiPenugasan'  => 'required',
            // 'komentar',
            // 'bukti_arr',
            // 'status_pengajuan',
            // 'active'
        ]);

        $newPengajuan = new SikPengajuanCuti;

        $newPengajuan->cuti_type = $validated['cutiType'];
        $newPengajuan->cuti_date_arr = $validated['cutiDateArr'];
        $newPengajuan->pengajuan_type = $validated['pengajuanType'];
        $newPengajuan->id_pegawai_penugasan = $validated['idPegawaiPenugasan'];

        $newPengajuan->id_pengaju = $activeId;

        if ($newPengajuan->save()) {
            return response()->json(["success" => true, "message" => "Pengajuan Dikirim !"]);
        } else {
            return response()->json(["success" => false, "message" => "Terjadi Kesalahan !"]);
        }

        return response()->json(["success" => false, "message" => "Error !"]);
    }

    public function sendPengajuanIzin(Request $req)
    {
        $activeId = determineSdm();

        $validated = $req->validate([
            'deskripsi_izin' => 'required',
            'cuti_date_arr' => 'required|json',
            'file_pendukung_arr'  => 'required|json',
            // 'komentar',
            // 'bukti_arr',
            // 'status_pengajuan',
            // 'active'
        ]);

        $newPengajuan = new PengajuanIzin;

        $newPengajuan->deskripsi_izin = $validated['deskripsi_izin'];
        $newPengajuan->cuti_date_arr = $validated['cuti_date_arr'];
        $newPengajuan->file_pendukung_arr = $validated['file_pendukung_arr'];
        // $newPengajuan->id_pegawai_penugasan = $validated['idPegawaiPenugasan'];

        $newPengajuan->id_pengaju = $activeId;
        $newPengajuan->cuti_type = 2;

        if ($newPengajuan->save()) {
            return response()->json(["success" => true, "message" => "Pengajuan Dikirim !"]);
        } else {
            return response()->json(["success" => false, "message" => "Terjadi Kesalahan !"]);
        }

        return response()->json(["success" => false, "message" => "Error !"]);
    }

    public function getPengajuanMasuk(Request $req)
    {
        $statusIn = [0, 2, 3, 6];

        $statusFil = $statusIn;
        $filterVal = null;

        if ($req->filter != "empty" && $req->filter != null) {
            $filterVal = intval($req->filter);
            $statusFil = [$filterVal];
        }

        $activeId = determineSdm();

        $allPengajuan = SikPengajuanCuti::where('id_pengaju', $activeId)->whereIn('status_pengajuan', $statusFil)->orderBy('created_at', 'desc')->where('active', true)->get();
        $allPengajuanIzin = PengajuanIzin::where('id_pengaju', $activeId)->whereIn('status_pengajuan', $statusFil)->orderBy('created_at', 'desc')->where('active', true)->get();

        for ($i = 0; $i < sizeof($allPengajuan); $i++) {
            $allPengajuan[$i]->formatted_date = Carbon::parse($allPengajuan[$i]->created_at)->format('d-m-Y');
        }
        for ($i = 0; $i < sizeof($allPengajuanIzin); $i++) {
            $allPengajuanIzin[$i]->formatted_date = Carbon::parse($allPengajuanIzin[$i]->created_at)->format('d-m-Y');
        }

        return response()->json(["allPengajuan" => $allPengajuan, "allPengajuanIzin" => $allPengajuanIzin, "statusIn" => $statusIn, "debug" => $req->filter]);
    }

    public function getPengajuanDiterima(Request $req)
    {
        // $statusIn = [0, 2, 3, 6];

        // $statusFil = $statusIn;
        // $filterVal = null;

        // if ($req->filter != "empty") {
        //     $filterVal = intval($req->filter);
        //     $statusFil = [$filterVal];
        // }

        $activeId = determineSdm();

        $allPengajuan = SikPengajuanCuti::where('id_pengaju', $activeId)->where('status_pengajuan', 7)->orderBy('created_at', 'desc')->where('active', true)->get();
        $allPengajuanIzin = PengajuanIzin::where('id_pengaju', $activeId)->where('status_pengajuan', 7)->orderBy('created_at', 'desc')->where('active', true)->get();

        for ($i = 0; $i < sizeof($allPengajuan); $i++) {
            $allPengajuan[$i]->formatted_date = Carbon::parse($allPengajuan[$i]->created_at)->format('d-m-Y');
        }
        for ($i = 0; $i < sizeof($allPengajuanIzin); $i++) {
            $allPengajuanIzin[$i]->formatted_date = Carbon::parse($allPengajuanIzin[$i]->created_at)->format('d-m-Y');
        }

        return response()->json(["allPengajuan" => $allPengajuan, "allPengajuanIzin" => $allPengajuanIzin]);
    }

    public function getPengajuanDitolak(Request $req)
    {
        $statusIn = [1, 4, 5, 8];

        $statusFil = $statusIn;
        $filterVal = null;

        if ($req->filter != "empty" && $req->filter != null) {
            $filterVal = intval($req->filter);
            $statusFil = [$filterVal];
        }

        $activeId = determineSdm();

        $allPengajuan = SikPengajuanCuti::where('id_pengaju', $activeId)->whereIn('status_pengajuan', $statusFil)->orderBy('created_at', 'desc')->where('active', true)->get();
        $allPengajuanIzin = PengajuanIzin::where('id_pengaju', $activeId)->whereIn('status_pengajuan', $statusFil)->orderBy('created_at', 'desc')->where('active', true)->get();

        for ($i = 0; $i < sizeof($allPengajuan); $i++) {
            $allPengajuan[$i]->formatted_date = Carbon::parse($allPengajuan[$i]->created_at)->format('d-m-Y');
        }
        for ($i = 0; $i < sizeof($allPengajuanIzin); $i++) {
            $allPengajuanIzin[$i]->formatted_date = Carbon::parse($allPengajuanIzin[$i]->created_at)->format('d-m-Y');
        }

        return response()->json(["allPengajuan" => $allPengajuan, "allPengajuanIzin" => $allPengajuanIzin, "statusIn" => $statusIn, "debug" => $activeId]);
    }

    // public function getPengajuanMasukIzin(Request $req)
    // {
    //     $activeId = determineSdm();

    //     $allPengajuan = PengajuanIzin::where('id_pengaju', $activeId)->orderBy('created_at', 'desc')->where('active', true)->get();

    //     return response()->json(["allPengajuanIzin" => $allPengajuan]);
    // }

    public function getPengajuanKepalaKotakSdm(Request $req)
    {
        // $statusIn = [0, 1];

        // $statusFil = $statusIn;
        // $filterVal = null;

        // if ($req->filter != null) {
        //     $filterVal = intval($req->filter);
        //     $statusFil = [$filterVal];
        // }

        $allPengajuan = SikPengajuanCuti::whereIn('status_pengajuan', [2])->orderBy('created_at', 'desc')->get();
        $allPengajuanIzin = PengajuanIzin::whereIn('status_pengajuan', [2])->orderBy('created_at', 'desc')->get();

        for ($i = 0; $i < sizeof($allPengajuan); $i++) {
            $bio = SikBiodata::where('master_id', intval($allPengajuan[$i]->id_pengaju))->select('fullname')->first();
            $allPengajuan[$i]->fullname = $bio->fullname;
            $allPengajuan[$i]->formatted_date = Carbon::parse($allPengajuan[$i]->created_at)->format('d-m-Y');
        }
        for ($i = 0; $i < sizeof($allPengajuanIzin); $i++) {
            $bio = SikBiodata::where('master_id', intval($allPengajuanIzin[$i]->id_pengaju))->select('fullname')->first();
            $allPengajuanIzin[$i]->fullname = $bio->fullname;
            $allPengajuanIzin[$i]->formatted_date = Carbon::parse($allPengajuanIzin[$i]->created_at)->format('d-m-Y');
        }

        return response()->json(["success" => true, "allPengajuan" => $allPengajuan, "allPengajuanIzin" => $allPengajuanIzin]);
    }

    public function getTerkirimKepalaKotakSdm(Request $req)
    {
        $statusIn = [3, 4, 5, 6, 7, 8];

        $statusFil = $statusIn;
        $filterVal = null;

        if ($req->filter != null) {
            $filterVal = intval($req->filter);
            $statusFil = [$filterVal];
        }

        $allPengajuan = SikPengajuanCuti::whereIn('status_pengajuan', $statusFil)->orderBy('created_at', 'desc')->get();
        $allPengajuanIzin = PengajuanIzin::whereIn('status_pengajuan', $statusFil)->orderBy('created_at', 'desc')->get();

        for ($i = 0; $i < sizeof($allPengajuan); $i++) {
            $bio = SikBiodata::where('master_id', intval($allPengajuan[$i]->id_pengaju))->select('fullname')->first();
            $allPengajuan[$i]->fullname = $bio->fullname;
            $allPengajuan[$i]->formatted_date = Carbon::parse($allPengajuan[$i]->created_at)->format('d-m-Y');
        }
        for ($i = 0; $i < sizeof($allPengajuanIzin); $i++) {
            $bio = SikBiodata::where('master_id', intval($allPengajuanIzin[$i]->id_pengaju))->select('fullname')->first();
            $allPengajuanIzin[$i]->fullname = $bio->fullname;
            $allPengajuanIzin[$i]->formatted_date = Carbon::parse($allPengajuanIzin[$i]->created_at)->format('d-m-Y');
        }

        return response()->json(["success" => true, "allPengajuan" => $allPengajuan, "allPengajuanIzin" => $allPengajuanIzin, "statusIn" => $statusIn, "debug" => $req->filter]);
    }

    public function getPengajuanKepalaKotak(Request $req)
    {
        $statusIn = [0, 1];

        $statusFil = $statusIn;
        $filterVal = null;

        // return response()->json($req->filter);

        if ($req->filter != "empty") {
            // return response()->json("yuhu1");
            $filterVal = intval($req->filter);
            $statusFil = [$filterVal];
        }

        // return response()->json("yuhu");

        $activeId = determineSdm();

        $targetBio = SikBiodata::where('master_id', $activeId)->first();

        $status = intval($targetBio->status);

        $allKonco = null;
        $allKoncoId = [];
        // $allKoncoProps = [];

        if ($status === 1 || $status === 3) {
            $unit_id = intval($targetBio->unit_id);

            $allKonco = SikBiodata::where('unit_id', $unit_id)->whereIn('status', [1, 3])->where('active', 1)->get();

            for ($i = 0; $i < sizeof($allKonco); $i++) {
                $master_id = intval($allKonco[$i]->master_id);

                array_push($allKoncoId, $master_id);
                // array_push($allKoncoProps, [
                //     "id"=>$master_id,
                //     "name"=>$allKonco[$i]->fullname,
                // ]);
            }
        } else if ($status === 2 || $status === 4) {
            $fakultas_id = intval($targetBio->fakultas_id);

            $allKonco = SikBiodata::where('fakultas_id', $fakultas_id)->whereIn('status', [2, 4])->where('active', 1)->get();

            for ($i = 0; $i < sizeof($allKonco); $i++) {
                $master_id = intval($allKonco[$i]->master_id);

                array_push($allKoncoId, $master_id);
                // array_push($allKoncoProps, [
                //     "id"=>$master_id,
                //     "name"=>$allKonco[$i]->fullname,
                // ]);
            }
        }

        // if ($allKonco === null) {
        //     $allKonco = [];
        // }

        $allPengajuan = SikPengajuanCuti::whereIn('id_pengaju', $allKoncoId)->whereIn('status_pengajuan', $statusFil)->orderBy('created_at', 'desc')->get();
        $allPengajuanIzin = PengajuanIzin::whereIn('id_pengaju', $allKoncoId)->whereIn('status_pengajuan', $statusFil)->orderBy('created_at', 'desc')->get();

        for ($i = 0; $i < sizeof($allPengajuan); $i++) {
            $bio = SikBiodata::where('master_id', intval($allPengajuan[$i]->id_pengaju))->select('fullname')->first();
            $allPengajuan[$i]->fullname = $bio->fullname;
            $allPengajuan[$i]->formatted_date = Carbon::parse($allPengajuan[$i]->created_at)->format('d-m-Y');
        }
        for ($i = 0; $i < sizeof($allPengajuanIzin); $i++) {
            $bio = SikBiodata::where('master_id', intval($allPengajuanIzin[$i]->id_pengaju))->select('fullname')->first();
            $allPengajuanIzin[$i]->fullname = $bio->fullname;
            $allPengajuanIzin[$i]->formatted_date = Carbon::parse($allPengajuanIzin[$i]->created_at)->format('d-m-Y');
        }

        return response()->json(["success" => true, "allPengajuan" => $allPengajuan, "allPengajuanIzin" => $allPengajuanIzin, "statusIn" => $statusIn, "debug" => $req->filter, "statuseIn" => $statusFil]);
    }

    public function getTerkirimKepalaKotak(Request $req)
    {
        $statusIn = [2, 3, 4, 5, 6];

        $statusFil = $statusIn;
        $filterVal = null;

        if ($req->filter != null) {
            $filterVal = intval($req->filter);
            $statusFil = [$filterVal];
        }

        $activeId = determineSdm();

        $targetBio = SikBiodata::where('master_id', $activeId)->first();

        $status = intval($targetBio->status);

        $allKonco = null;
        $allKoncoId = [];
        // $allKoncoProps = [];

        if ($status === 1 || $status === 3) {
            $unit_id = intval($targetBio->unit_id);

            $allKonco = SikBiodata::where('unit_id', $unit_id)->whereIn('status', [1, 3])->where('active', 1)->get();

            for ($i = 0; $i < sizeof($allKonco); $i++) {
                $master_id = intval($allKonco[$i]->master_id);

                array_push($allKoncoId, $master_id);
                // array_push($allKoncoProps, [
                //     "id"=>$master_id,
                //     "name"=>$allKonco[$i]->fullname,
                // ]);
            }
        } else if ($status === 2 || $status === 4) {
            $fakultas_id = intval($targetBio->fakultas_id);

            $allKonco = SikBiodata::where('fakultas_id', $fakultas_id)->whereIn('status', [2, 4])->where('active', 1)->get();

            for ($i = 0; $i < sizeof($allKonco); $i++) {
                $master_id = intval($allKonco[$i]->master_id);

                array_push($allKoncoId, $master_id);
                // array_push($allKoncoProps, [
                //     "id"=>$master_id,
                //     "name"=>$allKonco[$i]->fullname,
                // ]);
            }
        }

        // if ($allKonco === null) {
        //     $allKonco = [];
        // }

        $allPengajuan = SikPengajuanCuti::whereIn('id_pengaju', $allKoncoId)->whereIn('status_pengajuan', $statusFil)->orderBy('created_at', 'desc')->get();
        $allPengajuanIzin = PengajuanIzin::whereIn('id_pengaju', $allKoncoId)->whereIn('status_pengajuan', $statusFil)->orderBy('created_at', 'desc')->get();

        for ($i = 0; $i < sizeof($allPengajuan); $i++) {
            $bio = SikBiodata::where('master_id', intval($allPengajuan[$i]->id_pengaju))->select('fullname')->first();
            $allPengajuan[$i]->fullname = $bio->fullname;
            $allPengajuan[$i]->formatted_date = Carbon::parse($allPengajuan[$i]->created_at)->format('d-m-Y');
        }
        for ($i = 0; $i < sizeof($allPengajuanIzin); $i++) {
            $bio = SikBiodata::where('master_id', intval($allPengajuanIzin[$i]->id_pengaju))->select('fullname')->first();
            $allPengajuanIzin[$i]->fullname = $bio->fullname;
            $allPengajuanIzin[$i]->formatted_date = Carbon::parse($allPengajuanIzin[$i]->created_at)->format('d-m-Y');
        }

        return response()->json(["success" => true, "allPengajuan" => $allPengajuan, "allPengajuanIzin" => $allPengajuanIzin, "statusIn" => $statusIn]);
    }

    public function getSelectedCuti(Request $req)
    {
        // $activeId = determineSdm();

        $validated = $req->validate([
            "pengajuan_type" => "required",
            "user_type" => "required",
            "pengajuan_id" => "required"
        ]);

        // $activeBio = SikBiodata::where('master_id', $activeId)->where('active', 1)->first();

        // $userActive

        $pengajuanType = intval($validated['pengajuan_type']);
        $userType = intval($validated['user_type']);
        $pengajuanId = intval($validated['pengajuan_id']);

        if ($pengajuanType === 1) {
            $selectedCuti = SikPengajuanCuti::where('id', $pengajuanId)->where('active', true)->first();

            $pengajuBio = SikBiodata::where('master_id', intval($selectedCuti->id_pengaju))->select('fullname', 'id')->first();

            $selectedBio = SikBiodata::where('master_id', intval($selectedCuti->id_pegawai_penugasan))->select('fullname', 'id')->first();

            $payload = [
                "id_pengajuan" => $selectedCuti->id,
                "pengajuan_type" => $selectedCuti->pengajuan_type,
                "cuti_type" => $selectedCuti->cuti_type,
                "cuti_date_arr" => $selectedCuti->cuti_date_arr,
                "id_pegawai_penugasan" => $selectedCuti->id_pegawai_penugasan,
                "name_pegawai_penugasan" => $selectedBio->fullname,
                "pengajuan_type" => $selectedCuti->pengajuan_type,
                "status_pengajuan" => $selectedCuti->status_pengajuan,
                "name_pengaju" => $pengajuBio->fullname,
                "komentar" => $selectedCuti->komentar,
                "formatted_date" => Carbon::parse($selectedCuti->created_at)->format('d-m-Y')
            ];

            return response()->json(["payload" => $payload]);
        } else if ($pengajuanType === 2) {
            $selectedIzin = PengajuanIzin::where('id', $pengajuanId)->where('active', true)->first();

            $pengajuBio = SikBiodata::where('master_id', intval($selectedIzin->id_pengaju))->select('fullname', 'id')->first();

            $payload = [
                "id_pengajuan" => $selectedIzin->id,
                // "pengajuan_type" => $selectedIzin->pengajuan_type,
                // "cuti_type" => $selectedIzin->cuti_type,
                "cuti_date_arr" => $selectedIzin->cuti_date_arr,
                // "id_pegawai_penugasan" => $selectedIzin->id_pegawai_penugasan,
                // "name_pegawai_penugasan" => $selectedBio->fullname,
                "pengajuan_type" => $selectedIzin->cuti_type,
                "deskripsi_izin" => $selectedIzin->deskripsi_izin,
                "file_pendukung_arr" => $selectedIzin->file_pendukung_arr,
                "status_pengajuan" => $selectedIzin->status_pengajuan,
                "name_pengaju" => $pengajuBio->fullname,
                "komentar" => $selectedIzin->komentar,
                "formatted_date" => Carbon::parse($selectedIzin->created_at)->format('d-m-Y')
            ];

            return response()->json(["payload" => $payload]);
        }
    }

    public function approvePengajuanAction(Request $req)
    {
        $validated = $req->validate([
            "pengajuan_id" => "required",
            "status_pengajuan" => "required",
            "pengajuan_type" => "required",
            "comment" => "nullable|string"
        ]);

        $pengajuanId = intval($validated['pengajuan_id']);
        $statusPengajuan = intval($validated['status_pengajuan']);
        $pengajuanType = intval($validated['pengajuan_type']);
        $comment = $validated['comment'];

        if ($pengajuanType === 1) {
            $targetPengajuan = SikPengajuanCuti::where('id', $pengajuanId)->where('active', true)->first();

            $targetPengajuan->status_pengajuan = $statusPengajuan;

            if ($statusPengajuan === 8) {
                $targetPengajuan->komentar = $comment;
            }

            if ($targetPengajuan->save()) {
                return response()->json(["success" => true]);
            }
            return response()->json(["success" => false]);
        } else if ($pengajuanType === 2) {
            $targetPengajuan = PengajuanIzin::where('id', $pengajuanId)->where('active', true)->first();

            $targetPengajuan->status_pengajuan = $statusPengajuan;

            if ($statusPengajuan === 8) {
                $targetPengajuan->komentar = $comment;
            }

            if ($targetPengajuan->save()) {
                return response()->json(["success" => true]);
            }
            return response()->json(["success" => false]);
        }
        return response()->json(["success" => true]);
    }
}
