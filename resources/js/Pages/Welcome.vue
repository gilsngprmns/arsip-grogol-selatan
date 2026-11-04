<script setup>
import { institution } from '@/config/institution';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head } from '@inertiajs/vue3';
import { onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps({
    canLogin: {
        type: Boolean,
    },
    berandaSlides: {
        type: Array,
        default: () => [],
    },
});

/* ============ Hero carousel ====== */
const SLIDE_INTERVAL_MS = 5000;
const currentIndex = ref(0);
let timerId = null;

const showSlide = (index) => {
    currentIndex.value = index;
};

const nextSlide = () => {
    if (props.berandaSlides.length === 0) return;
    showSlide((currentIndex.value + 1) % props.berandaSlides.length);
};
const prevSlide = () => {
    if (props.berandaSlides.length === 0) return;
    showSlide((currentIndex.value - 1 + props.berandaSlides.length) % props.berandaSlides.length);
};

const startAutoSlide = () => {
    clearInterval(timerId);
    if (props.berandaSlides.length <= 1) return;
    timerId = setInterval(nextSlide, SLIDE_INTERVAL_MS);
};

const goTo = (index) => {
    showSlide(index);
    // Reset timer auto-play agar slide tidak berganti sesaat setelah interaksi manual.
    startAutoSlide();
};

onMounted(startAutoSlide);

onBeforeUnmount(() => {
    clearInterval(timerId);
});
</script>

<template>
    <Head title="Beranda" />

    <PublicLayout :can-login="canLogin">
        <template #skeleton>
            <!-- Placeholder setinggi hero (calc(100dvh-4.5rem), min 380px) agar
                 tinggi halaman sama saat loading dan setelah konten tampil,
                 tanpa lompat vertikal (prinsip Fix #67). 4.5rem = tinggi
                 header sticky PublicLayout. -->
            <section class="bg-white">
                <div class="h-[calc(100dvh-4.5rem)] min-h-[380px] w-full bg-slate-200" />
            </section>
            <!-- Skeleton section keamanan: geometri diselaraskan dengan konten
                 (Fix #67) agar halaman tidak melompat saat muat selesai. -->
            <section class="border-b border-slate-100 bg-white">
                <div class="mx-auto max-w-4xl px-4 py-16 text-center sm:px-6">
                    <div class="mx-auto h-8 w-2/3 rounded bg-slate-200" />
                    <div class="mx-auto mt-4 h-4 w-full max-w-3xl rounded bg-slate-200" />
                    <div class="mx-auto mt-2 h-4 w-4/5 max-w-3xl rounded bg-slate-200" />
                    <div class="mt-12 grid grid-cols-1 gap-8 md:grid-cols-3">
                        <div v-for="i in 3" :key="'keamanan-sk-' + i" class="flex flex-col items-center">
                            <div class="h-12 w-12 rounded-full bg-slate-200" />
                            <div class="mt-4 h-4 w-3/4 rounded bg-slate-200" />
                            <div class="mt-2 h-3 w-full max-w-xs rounded bg-slate-200" />
                            <div class="mt-1 h-3 w-full max-w-xs rounded bg-slate-200" />
                        </div>
                    </div>
                </div>
            </section>
            <!-- Skeleton section hukum: geometri setara konten (3 kartu
                 h-32 rounded-2xl + baris judul/deskripsi, Fix #67). -->
            <section class="border-b border-slate-100 bg-white">
                <div class="mx-auto max-w-4xl px-4 py-16 sm:px-6">
                    <div class="mx-auto h-8 w-1/2 rounded bg-slate-200" />
                    <div class="mx-auto mt-4 h-4 w-full max-w-3xl rounded bg-slate-200" />
                    <div class="mx-auto mt-2 h-4 w-4/5 max-w-3xl rounded bg-slate-200" />
                    <div class="mt-12 grid grid-cols-1 gap-6 md:grid-cols-3">
                        <div v-for="i in 3" :key="'hukum-sk-' + i" class="rounded-2xl bg-slate-200 p-6 text-left">
                            <div class="h-4 w-24 rounded bg-slate-300" />
                            <div class="mt-3 h-4 w-3/4 rounded bg-slate-300" />
                            <div class="mt-3 h-3 w-full rounded bg-slate-300" />
                            <div class="mt-2 h-3 w-5/6 rounded bg-slate-300" />
                        </div>
                    </div>
                </div>
            </section>
        </template>

        <!-- Hero full-bleed setinggi viewport (calc(100dvh - tinggi header
             sticky 4.5rem), min 380px): section di bawahnya baru terlihat
             saat di-scroll; teks hero boleh terpotong di viewport pendek
             (keputusan user). Foto carousel menyedilkan tepi layar, judul +
             deskripsi ditumpangkan di bawah lewat gradasi navy.
             Crossfade lewat utility opacity, semua slide tumpang tindih. -->
        <section class="relative bg-white">
            <div
                class="relative h-[calc(100dvh-4.5rem)] min-h-[380px] w-full overflow-hidden"
                aria-label="Foto kantor kelurahan"
                aria-roledescription="carousel"
            >
                <img
                    v-for="(slide, index) in berandaSlides"
                    :key="slide.id"
                    :src="slide.path"
                    alt=""
                    :loading="index === 0 ? 'eager' : 'lazy'"
                    draggable="false"
                    class="absolute inset-0 h-full w-full object-cover transition-opacity duration-700 ease-in-out"
                    :class="index === currentIndex ? 'opacity-100' : 'opacity-0'"
                />
                <!-- Gradasi navy dari bawah: membuat teks putih blok judul kontras
                     (white vs #0B1F3A ≈ 14:1, lulus WCAG AA), foto bagian atas
                     tetap jernih. Warna = brand-navy core, bukan warna baru. -->
                <div
                    class="absolute inset-0 bg-gradient-to-t from-brand-navy/90 via-brand-navy/40 to-transparent"
                    aria-hidden="true"
                />

                <button
                    type="button"
                    class="absolute left-4 top-1/2 flex h-11 w-11 -translate-y-1/2 cursor-pointer items-center justify-center rounded-full bg-white/20 text-white backdrop-blur-sm transition-all hover:bg-white/40 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/70"
                    aria-label="Slide sebelumnya"
                    @click="prevSlide()"
                >
                    <AppIcon name="chevron-left" :size="24" :stroke-width="2" class="h-6 w-6" decorative />
                </button>
                <button
                    type="button"
                    class="absolute right-4 top-1/2 flex h-11 w-11 -translate-y-1/2 cursor-pointer items-center justify-center rounded-full bg-white/20 text-white backdrop-blur-sm transition-all hover:bg-white/40 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/70"
                    aria-label="Slide berikutnya"
                    @click="nextSlide()"
                >
                    <AppIcon name="chevron-right" :size="24" :stroke-width="2" class="h-6 w-6" decorative />
                </button>

                <!-- Blok judul: satu-satunya fokus per layar (lever antislop);
                     pb-16 memberi jarak ke dot di bawahnya. -->
                <div class="absolute inset-x-0 bottom-0">
                    <div class="mx-auto max-w-3xl px-6 pb-16 text-center">
                        <h1
                            class="font-display text-3xl font-bold leading-[1.15] tracking-tight text-white sm:text-4xl md:text-5xl"
                        >
                            {{ institution.fullTitle }}
                        </h1>
                        <p class="mx-auto mt-4 max-w-xl text-sm leading-6 text-slate-200 sm:text-base sm:leading-7">
                            Sistem pencatatan dan penelusuran arsip dokumen kelurahan.
                            Dioptimalkan dengan teknologi QR Code untuk memastikan
                            transparansi, pelacakan akurat, dan efisiensi pelayanan
                            tata usaha.
                        </p>
                    </div>
                </div>

                <div
                    class="absolute bottom-5 left-1/2 flex -translate-x-1/2 space-x-2"
                    role="group"
                    aria-label="Pilih slide"
                >
                    <button
                        v-for="(slide, index) in berandaSlides"
                        :key="`dot-${slide.id}`"
                        type="button"
                        class="h-2.5 w-2.5 rounded-full transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-white/70"
                        :class="index === currentIndex ? 'bg-white' : 'bg-white/50 hover:bg-white/80'"
                        :aria-label="`Ke slide ${index + 1}`"
                        @click="goTo(index)"
                    />
                </div>
            </div>
        </section>

        <!-- Standar keamanan: 3 pilar yang memetakan fitur nyata aplikasi
             (role admin/staf + middleware EnsureUserIsAdmin; ActivityLog via
             middleware RecordActivity global; backup arsip:cadangkan harian 02:00).
             Satu fokus per layar (prinsip R-31): klaim keamanan adalah inti
             penutup halaman publik, bukan dekorasi. Warna = token DESIGN.md;
             lingkaran ikon membingkai aksen fungsional sesuai makna palet. -->
        <section class="border-b border-slate-100 bg-white">
            <div id="keamanan" class="mx-auto max-w-4xl px-4 py-16 text-center sm:px-6">
                <h2 v-reveal class="font-display text-3xl font-bold text-brand-navy md:text-4xl">
                    Standar Keamanan &amp; Integritas Data
                </h2>
                <p v-reveal class="mx-auto mt-4 max-w-3xl text-base leading-8 text-slate-600">
                    Sistem mencatat, melacak, dan menyelamatkan arsip dokumen
                    kelurahan dengan kontrol akses, rekam jejak, dan cadangan
                    data rutin.
                </p>

                <div class="mt-12 grid grid-cols-1 gap-8 text-center md:grid-cols-3">
                    <!-- Pilar 1: perisai = pintu masuk dibatasi, sesuai peran. -->
                    <div v-reveal :style="{ transitionDelay: '0ms' }">
                        <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-brand-blue-soft text-brand-blue">
                            <AppIcon name="shield" :size="20" :stroke-width="1.5" class="h-5 w-5" decorative />
                        </span>
                        <h3 class="mt-4 text-base font-bold text-brand-navy">Akses Terbatas</h3>
                        <p class="mx-auto mt-2 max-w-xs text-sm leading-7 text-slate-600">
                            Hanya petugas terdaftar yang dapat masuk. Peran
                            admin dan staf masing-masing memegang modul dan
                            tindakan yang menjadi tanggung jawabnya.
                        </p>
                    </div>

                    <!-- Pilar 2: dokumen bercentang = tercatat & terverifikasi jejaknya. -->
                    <div v-reveal :style="{ transitionDelay: '100ms' }">
                        <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                            <AppIcon name="file-check" :size="20" :stroke-width="1.5" class="h-5 w-5" decorative />
                        </span>
                        <h3 class="mt-4 text-base font-bold text-brand-navy">Rekam Jejak Audit</h3>
                        <p class="mx-auto mt-2 max-w-xs text-sm leading-7 text-slate-600">
                            Setiap pencatatan, perubahan, dan penghapusan data
                            terekam lengkap dengan waktu dan pelakunya, sehingga
                            dapat ditelusuri kembali kapan pun.
                        </p>
                    </div>

                    <!-- Pilar 3: rak/server = penyimpanan terpusat + cadangan terjadwal. -->
                    <div v-reveal :style="{ transitionDelay: '200ms' }">
                        <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-amber-50 text-amber-600">
                            <AppIcon name="server" :size="20" :stroke-width="1.5" class="h-5 w-5" decorative />
                        </span>
                        <h3 class="mt-4 text-base font-bold text-brand-navy">Sentralisasi &amp; Cadangan Arsip</h3>
                        <p class="mx-auto mt-2 max-w-xs text-sm leading-7 text-slate-600">
                            Arsip disimpan dalam satu sistem terpusat, tidak
                            bergantung pada perangkat tertentu, dan mendapat
                            cadangan otomatis setiap hari.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Hukum & standar operasional: landasan regulasi yang mengikat sistem
             (UU 43/2009 kearsipan nasional; Pergub DKI 99/2021 tata naskah
             dinas; SOP kelurahan sebagai standar internal).
             Kartu mengikuti pola publik Fitur.vue (rounded-2xl bg-slate-50
             shadow, hover shadow, tanpa border); badge biru memakai token
             brand (R-29), emerald/amber = aksen fungsional DESIGN.md. -->
        <section class="border-b border-slate-100 bg-white">
            <div id="hukum" class="mx-auto max-w-4xl px-4 py-16 text-center sm:px-6">
                <h2 v-reveal class="font-display text-3xl font-bold text-brand-navy md:text-4xl">
                    Hukum &amp; Standar Operasional
                </h2>
                <p v-reveal class="mx-auto mt-4 max-w-3xl text-base leading-8 text-slate-600">
                    Pengelolaan arsip digital kelurahan dilaksanakan berpedoman
                    pada regulasi kearsipan dan standar operasional yang berlaku.
                </p>

                <div class="mt-12 grid grid-cols-1 gap-6 md:grid-cols-3 md:text-left">
                    <!-- Badge biru = token brand; bukan Tailwind raw (R-29). -->
                    <article v-reveal :style="{ transitionDelay: '0ms' }" class="rounded-2xl bg-slate-50 p-6 shadow-sm transition-all duration-300 hover:shadow-md">
                        <span class="mb-3 inline-block w-fit rounded-md bg-brand-blue-soft px-2.5 py-1 text-xs font-semibold text-brand-blue">
                            Undang-Undang
                        </span>
                        <h3 class="text-base font-bold text-brand-navy">
                            UU No. 43 Tahun 2009
                        </h3>
                        <p class="mt-2 text-sm leading-7 text-slate-500">
                            Landasan kearsipan nasional: menjamin ketersediaan
                            arsip yang otentik, utuh, dan terpercaya.
                        </p>
                    </article>

                    <!-- emerald = aksen fungsional "aman/tersusun" (DESIGN.md). -->
                    <article v-reveal :style="{ transitionDelay: '100ms' }" class="rounded-2xl bg-slate-50 p-6 shadow-sm transition-all duration-300 hover:shadow-md">
                        <span class="mb-3 inline-block w-fit rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-600">
                            Peraturan Gubernur
                        </span>
                        <h3 class="text-base font-bold text-brand-navy">
                            Pergub DKI No. 99 Tahun 2021
                        </h3>
                        <p class="mt-2 text-sm leading-7 text-slate-500">
                            Standar tata naskah dinas DKI: mengatur jenis,
                            format, penyiapan, pengamanan, distribusi, dan
                            penyimpanan naskah di lingkungan pemerintahan
                            daerah.
                        </p>
                    </article>

                    <!-- amber = aksen fungsional terbatas (DESIGN.md). -->
                    <article v-reveal :style="{ transitionDelay: '200ms' }" class="rounded-2xl bg-slate-50 p-6 shadow-sm transition-all duration-300 hover:shadow-md">
                        <span class="mb-3 inline-block w-fit rounded-md bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-600">
                            Standar Operasional
                        </span>
                        <h3 class="text-base font-bold text-brand-navy">
                            SOP Tata Usaha Kelurahan
                        </h3>
                        <p class="mt-2 text-sm leading-7 text-slate-500">
                            Prosedur internal pencatatan, penyimpanan fisik
                            rak, dan sirkulasi peminjaman dokumen.
                        </p>
                    </article>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
