<?php

namespace Database\Factories;

use App\Models\DokumenArsip;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DokumenArsip>
 */
class DokumenArsipFactory extends Factory
{
    protected $model = DokumenArsip::class;

    /**
     * Kolom wajib (NOT NULL) disediakan di sini. Kolom FK `seksi_id` dan
     * `dicatat_oleh` sengaja tidak di-default — pemakai factory wajib menyetelnya
     * agar merujuk entitas yang benar-benar ada di basis data uji.
     */
    public function definition(): array
    {
        return [
            'kode_arsip' => strtoupper(fake()->uuid()),
            'nomor_surat' => fake()->numerify('###/KS/'.fake()->year()),
            'perihal' => fake()->sentence(4),
            'jenis_surat' => fake()->randomElement(['masuk', 'keluar']),
            'sifat_surat' => 'biasa',
            'tanggal_surat' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'status_arsip' => 'tersedia',
        ];
    }
}
