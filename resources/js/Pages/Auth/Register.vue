<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { institution } from '@/config/institution';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <GuestLayout>
        <Head title="Pendaftaran tidak tersedia" />

        <div class="mb-8">
            <p class="text-sm font-medium text-brand-blue">{{ institution.accessNotice }}</p>
            <h1 class="mt-3 font-display text-3xl font-bold tracking-tight text-brand-navy">
                Pendaftaran mandiri tidak tersedia
            </h1>
            <p class="mt-3 text-sm leading-7 text-slate-500">
                {{ institution.eligibility }}
            </p>
            <p class="mt-3 text-sm leading-7 text-slate-500">
                {{ institution.accountHowTo }}
            </p>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <Link
                :href="route('bantuan')"
                class="inline-flex min-h-11 items-center justify-center rounded-lg bg-brand-blue px-4 py-2.5 text-sm font-semibold text-white transition duration-200 hover:bg-blue-700"
            >
                Buka halaman bantuan
            </Link>
            <Link
                :href="route('login')"
                class="inline-flex min-h-11 items-center justify-center rounded-lg px-2 text-sm font-semibold text-slate-500 transition duration-200 hover:text-brand-navy"
            >
                Sudah punya akun? Masuk
            </Link>
        </div>
    </GuestLayout>
</template>
