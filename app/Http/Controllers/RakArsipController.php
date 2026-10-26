<?php

namespace App\Http\Controllers;

use App\Models\LokasiArsip;
use App\Models\RakArsip;
use App\Models\Seksi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class RakArsipController extends Controller
{
    public function index(): Response
    {
        $rak = RakArsip::query()
            ->with(['lokasiArsip', 'seksi:id,nama_seksi'])
            ->withCount('dokumenArsip')
            ->orderBy('kode_rak')
            ->get();

        return Inertia::render('Rak/Index', [
            'rak' => $rak,
            'lokasi' => LokasiArsip::query()
                ->withCount(['rakArsip', 'dokumenArsip'])
                ->orderBy('nama_lokasi')
                ->get(),
            'lokasiList' => LokasiArsip::query()
                ->where('is_active', true)
                ->orderBy('nama_lokasi')
                ->get(['id', 'kode_lokasi', 'nama_lokasi']),
            'seksiOptions' => Seksi::query()
                ->orderByOrganisasi()
                ->get(['id', 'nama_seksi']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'lokasi_id' => ['required', 'exists:lokasi_arsip,id'],
            'seksi_id' => ['required', 'exists:seksi,id'],
            'kode_rak' => ['required', 'string', 'max:50', 'unique:rak_arsip,kode_rak'],
            'nama_rak' => ['required', 'string', 'max:150'],
            'nomor_rak' => ['nullable', 'string', 'max:50'],
            'kapasitas' => ['nullable', 'integer', 'min:1'],
            'deskripsi' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        RakArsip::create($validated);

        return redirect()->route('rak.index')->with('success', 'Rak arsip berhasil ditambahkan.');
    }

    public function update(Request $request, RakArsip $rak): RedirectResponse
    {
        $validated = $request->validate([
            'lokasi_id' => ['required', 'exists:lokasi_arsip,id'],
            'seksi_id' => ['required', 'exists:seksi,id'],
            'kode_rak' => ['required', 'string', 'max:50', 'unique:rak_arsip,kode_rak,'.$rak->id],
            'nama_rak' => ['required', 'string', 'max:150'],
            'nomor_rak' => ['nullable', 'string', 'max:50'],
            'kapasitas' => ['nullable', 'integer', 'min:1'],
            'deskripsi' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $rak->update($validated);

        return redirect()->route('rak.index')->with('success', 'Rak arsip berhasil diperbarui.');
    }

    public function destroy(RakArsip $rak): RedirectResponse
    {
        if ($rak->dokumenArsip()->count() > 0) {
            return redirect()->route('rak.index')
                ->with('error', "Rak {$rak->kode_rak} masih berisi arsip. Pindahkan arsip terlebih dahulu.");
        }

        $rak->delete();

        return redirect()->route('rak.index')->with('danger', 'Rak arsip berhasil dihapus.');
    }
}
