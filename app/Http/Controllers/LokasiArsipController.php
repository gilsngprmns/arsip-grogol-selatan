<?php

namespace App\Http\Controllers;

use App\Models\LokasiArsip;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LokasiArsipController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode_lokasi' => ['required', 'string', 'max:50', 'unique:lokasi_arsip,kode_lokasi'],
            'nama_lokasi' => ['required', 'string', 'max:150'],
            'gedung' => ['nullable', 'string', 'max:150'],
            'lantai' => ['nullable', 'string', 'max:50'],
            'ruangan' => ['nullable', 'string', 'max:100'],
            'deskripsi' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        LokasiArsip::create($validated);

        return redirect()->route('rak.index')->with('success', 'Lokasi arsip berhasil ditambahkan.');
    }

    public function update(Request $request, LokasiArsip $lokasi): RedirectResponse
    {
        $validated = $request->validate([
            'kode_lokasi' => ['required', 'string', 'max:50', 'unique:lokasi_arsip,kode_lokasi,'.$lokasi->id],
            'nama_lokasi' => ['required', 'string', 'max:150'],
            'gedung' => ['nullable', 'string', 'max:150'],
            'lantai' => ['nullable', 'string', 'max:50'],
            'ruangan' => ['nullable', 'string', 'max:100'],
            'deskripsi' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $lokasi->update($validated);

        return redirect()->route('rak.index')->with('success', 'Lokasi arsip berhasil diperbarui.');
    }

    public function destroy(LokasiArsip $lokasi): RedirectResponse
    {
        $rakCount = $lokasi->rakArsip()->count();
        $docCount = $lokasi->dokumenArsip()->count();

        if ($rakCount > 0) {
            return redirect()->route('rak.index')
                ->with('error', "Lokasi {$lokasi->nama_lokasi} masih berisi {$rakCount} rak arsip ({$docCount} arsip). Kosongkan rak terlebih dahulu.");
        }

        $lokasi->delete();

        return redirect()->route('rak.index')->with('danger', 'Lokasi arsip berhasil dihapus.');
    }
}
