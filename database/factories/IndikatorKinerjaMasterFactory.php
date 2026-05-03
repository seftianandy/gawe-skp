<?php

namespace Database\Factories;

use App\Models\IndikatorKinerjaMaster;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IndikatorKinerjaMaster>
 */
class IndikatorKinerjaMasterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'nama_indikator' => fake()->sentence(4),
            'satuan' => fake()->word(),
            'target' => (string) fake()->numberBetween(1, 10),
            'kategori' => fake()->randomElement(['kualitas', 'kuantitas']),
        ];
    }
}
