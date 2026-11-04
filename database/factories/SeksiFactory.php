<?php

namespace Database\Factories;

use App\Models\Seksi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Seksi>
 */
class SeksiFactory extends Factory
{
    protected $model = Seksi::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_seksi' => fake()->word(),
        ];
    }
}
