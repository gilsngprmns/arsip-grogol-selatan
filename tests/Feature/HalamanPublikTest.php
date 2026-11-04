<?php

namespace Tests\Feature;

use App\Models\AlurKerja;
use App\Models\BerandaSlide;
use App\Models\Faq;
use App\Models\Manfaat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HalamanPublikTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    }

    /* ===== Halaman publik muat dari DB ===== */

    public function test_home_page_shows_slides_from_database(): void
    {
        BerandaSlide::create(['path' => '/images/1.jpg', 'sort' => 0]);
        BerandaSlide::create(['path' => '/images/2.jpg', 'sort' => 1]);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Welcome')
            ->where('berandaSlides.0.path', '/images/1.jpg'));
    }

    public function test_fitur_page_shows_manfaat_from_database(): void
    {
        Manfaat::query()->delete();
        Manfaat::create(['judul' => 'Uji 1', 'deskripsi' => 'Desc 1', 'ikon' => 'folder', 'sort' => 0]);
        Manfaat::create(['judul' => 'Uji 2', 'deskripsi' => 'Desc 2', 'ikon' => 'pin', 'sort' => 1]);

        $response = $this->get('/fitur');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Public/Fitur')
            ->where('manfaat.0.judul', 'Uji 1'));
    }

    public function test_alur_kerja_page_shows_steps_from_database(): void
    {
        AlurKerja::query()->delete();
        AlurKerja::create(['nomor' => '1', 'judul' => 'Uji', 'deskripsi' => 'Desc', 'sort' => 0]);

        $response = $this->get('/alur-kerja');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Public/AlurKerja')
            ->where('alurKerja.0.judul', 'Uji'));
    }

    public function test_bantuan_page_shows_faq_from_database(): void
    {
        Faq::query()->delete();
        Faq::create(['pertanyaan' => 'Q?', 'jawaban' => 'A.', 'sort' => 0]);

        $response = $this->get('/bantuan');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Public/Bantuan')
            ->where('faq.0.pertanyaan', 'Q?'));
    }

    /* ===== Guard admin ===== */

    public function test_halaman_publik_requires_admin(): void
    {
        $staf = User::factory()->create(['role' => 'staf', 'is_active' => true]);
        $this->actingAs($staf)->get('/halaman-publik/beranda')->assertStatus(403);
    }

    public function test_halaman_publik_redirects_guests_to_login(): void
    {
        $this->get('/halaman-publik/beranda')->assertRedirect(route('login'));
    }

    public function test_sub_page_beranda_renders(): void
    {
        BerandaSlide::create(['path' => '/images/1.jpg', 'sort' => 0]);
        $this->actingAs($this->admin)->get('/halaman-publik/beranda')
            ->assertStatus(200)
            ->assertInertia(fn ($page) => $page->component('HalamanPublik/Beranda'));
    }

    public function test_sub_page_manfaat_renders(): void
    {
        Manfaat::create(['judul' => 'X', 'deskripsi' => 'Y', 'ikon' => 'folder', 'sort' => 0]);
        $this->actingAs($this->admin)->get('/halaman-publik/manfaat')
            ->assertStatus(200)
            ->assertInertia(fn ($page) => $page->component('HalamanPublik/Manfaat'));
    }

    public function test_sub_page_alur_kerja_renders(): void
    {
        AlurKerja::create(['nomor' => '1', 'judul' => 'X', 'deskripsi' => 'D', 'sort' => 0]);
        $this->actingAs($this->admin)->get('/halaman-publik/alur-kerja')
            ->assertStatus(200)
            ->assertInertia(fn ($page) => $page->component('HalamanPublik/AlurKerja'));
    }

    public function test_sub_page_faq_renders(): void
    {
        Faq::create(['pertanyaan' => 'Q?', 'jawaban' => 'A.', 'sort' => 0]);
        $this->actingAs($this->admin)->get('/halaman-publik/faq')
            ->assertStatus(200)
            ->assertInertia(fn ($page) => $page->component('HalamanPublik/Faq'));
    }

    /* ===== CRUD Manfaat ===== */

    public function test_admin_can_create_manfaat(): void
    {
        $this->actingAs($this->admin)->post('/halaman-publik/manfaat', [
            'judul' => 'Kartu Baru',
            'deskripsi' => 'Deskripsi baru',
            'ikon' => 'shield',
        ])->assertRedirect(route('halaman-publik.manfaat'));

        $this->assertDatabaseHas('manfaat', ['judul' => 'Kartu Baru', 'ikon' => 'shield']);
    }

    public function test_admin_can_update_manfaat(): void
    {
        $item = Manfaat::create(['judul' => 'Lama', 'deskripsi' => 'D', 'ikon' => 'folder', 'sort' => 0]);
        $this->actingAs($this->admin)->put("/halaman-publik/manfaat/{$item->id}", [
            'judul' => 'Baru',
            'deskripsi' => 'D2',
            'ikon' => 'qr',
        ])->assertRedirect(route('halaman-publik.manfaat'));

        $this->assertDatabaseHas('manfaat', ['id' => $item->id, 'judul' => 'Baru', 'ikon' => 'qr']);
    }

    public function test_admin_can_delete_manfaat(): void
    {
        $item = Manfaat::create(['judul' => 'Hapus', 'deskripsi' => 'D', 'ikon' => 'folder', 'sort' => 0]);
        $this->actingAs($this->admin)->delete("/halaman-publik/manfaat/{$item->id}")
            ->assertRedirect(route('halaman-publik.manfaat'))
            ->assertSessionHas('danger');

        $this->assertDatabaseMissing('manfaat', ['id' => $item->id]);
    }

    /* ===== CRUD AlurKerja ===== */

    public function test_admin_can_create_alur_kerja(): void
    {
        $this->actingAs($this->admin)->post('/halaman-publik/alur-kerja', [
            'nomor' => '9',
            'judul' => 'Langkah 9',
            'deskripsi' => 'Desc',
            'hasil' => 'Hasilnya',
        ])->assertRedirect(route('halaman-publik.alur-kerja'));

        $this->assertDatabaseHas('alur_kerja', ['nomor' => '9', 'judul' => 'Langkah 9']);
    }

    public function test_admin_can_delete_alur_kerja(): void
    {
        $item = AlurKerja::create(['nomor' => '1', 'judul' => 'X', 'deskripsi' => 'D', 'sort' => 0]);
        $this->actingAs($this->admin)->delete("/halaman-publik/alur-kerja/{$item->id}")
            ->assertRedirect(route('halaman-publik.alur-kerja'))
            ->assertSessionHas('danger');

        $this->assertDatabaseMissing('alur_kerja', ['id' => $item->id]);
    }

    /* ===== CRUD Faq ===== */

    public function test_admin_can_create_faq(): void
    {
        $this->actingAs($this->admin)->post('/halaman-publik/faq', [
            'pertanyaan' => 'Q uji?',
            'jawaban' => 'A uji.',
        ])->assertRedirect(route('halaman-publik.faq'));

        $this->assertDatabaseHas('faq', ['pertanyaan' => 'Q uji?']);
    }

    public function test_admin_can_update_faq(): void
    {
        $item = Faq::create(['pertanyaan' => 'Lama', 'jawaban' => 'A', 'sort' => 0]);
        $this->actingAs($this->admin)->put("/halaman-publik/faq/{$item->id}", [
            'pertanyaan' => 'Baru',
            'jawaban' => 'B',
        ])->assertRedirect(route('halaman-publik.faq'));

        $this->assertDatabaseHas('faq', ['id' => $item->id, 'pertanyaan' => 'Baru', 'jawaban' => 'B']);
    }

    public function test_admin_can_delete_faq(): void
    {
        $item = Faq::create(['pertanyaan' => 'Del', 'jawaban' => 'A', 'sort' => 0]);
        $this->actingAs($this->admin)->delete("/halaman-publik/faq/{$item->id}")
            ->assertRedirect(route('halaman-publik.faq'))
            ->assertSessionHas('danger');

        $this->assertDatabaseMissing('faq', ['id' => $item->id]);
    }

    /* ===== Slide upload (mock storage) ===== */

    public function test_admin_can_store_slide(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        BerandaSlide::query()->delete();

        $this->actingAs($this->admin)->post('/halaman-publik/beranda-slides', [
            'foto' => \Illuminate\Http\UploadedFile::fake()->image('slide1.jpg', 800, 400),
        ])->assertRedirect(route('halaman-publik.beranda'));

        $this->assertDatabaseCount('beranda_slides', 1);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists(
            collect(\Illuminate\Support\Facades\Storage::disk('public')->allFiles())
                ->first(fn ($f) => str_starts_with($f, 'beranda/'))
        );
    }

    public function test_admin_can_destroy_slide_removes_file(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $path = 'beranda/test.jpg';
        \Illuminate\Support\Facades\Storage::disk('public')->put($path, 'fake');

        $slide = BerandaSlide::create(['path' => $path, 'sort' => 0]);
        $this->actingAs($this->admin)->delete("/halaman-publik/beranda-slides/{$slide->id}")
            ->assertRedirect(route('halaman-publik.beranda'))
            ->assertSessionHas('danger');

        $this->assertDatabaseMissing('beranda_slides', ['id' => $slide->id]);
    }
}
