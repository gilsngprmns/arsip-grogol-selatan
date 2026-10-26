<?php

use App\Http\Controllers\ArsipController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\LokasiArsipController;
use App\Http\Controllers\GlobalSearchController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RakArsipController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserManagementController;
use App\Models\DokumenArsip;
use App\Models\ActivityLog;
use App\Models\LokasiArsip;
use App\Models\RakArsip;
use App\Models\RiwayatSirkulasi;
use App\Models\Seksi;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
    ]);
})->name('home');

Route::get('/fitur', function () {
    return Inertia::render('Public/Fitur', [
        'canLogin' => Route::has('login'),
    ]);
})->name('fitur');

Route::get('/alur-kerja', function () {
    return Inertia::render('Public/AlurKerja', [
        'canLogin' => Route::has('login'),
    ]);
})->name('alur-kerja');

Route::get('/bantuan', function () {
    return Inertia::render('Public/Bantuan', [
        'canLogin' => Route::has('login'),
    ]);
})->name('bantuan');

Route::get('/dashboard', function (\Illuminate\Http\Request $request) {
    $user = $request->user();
    $isAdmin = $user->role === 'admin';
    $documentQuery = DokumenArsip::query();

    if (! $isAdmin) {
        $documentQuery->where('seksi_id', $user->seksi_id);
    }

    $stats = [
        'total_dokumen' => (clone $documentQuery)->count(),
        'dokumen_tersedia' => (clone $documentQuery)->where('status_arsip', 'tersedia')->count(),
        'dokumen_dipinjam' => (clone $documentQuery)->where('status_arsip', 'dipinjam')->count(),
        'dokumen_hilang' => (clone $documentQuery)->where('status_arsip', 'hilang')->count(),
        'total_user' => $isAdmin ? User::count() : User::where('seksi_id', $user->seksi_id)->count(),
        'total_lokasi' => LokasiArsip::count(),
        'total_rak' => RakArsip::count(),
        'per_seksi' => ($isAdmin ? Seksi::query() : Seksi::query()->whereKey($user->seksi_id))
            ->withCount(['dokumenArsip', 'users'])
            ->orderByOrganisasi()
            ->get()
            ->map(fn ($seksi) => [
                'id' => $seksi->id,
                'nama_seksi' => $seksi->nama_seksi,
                'jumlah_arsip' => $seksi->dokumen_arsip_count,
                'jumlah_user' => $seksi->users_count,
            ])
            ->toArray(),
        'scope' => $isAdmin ? 'Seluruh unit kerja' : ($user->seksi?->nama_seksi ?? 'Seksi belum ditentukan'),
    ];

    $trendGranularity = in_array($request->input('trend_granularity', 'month'), ['day', 'month', 'year'], true)
        ? $request->input('trend_granularity', 'month')
        : 'month';
    $trendDefaults = ['day' => 7, 'month' => 6, 'year' => 3];
    $trendMax = ['day' => 31, 'month' => 12, 'year' => 10];
    $trendPeriod = (int) $request->input('trend_period', 0);
    if ($trendPeriod < 1 || $trendPeriod > $trendMax[$trendGranularity]) {
        $trendPeriod = $trendDefaults[$trendGranularity];
    }

    $trendStart = match ($trendGranularity) {
        'day'  => now()->subDays($trendPeriod - 1)->startOfDay(),
        'year' => now()->subYears($trendPeriod - 1)->startOfYear(),
        default => now()->subMonths($trendPeriod - 1)->startOfMonth(),
    };
    $trendRows = RiwayatSirkulasi::query()
        ->whereIn('jenis_aksi', ['dipinjam', 'dikembalikan'])
        ->where('waktu_aksi', '>=', $trendStart)
        ->when(
            ! $isAdmin,
            fn ($query) => $query->whereHas(
                'dokumenArsip',
                fn ($document) => $document->where('seksi_id', $user->seksi_id),
            ),
        )
        ->get(['waktu_aksi', 'jenis_aksi']);

    $trendKeyFormat = ['day' => 'Y-m-d', 'month' => 'Y-m', 'year' => 'Y'][$trendGranularity];
    $trends = collect(range($trendPeriod - 1, 0, -1))->map(function (int $offset) use ($trendRows, $trendGranularity, $trendKeyFormat, $trendPeriod) {
        $unit = match ($trendGranularity) {
            'day'  => now()->subDays($offset),
            'year' => now()->subYears($offset),
            default => now()->subMonths($offset),
        };
        $key = $unit->format($trendKeyFormat);
        $rows = $trendRows->filter(fn ($row) => optional($row->waktu_aksi)->format($trendKeyFormat) === $key);
        $label = match ($trendGranularity) {
            'day'  => $unit->format('d M'),
            'year' => $unit->format('Y'),
            default => $unit->translatedFormat('M'),
        };

        return [
            'label' => $label,
            'pinjam' => $rows->where('jenis_aksi', 'dipinjam')->count(),
            'kembali' => $rows->where('jenis_aksi', 'dikembalikan')->count(),
        ];
    })->values();

    $notifications = [
        'tanpa_lokasi' => (clone $documentQuery)->whereNull('rak_id')->count(),
        'terlambat' => (clone $documentQuery)->where('status_arsip', 'dipinjam')->whereNotNull('batas_pengembalian')->where('batas_pengembalian', '<', now())->count(),
    ];

    if ($isAdmin) {
        $fullRacks = RakArsip::query()
            ->where('is_active', true)
            ->withCount('dokumenArsip')
            ->get()
            ->filter(fn ($rack) => ($rack->kapasitas ?? 200) > 0 && $rack->dokumen_arsip_count >= (($rack->kapasitas ?? 200) * 0.8))
            ->count();

        $notifications['rak_hampir_penuh'] = $fullRacks;
        $notifications['aktivitas_penting'] = ActivityLog::query()
            ->whereIn('route_name', ['users.store', 'users.update', 'users.destroy', 'users.force-delete', 'backup.import'])
            ->where('created_at', '>=', now()->startOfDay())
            ->count();
    }

    $adminOverview = null;
    if ($isAdmin) {
        $adminOverview = [
            'user_aktif' => User::where('is_active', true)->count(),
            'user_nonaktif' => User::where('is_active', false)->count(),
            'total_seksi' => Seksi::count(),
            'dokumen_tanpa_lokasi' => DokumenArsip::whereNull('rak_id')->count(),
            'aktivitas_hari_ini' => ActivityLog::where('created_at', '>=', now()->startOfDay())->count(),
            'rak_perhatian' => RakArsip::query()
                ->where('is_active', true)
                ->withCount('dokumenArsip')
                ->with('lokasiArsip:id,nama_lokasi')
                ->get()
                ->filter(function ($rack) { $c = $rack->kapasitas ?? 200; return $c > 0 && $rack->dokumen_arsip_count >= ($c * 0.8); })
                ->sortByDesc(function ($rack) { $c = $rack->kapasitas ?? 200; return $c > 0 ? $rack->dokumen_arsip_count / $c : 0; })
                ->map(function ($r) { $c = $r->kapasitas ?? 200; return tap($r, fn ($r) => $r->pct = round(($r->dokumen_arsip_count / $c) * 100)); })
                ->take(5)
                ->values(),
        ];
    }

    $recentDocuments = (clone $documentQuery)
        ->with(['seksi:id,nama_seksi', 'rak:id,kode_rak,nama_rak'])
        ->latest('created_at')
        ->limit(6)
        ->get(['id', 'kode_arsip', 'perihal', 'status_arsip', 'seksi_id', 'rak_id', 'created_at']);

    $recentActivities = ActivityLog::query()
        ->with('user:id,name,role')
        ->when(! $isAdmin, fn ($query) => $query->where('user_id', $user->id))
        ->latest()
        ->limit(6)
        ->get(['id', 'user_id', 'action', 'description', 'created_at']);

    $activeLoans = (clone $documentQuery)
        ->where('status_arsip', 'dipinjam')
        ->with([
            'seksi:id,nama_seksi',
            'riwayatSirkulasi' => fn ($query) => $query
                ->where('jenis_aksi', 'dipinjam')
                ->latest('waktu_aksi')
                ->limit(1),
        ])
        ->orderByRaw('CASE WHEN batas_pengembalian IS NULL THEN 1 ELSE 0 END')
        ->orderBy('batas_pengembalian')
        ->limit(8)
        ->get(['id', 'kode_arsip', 'perihal', 'status_arsip', 'seksi_id', 'batas_pengembalian'])
        ->map(function (DokumenArsip $document) {
            $deadline = $document->batas_pengembalian;
            $isOverdue = $deadline !== null && $deadline->isPast();
            $daysRemaining = null;

            if ($deadline !== null) {
                $today = now()->startOfDay();
                $due = $deadline->copy()->startOfDay();
                $daysRemaining = (int) $today->diffInDays($due, false);
            }

            return [
                'id' => $document->id,
                'kode_arsip' => $document->kode_arsip,
                'perihal' => $document->perihal,
                'status_arsip' => $document->status_arsip,
                'seksi' => $document->seksi,
                'batas_pengembalian' => $deadline?->toIso8601String(),
                'peminjam' => $document->riwayatSirkulasi->first()?->pihak_terkait,
                'terlambat' => $isOverdue,
                'sisa_hari' => $daysRemaining,
            ];
        })
        ->values();

    return Inertia::render('Dashboard', [
        'stats' => $stats,
        'recentDocuments' => $recentDocuments,
        'recentActivities' => $recentActivities,
        'trends' => $trends,
        'trendGranularity' => $trendGranularity,
        'trendPeriod' => $trendPeriod,
        'notifications' => $notifications,
        'adminOverview' => $adminOverview,
        'activeLoans' => $activeLoans,
        'dataHealth' => [
            'tanpa_lokasi' => (clone $documentQuery)->whereNull('rak_id')->count(),
            'tanpa_file' => (clone $documentQuery)->where(function ($query): void {
                $query->whereNull('file_path')->orWhere('file_path', '');
            })->count(),
            'metadata_kosong' => (clone $documentQuery)->where(function ($query): void {
                $query->whereNull('nomor_surat')
                    ->orWhere('nomor_surat', '')
                    ->orWhereNull('tanggal_surat')
                    ->orWhereNull('seksi_id')
                    ->orWhereNull('perihal')
                    ->orWhere('perihal', '');
            })->count(),
            'tanpa_qr' => (clone $documentQuery)->where(function ($query): void {
                $query->whereNull('qr_code_path')->orWhere('qr_code_path', '');
            })->count(),
        ],
    ]);
})->middleware(['auth', 'active', 'verified'])->name('dashboard');

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/search', GlobalSearchController::class)->name('search');
    Route::get('/scan', fn () => Inertia::render('Scan/Index'))->name('scan.index');
    Route::get('arsip/export', [ArsipController::class, 'exportCsv'])->name('arsip.export');
    Route::post('arsip/{arsip}/pinjam', [ArsipController::class, 'pinjam'])->name('arsip.pinjam');
    Route::post('arsip/{arsip}/kembalikan', [ArsipController::class, 'kembalikan'])->name('arsip.kembalikan');
    Route::post('arsip/{arsip}/settersedia', [ArsipController::class, 'settersedia'])->name('arsip.settersedia');
    Route::post('arsip/{arsip}/tandahilang', [ArsipController::class, 'tandahilang'])->name('arsip.tandahilang');
    Route::resource('arsip', ArsipController::class);
    Route::post('/api/arsip/scan', [ArsipController::class, 'scanByKode'])
        ->name('arsip.scan');

    Route::middleware('admin')->group(function () {
        Route::post('arsip/{arsip}/force-delete', [ArsipController::class, 'forceDelete'])
            ->name('arsip.force-delete');
        Route::get('/backup', [BackupController::class, 'index'])->name('backup.index');
        Route::get('/backup/export', [BackupController::class, 'export'])->name('backup.export');
        Route::post('/backup/import', [BackupController::class, 'import'])->name('backup.import');
        Route::post('/backup/run', [BackupController::class, 'run'])->name('backup.run');
        Route::get('/backup/files/{file}', [BackupController::class, 'files'])->where(
            'file', 'backup-arsip-[0-9]{8}-[0-9]{6}\.(json|zip)'
        )->name('backup.files');

        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
        Route::post('/users/{user}/activate', [UserManagementController::class, 'activate'])->name('users.activate');
        Route::post('/users/{user}/password', [UserManagementController::class, 'resetPassword'])->name('users.password');
        Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/{user}/force-delete', [UserManagementController::class, 'forceDelete'])->name('users.force-delete');
        Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('activity.index');
        Route::get('/reports/activity.csv', [ReportController::class, 'activity'])->name('reports.activity');
        Route::get('/reports/users.csv', [ReportController::class, 'users'])->name('reports.users');
        Route::get('/reports/racks.csv', [ReportController::class, 'racks'])->name('reports.racks');
        Route::get('/reports/sections.csv', [ReportController::class, 'sections'])->name('reports.sections');

        Route::get('/lokasi', fn () => redirect()->route('rak.index'))->name('lokasi.redirect');
        Route::post('/lokasi', [LokasiArsipController::class, 'store'])->name('lokasi.store');
        Route::put('/lokasi/{lokasi}', [LokasiArsipController::class, 'update'])->name('lokasi.update');
        Route::delete('/lokasi/{lokasi}', [LokasiArsipController::class, 'destroy'])->name('lokasi.destroy');

        Route::get('/rak', [RakArsipController::class, 'index'])->name('rak.index');
        Route::post('/rak', [RakArsipController::class, 'store'])->name('rak.store');
        Route::put('/rak/{rak}', [RakArsipController::class, 'update'])->name('rak.update');
        Route::delete('/rak/{rak}', [RakArsipController::class, 'destroy'])->name('rak.destroy');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
