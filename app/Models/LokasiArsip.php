<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class LokasiArsip extends Model
{
    protected $table = 'lokasi_arsip';

    protected $fillable = [
        'kode_lokasi',
        'nama_lokasi',
        'gedung',
        'lantai',
        'ruangan',
        'deskripsi',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function rakArsip(): HasMany
    {
        return $this->hasMany(RakArsip::class, 'lokasi_id');
    }

    public function dokumenArsip(): HasManyThrough
    {
        return $this->hasManyThrough(
            DokumenArsip::class,
            RakArsip::class,
            'lokasi_id',
            'rak_id',
            'id'
        );
    }
}
