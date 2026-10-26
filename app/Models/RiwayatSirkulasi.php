<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatSirkulasi extends Model
{
    protected $table = 'riwayat_sirkulasi';

    protected $fillable = [
        'dokumen_arsip_id',
        'user_id',
        'jenis_aksi',
        'pihak_terkait',
        'keterangan',
        'waktu_aksi',
    ];

    protected function casts(): array
    {
        return [
            'waktu_aksi' => 'datetime',
        ];
    }

    public function dokumenArsip(): BelongsTo
    {
        return $this->belongsTo(DokumenArsip::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
