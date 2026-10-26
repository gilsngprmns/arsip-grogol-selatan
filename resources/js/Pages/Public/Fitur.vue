<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
});

/** Enam manfaat kerja — bukan katalog fitur produk. */
const benefits = [
    {
        title: 'Dokumen lebih mudah ditemukan',
        description:
            'Nomor surat, perihal, dan klasifikasi tercatat rapi sehingga petugas tidak perlu mencari berkas secara manual berulang kali.',
        icon: 'folder',
    },
    {
        title: 'Lokasi penyimpanan jelas',
        description:
            'Setiap dokumen dihubungkan ke ruang, rak, dan boks fisik. Saat dibutuhkan, petugas tahu tepat di mana berkas disimpan.',
        icon: 'pin',
    },
    {
        title: 'Peminjaman tetap terkendali',
        description:
            'Siapa yang meminjam, kapan harus dikembalikan, dan status dokumen dapat dipantau tanpa catatan lepas.',
        icon: 'circulation',
    },
    {
        title: 'Digitalisasi & Proteksi Berkas',
        description:
            'Salinan pindaian dokumen (PDF / foto) terlampir pada arsip, sehingga fisik yang lapuk atau hilang tidak menghilangkan isi dokumen.',
        icon: 'shield',
    },
    {
        title: 'Pelabelan QR Code Otomatis',
        description:
            'Setiap dokumen langsung mendapat stiker label QR yang siap dicetak, sehingga identifikasi dan verifikasi berkas di rak lebih cepat.',
        icon: 'qr',
    },
    {
        title: 'Transparansi & Jejak Audit',
        description:
            'Riwayat peminjaman dan perubahan data tercatat rinci, menjaga akuntabilitas internal tata usaha tetap dapat ditelusuri.',
        icon: 'history',
    },
];
</script>

<template>
    <Head title="Manfaat" />

    <PublicLayout :can-login="canLogin">
        <template #skeleton>
            <section class="flex-1 border-b border-slate-100 bg-white">
                <div class="mx-auto w-full max-w-5xl px-6 py-20 lg:py-28">
                    <div class="mx-auto h-10 w-2/3 rounded bg-slate-200" />
                    <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        <div v-for="i in 6" :key="'sk-' + i" class="h-40 rounded-2xl bg-slate-100" />
                    </div>
                </div>
            </section>
        </template>

        <section class="border-b border-slate-100 bg-white shadow-sm">
            <div class="mx-auto w-full max-w-5xl px-6 py-20 lg:py-28">
                <h1
                    class="text-center font-display text-4xl font-bold leading-[1.15] tracking-tight text-brand-navy md:text-5xl lg:text-6xl"
                >
                    Manfaat dalam kerja pengarsipan
                </h1>

                <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <article
                        v-for="benefit in benefits"
                        :key="benefit.title"
                        class="rounded-2xl bg-slate-50 p-6 shadow-sm transition-all duration-300 hover:shadow-md hover:-translate-y-0.5"
                    >
                        <span
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 text-blue-900"
                        >
                            <!-- folder -->
                            <svg
                                v-if="benefit.icon === 'folder'"
                                viewBox="0 0 24 24"
                                class="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"
                                />
                            </svg>
                            <!-- pin lokasi -->
                            <svg
                                v-else-if="benefit.icon === 'pin'"
                                viewBox="0 0 24 24"
                                class="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 21s-6.5-5.4-6.5-10.5A6.5 6.5 0 0112 4a6.5 6.5 0 016.5 6.5C18.5 15.6 12 21 12 21z"
                                />
                                <circle cx="12" cy="10.5" r="2.5" />
                            </svg>
                            <!-- panah sirkulasi -->
                            <svg
                                v-else-if="benefit.icon === 'circulation'"
                                viewBox="0 0 24 24"
                                class="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M4 12a8 8 0 0114.9-4M20 12a8 8 0 01-14.9 4"
                                />
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M18.9 4.5v3.5H15.4M5.1 19.5V16h3.5"
                                />
                            </svg>
                            <!-- perisai dengan garis dokumen -->
                            <svg
                                v-else-if="benefit.icon === 'shield'"
                                viewBox="0 0 24 24"
                                class="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 3l7 3v5c0 4.5-3 8.5-7 10-4-1.5-7-5.5-7-10V6l7-3z"
                                />
                                <path
                                    stroke-linecap="round"
                                    d="M9.5 10h5M9.5 13h3.5"
                                />
                            </svg>
                            <!-- kotak kode QR -->
                            <svg
                                v-else-if="benefit.icon === 'qr'"
                                viewBox="0 0 24 24"
                                class="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                aria-hidden="true"
                            >
                                <rect x="3.5" y="3.5" width="17" height="17" rx="2.5" />
                                <rect x="7.5" y="7.5" width="3.5" height="3.5" />
                                <rect x="13" y="7.5" width="3.5" height="3.5" />
                                <rect x="7.5" y="13" width="3.5" height="3.5" />
                                <path stroke-linecap="round" d="M13 13h3.5v3.5" />
                                <circle cx="18.5" cy="18.5" r="1.5" />
                            </svg>
                            <!-- panah lingkaran waktu (riwayat) -->
                            <svg
                                v-else
                                viewBox="0 0 24 24"
                                class="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"
                                />
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M3 3v5h5"
                                />
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 7v5l4 2"
                                />
                            </svg>
                        </span>
                        <h2
                            class="font-display mt-5 text-lg font-semibold tracking-tight text-brand-navy"
                        >
                            {{ benefit.title }}
                        </h2>
                        <p class="mt-2 text-sm leading-7 text-slate-500">
                            {{ benefit.description }}
                        </p>
                    </article>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
