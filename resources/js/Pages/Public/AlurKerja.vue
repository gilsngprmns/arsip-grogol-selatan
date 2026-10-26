<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
});

const steps = [
    {
        number: '1',
        title: 'Catat dokumen',
        description:
            'Isi data surat (nomor, tanggal, perihal) dan unggah berkas pindaian bila tersedia.',
        result: 'Dokumen tercatat di sistem.',
    },
    {
        number: '2',
        title: 'Tetapkan lokasi penyimpanan',
        description:
            'Pilih ruang, rak, dan boks sesuai tempat fisik berkas, lalu simpan penempatan tersebut.',
        result: 'Lokasi dokumen diketahui saat dibutuhkan.',
    },
    {
        number: '3',
        title: 'Kelola peminjaman dan penelusuran',
        description:
            'Cari dokumen lewat pencarian, catat peminjaman bila dipinjam, dan kembalikan setelah selesai dipakai.',
        result: 'Status dan riwayat dokumen tetap terpantau.',
    },
];
</script>

<template>
    <Head title="Prosedur" />

    <PublicLayout :can-login="canLogin">
        <template #skeleton>
            <section class="flex-1 border-b border-slate-100 bg-white">
                <div class="mx-auto w-full max-w-3xl px-6 py-20 lg:py-28">
                    <div class="mx-auto h-10 w-2/3 rounded bg-slate-200" />
                    <div class="mt-14 space-y-10">
                        <div v-for="i in 3" :key="'sk-' + i" class="relative pl-14">
                            <div class="absolute left-0 top-0 h-10 w-10 rounded-full bg-slate-200" />
                            <div class="h-5 w-3/4 rounded bg-slate-200" />
                            <div class="mt-2 h-4 w-full rounded bg-slate-200" />
                            <div class="mt-2 h-4 w-full rounded bg-slate-200" />
                            <div class="mt-2 h-4 w-1/2 rounded bg-slate-100" />
                        </div>
                    </div>
                </div>
            </section>
        </template>

        <section class="border-b border-slate-100 bg-white shadow-sm">
            <div class="mx-auto w-full max-w-3xl px-6 py-20 lg:py-28">
                <h1
                    class="text-center font-display text-4xl font-bold leading-[1.15] tracking-tight text-brand-navy md:text-5xl lg:text-6xl"
                >
                    Cara mengarsipkan dokumen
                </h1>

                <ol class="mt-14">
                    <li
                        v-for="(step, index) in steps"
                        :key="step.number"
                        class="relative pl-16"
                        :class="index < steps.length - 1 ? 'pb-14' : ''"
                    >
                        <!-- garis konektor ke langkah berikutnya -->
                        <span
                            v-if="index < steps.length - 1"
                            aria-hidden="true"
                            class="absolute bottom-0 left-4 top-10 w-px -translate-x-1/2 bg-slate-200"
                        ></span>

                        <!-- node penomoran -->
                        <span
                            aria-hidden="true"
                            class="absolute left-4 top-0 flex h-10 w-10 -translate-x-1/2 items-center justify-center rounded-full bg-brand-navy text-base font-bold text-white ring-4 ring-white"
                        >
                            {{ step.number }}
                        </span>

                        <h2
                            class="font-display text-xl font-semibold tracking-tight text-brand-navy"
                        >
                            {{ step.title }}
                        </h2>
                        <p class="mt-2 text-base leading-8 text-slate-600">
                            {{ step.description }}
                        </p>
                        <p class="mt-2 text-sm leading-7 text-slate-500">
                            <span class="font-semibold text-slate-700">Hasil:</span>
                            {{ step.result }}
                        </p>
                    </li>
                </ol>
            </div>
        </section>
    </PublicLayout>
</template>
