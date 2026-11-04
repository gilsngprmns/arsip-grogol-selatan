<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Manfaat extends Model
{
    protected $table = 'manfaat';

    protected $fillable = [
        'judul',
        'deskripsi',
        'ikon',
        'sort',
    ];

    protected function casts(): array
    {
        return [
            'sort' => 'integer',
        ];
    }
}
