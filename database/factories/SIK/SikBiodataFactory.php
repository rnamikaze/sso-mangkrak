<?php

namespace Database\Factories\SIK;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

function zero_adder($number)
{
    // Convert the number to a string
    $number_str = (string) $number;

    // Calculate the number of zeros needed
    $zeros_needed = 16 - strlen($number_str);

    // If the number already has 16 or more digits, return it as it is
    if ($zeros_needed <= 0) {
        return $number_str;
    }

    // Otherwise, prepend the necessary number of zeros
    $zero_padding = str_repeat('0', $zeros_needed);
    return $zero_padding . $number_str;
}

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class SikBiodataFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = ['tendik', 'dosen fakultas', 'dosen prodi'];

        return [
            'nik' => zero_adder(1),
            'fullname' => fake()->name,
            'kelamin' => 1,
            'tinggi_badan' => rand(120, 200),
            'berat_badan' => rand(30, 120),
            'tempat_lahir' => fake()->address,
            'tanggal_lahir' => Carbon::createFromFormat('d-m-Y', '24-11-2004')->format('Y-m-d'),
            'alamat_rumah' => fake()->address,
            'telepon' => '089000111222',
            'email' => fake()->email,
            'pendidikan_terakhir' => 'S1',
            'no_bpjs_kes' => '111222333',
            'no_bpjs_kerja' => '222333444',
            'alamat' => fake()->address,
            'kerabat_nama' => fake()->name,
            'kerabat_hubungan' => 'saudara',
            'kerabat_telepon' => fake()->phoneNumber,
            'status' => $status[rand(0, 2)],
            'unit_id' => rand(0, 16),
            'jabatan_struktural_id' => rand(0, 12),
            'jabatan_fungsional_id' => rand(0, 3),
            'status_serdos' => 0,
            'active' => 1
        ];
    }
}
