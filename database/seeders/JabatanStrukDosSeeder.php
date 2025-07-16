<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use App\Models\SIK\SikJabatanStrukDos;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class JabatanStrukDosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $JabatanStrukturalNameArray = [
            "Dekan",
            "Wakil Dekan",
            "Ka. Prodi",
            "Sek. Prodi",
            "Dosen",
            "TU Fakultas"
        ];

        $JabatanStrukturalIdArray = [
            0,
            1,
            2,
            3,
            4,
            5
        ];

        for ($i = 0; $i < sizeof($JabatanStrukturalNameArray); $i++) {
            SikJabatanStrukDos::create([
                'code' => Str::random(10),
                'level_jsd' => $JabatanStrukturalIdArray[$i],
                'name' => $JabatanStrukturalNameArray[$i],
                'active' => 1,
                'fakultas_fr_id' => 1
            ]);
        }
    }
}
