<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BerandaSlide extends Model
{
    protected $table = 'beranda_slides';

    protected $fillable = [
        'path',
        'sort',
    ];

    protected function casts(): array
    {
        return [
            'sort' => 'integer',
        ];
    }
}
