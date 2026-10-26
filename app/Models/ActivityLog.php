<?php

namespace App\Models;

use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'route_name',
        'method',
        'description',
        'ip_address',
        'user_agent',
        'metadata',
        'old_values',
        'new_values',
        'target_type',
        'target_id',
        'target_label',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Filter log berdasarkan input query string halaman Log Activity.
     * Dipakai bersama oleh controller halaman (index) dan unduhan CSV,
     * sehingga keduanya selalu menampilkan hasil yang sama.
     *
     * Parameter request yang dikenali:
     * - tanggal_dari / tanggal_sampai: 'Y-m-d' dalam zona Asia/Jakarta
     * - pengguna: id user, atau 'sistem' untuk log tanpa user
     * - aksi: nilai kolom `action` (route name / method)
     * - q: pencarian bebas pada description & target_label
     */
    public function scopeFilterByRequest(Builder $query, Request $request): Builder
    {
        $dari = trim((string) $request->query('tanggal_dari'));
        $sampai = trim((string) $request->query('tanggal_sampai'));
        $pengguna = trim((string) $request->query('pengguna'));
        $aksi = trim((string) $request->query('aksi'));
        $q = trim((string) $request->query('q'));

        // Tanggal filter memakai perbatasan hari zona Asia/Jakarta,
        // sementara created_at disimpan dalam zona aplikasi (UTC).
        if ($dari !== '' && strtotime($dari) !== false) {
            $mulai = (new DateTimeImmutable($dari.'T00:00:00', new DateTimeZone('Asia/Jakarta')))
                ->setTimezone(new DateTimeZone((string) config('app.timezone')))
                ->format('Y-m-d H:i:s');
            $query->where('created_at', '>=', $mulai);
        }

        if ($sampai !== '' && strtotime($sampai) !== false) {
            $selesai = (new DateTimeImmutable($sampai.'T23:59:59.999999', new DateTimeZone('Asia/Jakarta')))
                ->setTimezone(new DateTimeZone((string) config('app.timezone')))
                ->format('Y-m-d H:i:s');
            $query->where('created_at', '<=', $selesai);
        }

        if ($pengguna === 'sistem') {
            $query->whereNull('user_id');
        } elseif ($pengguna !== '' && ctype_digit($pengguna)) {
            $query->where('user_id', $pengguna);
        }

        if ($aksi !== '') {
            $query->where('action', $aksi);
        }

        if ($q !== '') {
            $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q).'%';
            $query->where(function (Builder $sub) use ($like): void {
                $sub->where('description', 'like', $like)
                    ->orWhere('target_label', 'like', $like);
            });
        }

        return $query;
    }
}
