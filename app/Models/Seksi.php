<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Seksi extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $table = 'seksi';

    protected $fillable = [
        'nama_seksi',
        'keterangan',
    ];

    /**
     * Urutan struktural kelurahan: Pemerintahan → Ekonomi → Kesejahteraan.
     */
    public function scopeOrderByOrganisasi($query)
    {
        return $query
            ->orderByRaw("CASE
                WHEN nama_seksi LIKE '%Pemerintahan%' THEN 1
                WHEN nama_seksi LIKE '%Ekonomi%' THEN 2
                WHEN nama_seksi LIKE '%Kesejahteraan%' THEN 3
                ELSE 99
            END")
            ->orderBy('nama_seksi');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function dokumenArsip(): HasMany
    {
        return $this->hasMany(DokumenArsip::class);
    }

    public function rakArsip(): HasMany
    {
        return $this->hasMany(RakArsip::class);
    }
}
