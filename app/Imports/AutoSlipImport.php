<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStartRow;

class AutoSlipImport implements WithMapping, WithHeadingRow
{
    public function map($row): array
    {
        return [
            'no' => $row['no'],
            'nid' => $row['nid'],
            'nama' => $row['nama'],
            'jabatan' => $row['jabatan'],
            'gol' => $row['gol'],
            'tmt' => $row['tmt'],
            'gaji_pokok' => $row['gaji_pokok'],
            'tunjangan_jabatan' => $row['tunjangan_jabatan'],
            'jabfung' => $row['jabfung'],
            'subsidi_kesehatan' => $row['subsidi_kesehatan'],
            'subsidi_ketenagakerjaan' => $row['subsidi_ketenagakerjaan'],
            'subsidi_jaminan_pensiun' => $row['subsidi_jaminan_pensiun'],
            'tunjangan_kpi' => $row['tunjangan_kpi'],
            'insentif_uas' => $row['insentif_uas'],
            'tunjangan_doktor' => $row['tunjangan_doktor'],
            'kelebihan_mengajar' => $row['kelebihan_mengajar'],
            'jumlah_gaji' => $row['jumlah_gaji'],
            'bpjs_kesehatan_1' => $row['bpjs_kesehatan_1'],
            'bpjs_kesehatan_2' => $row['bpjs_kesehatan_2'],
            'bpjs_ketenagakerjaan_1' => $row['bpjs_ketenagakerjaan_1'],
            'bpjs_ketenagakerjaan_2' => $row['bpjs_ketenagakerjaan_2'],
            'bpjs' => $row['bpjs'],
            'koperasi_wajib' => $row['koperasi_wajib'],
            'pinjaman' => $row['pinjaman'],
            'koperasi_pinjaman' => $row['koperasi_pinjaman'],
            'potongan_20' => $row['potongan_20'],
            'jpzis' => $row['jpzis'],
            'jumlah_potongan' => $row['jumlah_potongan'],
            'diterimakan' => $row['diterimakan'],
            'no_rekening' => $row['no_rekening'],
        ];
    }

    // public function startRow(): int
    // {
    //     return 8;
    // }
}
