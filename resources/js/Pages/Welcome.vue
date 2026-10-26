<script setup>
import { institution } from '@/config/institution';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
});
</script>

<template>
    <Head title="Beranda" />

    <PublicLayout :can-login="canLogin">
        <template #skeleton>
            <section class="flex-1 border-b border-slate-100 bg-white">
                <div class="mx-auto grid w-full max-w-6xl items-center gap-12 px-6 py-20 lg:grid-cols-2 lg:gap-16 lg:py-28">
                    <div>
                        <div class="mx-auto h-10 w-3/4 rounded bg-slate-200 lg:mx-0" />
                        <div class="mx-auto mt-6 h-4 w-full rounded bg-slate-200 lg:mx-0" />
                        <div class="mx-auto mt-2 h-4 w-full rounded bg-slate-200 lg:mx-0" />
                        <div class="mx-auto mt-2 h-4 w-1/2 rounded bg-slate-200 lg:mx-0" />
                    </div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-5">
                        <div class="h-32 rounded-2xl bg-slate-100 sm:col-span-3" />
                        <div class="h-32 rounded-2xl bg-slate-100 sm:col-span-2" />
                    </div>
                </div>
            </section>
        </template>

        <section class="border-b border-slate-100 bg-white shadow-sm">
            <div
                class="mx-auto grid w-full max-w-6xl items-center gap-12 px-6 py-20 lg:grid-cols-2 lg:gap-16 lg:py-28"
            >
                <div>
                    <h1
                        class="text-center font-display text-4xl font-bold leading-[1.15] tracking-tight text-brand-navy md:text-5xl lg:text-6xl lg:text-left"
                    >
                        {{ institution.fullTitle }}
                    </h1>
                    <p class="mx-auto mt-6 max-w-xl text-base leading-8 text-slate-600 lg:mx-0 lg:text-left">
                        Sistem pencatatan dan penelusuran arsip dokumen kelurahan.
                        Dioptimalkan dengan teknologi QR Code untuk memastikan
                        transparansi, pelacakan akurat, dan efisiensi pelayanan
                        tata usaha.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-5">
                    <div
                        class="rounded-2xl bg-slate-50 p-6 shadow-sm border border-slate-100/50 sm:col-span-3"
                    >
                        <div class="flex items-start gap-4">
                            <span
                                class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white text-brand-navy shadow-sm"
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    class="h-5 w-5"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                    aria-hidden="true"
                                >
                                    <circle cx="12" cy="12" r="9" />
                                    <path
                                        stroke-linecap="round"
                                        d="M12 7v5l3 2"
                                    />
                                </svg>
                            </span>
                            <div>
                                <p class="text-sm font-semibold text-brand-navy">
                                    Jam pelayanan
                                </p>
                                <p class="mt-1 text-sm leading-7 text-slate-600">
                                    {{ institution.officeHours }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="flex flex-col rounded-2xl bg-slate-50 p-6 shadow-sm border border-slate-100/50 sm:col-span-2"
                    >
                        <div class="flex items-start gap-4">
                            <span
                                class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white text-brand-navy shadow-sm"
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    class="h-5 w-5"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                    aria-hidden="true"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M12 3l7 3v5c0 4.5-3 8.5-7 10-4-1.5-7-5.5-7-10V6l7-3z"
                                    />
                                    <circle cx="12" cy="10" r="2" />
                                    <path
                                        stroke-linecap="round"
                                        d="M8.5 16.5c.9-1 2.2-1.5 3.5-1.5s2.6.5 3.5 1.5"
                                    />
                                </svg>
                            </span>
                            <div>
                                <p class="text-sm font-semibold text-brand-navy">
                                    Akses sistem
                                </p>
                                <p class="mt-1 text-sm leading-7 text-slate-600">
                                    {{ institution.accessNotice }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
