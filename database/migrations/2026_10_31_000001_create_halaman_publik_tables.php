<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('beranda_slides', function (Blueprint $table) {
            $table->id();
            $table->string('path');
            $table->integer('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('manfaat', function (Blueprint $table) {
            $table->id();
            $table->string('judul', 150);
            $table->text('deskripsi');
            $table->string('ikon', 40)->default('folder');
            $table->integer('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('alur_kerja', function (Blueprint $table) {
            $table->id();
            $table->string('nomor', 20);
            $table->string('judul', 150);
            $table->text('deskripsi');
            $table->string('hasil', 200)->nullable();
            $table->integer('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('faq', function (Blueprint $table) {
            $table->id();
            $table->string('pertanyaan', 255);
            $table->text('jawaban');
            $table->integer('sort')->default(0);
            $table->timestamps();
        });

        if (DB::table('manfaat')->count() === 0) {
            $now = now();

            DB::table('beranda_slides')->insert([
                ['path' => '/images/1.jpg', 'sort' => 0, 'created_at' => $now, 'updated_at' => $now],
                ['path' => '/images/2.jpg', 'sort' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['path' => '/images/3.jpg', 'sort' => 2, 'created_at' => $now, 'updated_at' => $now],
                ['path' => '/images/4.jpg', 'sort' => 3, 'created_at' => $now, 'updated_at' => $now],
                ['path' => '/images/5.jpg', 'sort' => 4, 'created_at' => $now, 'updated_at' => $now],
            ]);

            DB::table('manfaat')->insert([
                ['judul' => 'Dokumen lebih mudah ditemukan', 'deskripsi' => 'Nomor surat, perihal, dan klasifikasi tercatat rapi sehingga petugas tidak perlu mencari berkas secara manual berulang kali.', 'ikon' => 'folder', 'sort' => 0, 'created_at' => $now, 'updated_at' => $now],
                ['judul' => 'Lokasi penyimpanan jelas', 'deskripsi' => 'Setiap dokumen dihubungkan ke ruang, rak, dan boks fisik. Saat dibutuhkan, petugas tahu tepat di mana berkas disimpan.', 'ikon' => 'pin', 'sort' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['judul' => 'Peminjaman tetap terkendali', 'deskripsi' => 'Siapa yang meminjam, kapan harus dikembalikan, dan status dokumen dapat dipantau tanpa catatan lepas.', 'ikon' => 'circulation', 'sort' => 2, 'created_at' => $now, 'updated_at' => $now],
                ['judul' => 'Digitalisasi dan Proteksi Berkas', 'deskripsi' => 'Salinan pindaian dokumen (PDF / foto) terlampir pada arsip, sehingga fisik yang lapuk atau hilang tidak menghilangkan isi dokumen.', 'ikon' => 'shield', 'sort' => 3, 'created_at' => $now, 'updated_at' => $now],
                ['judul' => 'Pelabelan QR Code Otomatis', 'deskripsi' => 'Setiap dokumen langsung mendapat stiker label QR yang siap dicetak, sehingga identifikasi dan verifikasi berkas di rak lebih cepat.', 'ikon' => 'qr', 'sort' => 4, 'created_at' => $now, 'updated_at' => $now],
                ['judul' => 'Transparansi dan Jejak Audit', 'deskripsi' => 'Riwayat peminjaman dan perubahan data tercatat rinci, menjaga akuntabilitas internal tata usaha tetap dapat ditelusuri.', 'ikon' => 'history', 'sort' => 5, 'created_at' => $now, 'updated_at' => $now],
            ]);

            DB::table('alur_kerja')->insert([
                ['nomor' => '1', 'judul' => 'Catat dokumen', 'deskripsi' => 'Isi data surat (nomor, tanggal, perihal) dan unggah berkas pindaian bila tersedia.', 'hasil' => 'Dokumen tercatat di sistem.', 'sort' => 0, 'created_at' => $now, 'updated_at' => $now],
                ['nomor' => '2', 'judul' => 'Tetapkan lokasi penyimpanan', 'deskripsi' => 'Pilih ruang, rak, dan boks sesuai tempat fisik berkas, lalu simpan penempatan tersebut.', 'hasil' => 'Lokasi dokumen diketahui saat dibutuhkan.', 'sort' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['nomor' => '3', 'judul' => 'Kelola peminjaman dan penelusuran', 'deskripsi' => 'Cari dokumen lewat pencarian, catat peminjaman bila dipinjam, dan kembalikan setelah selesai dipakai.', 'hasil' => 'Status dan riwayat dokumen tetap terpantau.', 'sort' => 2, 'created_at' => $now, 'updated_at' => $now],
            ]);

            DB::table('faq')->insert([
                ['pertanyaan' => 'Bagaimana cara mendapatkan akun?', 'jawaban' => 'Ajukan permintaan kepada admin pada jam pelayanan dengan identitas petugas dan unit kerja. Akun akan dibuat oleh admin setelah disetujui.', 'sort' => 0, 'created_at' => $now, 'updated_at' => $now],
                ['pertanyaan' => 'Saya lupa kata sandi. Apa yang harus dilakukan?', 'jawaban' => 'Hubungi admin pada jam kerja agar kata sandi dapat diatur ulang. Jangan membagikan akun kepada petugas lain.', 'sort' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['pertanyaan' => 'Di mana saya melihat cara mengarsipkan dokumen?', 'jawaban' => 'Langkah kerja ada di halaman Prosedur. Halaman Manfaat menjelaskan kegunaan sistem dalam tugas sehari-hari.', 'sort' => 2, 'created_at' => $now, 'updated_at' => $now],
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('faq');
        Schema::dropIfExists('alur_kerja');
        Schema::dropIfExists('manfaat');
        Schema::dropIfExists('beranda_slides');
    }
};
