<?php

namespace App\Http\Controllers;

use App\Models\AlurKerja;
use App\Models\BerandaSlide;
use App\Models\Faq;
use App\Models\Manfaat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class HalamanPublikController extends Controller
{
    public const IkonManfaat = ['folder', 'pin', 'circulation', 'shield', 'qr', 'history'];

    /* ============ Beranda — foto carousel ============ */

    public function beranda(): Response
    {
        $berandaSlides = BerandaSlide::query()->orderBy('sort')->get(['id', 'path', 'sort']);

        return Inertia::render('HalamanPublik/Beranda', [
            'berandaSlides' => $berandaSlides,
        ]);
    }

    public function storeSlide(Request $request): RedirectResponse
    {
        $request->validate([
            'foto' => ['required', 'image', 'max:5120'],
        ]);

        $slide = BerandaSlide::create([
            'path' => $request->file('foto')->store('beranda', 'public'),
            'sort' => (int) BerandaSlide::query()->max('sort') + 1,
        ]);

        return $this->redirectSub('halaman-publik.beranda', 'Slide foto berhasil ditambahkan.');
    }

    public function destroySlide(BerandaSlide $slide): RedirectResponse
    {
        $this->hapusFileSlide($slide->path);
        $slide->delete();

        return $this->redirectSub('halaman-publik.beranda', 'Slide foto berhasil dihapus.', 'danger');
    }

    /* ============ Manfaat ============ */

    public function manfaat(): Response
    {
        return Inertia::render('HalamanPublik/Manfaat', [
            'manfaat' => Manfaat::query()->orderBy('sort')->get(),
            'ikonManfaat' => self::IkonManfaat,
        ]);
    }

    public function storeManfaat(Request $request): RedirectResponse
    {
        $row = $this->validasiManfaat($request, null);

        Manfaat::create($row + ['sort' => (int) Manfaat::query()->max('sort') + 1]);

        return $this->redirectSub('halaman-publik.manfaat', 'Manfaat berhasil ditambahkan.');
    }

    public function updateManfaat(Request $request, Manfaat $manfaat): RedirectResponse
    {
        $row = $this->validasiManfaat($request, $manfaat->id);

        $manfaat->update($row);

        return $this->redirectSub('halaman-publik.manfaat', 'Manfaat berhasil diperbarui.');
    }

    public function destroyManfaat(Manfaat $manfaat): RedirectResponse
    {
        $manfaat->delete();

        return $this->redirectSub('halaman-publik.manfaat', 'Manfaat berhasil dihapus.', 'danger');
    }

    /* ============ Prosedur (alur kerja) ============ */

    public function alurKerja(): Response
    {
        return Inertia::render('HalamanPublik/AlurKerja', [
            'alurKerja' => AlurKerja::query()->orderBy('sort')->get(),
        ]);
    }

    public function storeAlurKerja(Request $request): RedirectResponse
    {
        $row = $this->validasiAlurKerja($request);

        AlurKerja::create($row + ['sort' => (int) AlurKerja::query()->max('sort') + 1]);

        return $this->redirectSub('halaman-publik.alur-kerja', 'Langkah prosedur berhasil ditambahkan.');
    }

    public function updateAlurKerja(Request $request, AlurKerja $alurKerja): RedirectResponse
    {
        $alurKerja->update($this->validasiAlurKerja($request));

        return $this->redirectSub('halaman-publik.alur-kerja', 'Langkah prosedur berhasil diperbarui.');
    }

    public function destroyAlurKerja(AlurKerja $alurKerja): RedirectResponse
    {
        $alurKerja->delete();

        return $this->redirectSub('halaman-publik.alur-kerja', 'Langkah prosedur berhasil dihapus.', 'danger');
    }

    /* ============ Bantuan (FAQ) ============ */

    public function faq(): Response
    {
        return Inertia::render('HalamanPublik/Faq', [
            'faq' => Faq::query()->orderBy('sort')->get(),
        ]);
    }

    public function storeFaq(Request $request): RedirectResponse
    {
        $row = $this->validasiFaq($request);

        Faq::create($row + ['sort' => (int) Faq::query()->max('sort') + 1]);

        return $this->redirectSub('halaman-publik.faq', 'FAQ berhasil ditambahkan.');
    }

    public function updateFaq(Request $request, Faq $faq): RedirectResponse
    {
        $faq->update($this->validasiFaq($request));

        return $this->redirectSub('halaman-publik.faq', 'FAQ berhasil diperbarui.');
    }

    public function destroyFaq(Faq $faq): RedirectResponse
    {
        $faq->delete();

        return $this->redirectSub('halaman-publik.faq', 'FAQ berhasil dihapus.', 'danger');
    }

    /* ============ Helper ============ */

    private function validasiManfaat(Request $request, ?int $abaikanId): array
    {
        return $request->validate([
            'judul' => ['required', 'string', 'max:150'],
            'deskripsi' => ['required', 'string', 'max:1000'],
            'ikon' => ['required', Rule::in(self::IkonManfaat)],
        ]);
    }

    private function validasiAlurKerja(Request $request): array
    {
        return $request->validate([
            'nomor' => ['required', 'string', 'max:20'],
            'judul' => ['required', 'string', 'max:150'],
            'deskripsi' => ['required', 'string', 'max:1000'],
            'hasil' => ['nullable', 'string', 'max:200'],
        ]);
    }

    private function validasiFaq(Request $request): array
    {
        return $request->validate([
            'pertanyaan' => ['required', 'string', 'max:255'],
            'jawaban' => ['required', 'string', 'max:2000'],
        ]);
    }

    /**
     * Hapus file fisik slide hanya jika ia berasal dari upload admin
     * (path disk `public`, diawali `/storage/`). File statis situs
     * (mis. seed awal `/images/N.jpg`) DIPERTAHANKAN.
     */
    private function hapusFileSlide(?string $path): void
    {
        if ($path && str_starts_with($path, '/storage/')) {
            $relatif = ltrim($path, '/storage/');
            if (Storage::disk('public')->exists($relatif)) {
                Storage::disk('public')->delete($relatif);
            }
        }
    }

    private function redirectSub(string $routeName, string $pesan, string $kunci = 'success'): RedirectResponse
    {
        return redirect()->route($routeName)->with($kunci, $pesan);
    }
}
