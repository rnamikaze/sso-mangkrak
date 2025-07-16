<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class SikJabatanStrukturalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
            'jabatan_level' => 1,
            'code' => Str::random(15),
            'name' => fake()->unique()->name(),
            'unit_id' => 1, // id from sik_unit_kerja table
            'active' => 1
        ];
    }
}
