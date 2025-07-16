<?php

namespace Database\Seeders;

use App\Models\SikBaseKpi;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SikBaseKpiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $data = [
            [
                "status_kerja_id" => 1,
                "name" => "Kepala",
                "base" => 500000
            ],
            [
                "status_kerja_id" => 2,
                "name" => "Kepala Bagian/Divisi",
                "base" => 450000
            ],
            [
                "status_kerja_id" => 3,
                "name" => "Sekretaris",
                "base" => 425000
            ],
            [
                "status_kerja_id" => 4,
                "name" => "Staf Tetap",
                "base" => 400000
            ],
            [
                "status_kerja_id" => 5,
                "name" => "Staf Kontrak",
                "base" => 300000
            ],
            [
                "status_kerja_id" => 6,
                "name" => "Staf Surat Tugas (ST)",
                "base" => 200000
            ],
        ];

        foreach ($data as $status) {
            SikBaseKpi::create([
                "status_kerja_id" => $status['status_kerja_id'],
                "name" => $status['name'],
                "base" => $status['base']
            ]);

            echo "Sukses Menambahkan " . $status['name'] . ". \r\n";
        }
    }
}
