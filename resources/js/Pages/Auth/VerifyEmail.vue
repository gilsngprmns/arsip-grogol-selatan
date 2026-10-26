<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <GuestLayout>
        <Head title="Verifikasi email" />

        <div class="mb-8">
            <p class="text-sm font-medium text-brand-blue">Aktivasi akun</p>
            <h1 class="mt-3 font-display text-3xl font-bold tracking-tight text-brand-navy">
                Verifikasi alamat email
            </h1>
            <p class="mt-3 text-sm leading-7 text-slate-500">
                Sebelum mulai, silakan verifikasi alamat email Anda melalui tautan yang kami kirim.
                Jika belum menerima email, Anda dapat meminta pengiriman ulang.
            </p>
        </div>

        <div
            v-if="verificationLinkSent"
            class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800"
        >
            Tautan verifikasi baru telah dikirim ke alamat email Anda.
        </div>

        <form class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between" @submit.prevent="submit">
            <PrimaryButton :disabled="form.processing">
                Kirim ulang email verifikasi
            </PrimaryButton>

            <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="inline-flex min-h-11 items-center justify-center rounded-lg px-2 text-sm font-semibold text-slate-500 transition duration-200 hover:text-brand-navy focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-blue/40"
            >
                Keluar
            </Link>
        </form>
    </GuestLayout>
</template>
