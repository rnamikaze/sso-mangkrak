<?php

namespace Database\Seeders;

use App\Models\SikStaffFakultasModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SikStaffFakultasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $staffFakultasArr = [
            [
                'name' => "TU Fakultas"
            ],
            [
                'name' => "Laboran"
            ],
        ];

        foreach ($staffFakultasArr as $staff) {
            SikStaffFakultasModel::create([
                'name' => $staff['name']
            ]);

            echo 'Sukes Staff Fakultas -> ' . $staff['name'];
        }
    }
}
