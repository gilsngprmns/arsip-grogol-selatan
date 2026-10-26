<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = [
            'tanggal_dari' => trim((string) $request->query('tanggal_dari', '')),
            'tanggal_sampai' => trim((string) $request->query('tanggal_sampai', '')),
            'pengguna' => trim((string) $request->query('pengguna', '')),
            'aksi' => trim((string) $request->query('aksi', '')),
            'q' => trim((string) $request->query('q', '')),
        ];

        return Inertia::render('Activity/Index', [
            'activities' => ActivityLog::query()
                ->with('user:id,name,role')
                ->whereNotIn('route_name', [
                    'login',
                    'logout',
                    'password.email',
                    'password.store',
                    'password.confirm',
                    'verification.send',
                ])
                ->filterByRequest($request)
                ->latest()
                ->paginate(25)
                ->withQueryString(),
            'filters' => $filters,
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
            'actions' => ActivityLog::query()
                ->select('action')
                ->whereNotNull('action')
                ->where('action', '!=', '')
                ->distinct()
                ->orderBy('action')
                ->pluck('action'),
        ]);
    }
}
