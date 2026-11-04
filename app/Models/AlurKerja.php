<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlurKerja extends Model
{
    protected $table = 'alur_kerja';

    protected $fillable = [
        'nomor',
        'judul',
        'deskripsi',
        'hasil',
        'sort',
    ];

    protected function casts(): array
    {
        return [
            'sort' => 'integer',
        ];
    }
}
