<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { institution } from '@/config/institution';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Lupa kata sandi" />

        <div class="mb-8">
            <p class="text-sm font-medium text-brand-blue">Pemulihan akun</p>
            <h1 class="mt-3 font-display text-3xl font-bold tracking-tight text-brand-navy">
                Lupa kata sandi?
            </h1>
            <p class="mt-3 text-sm leading-7 text-slate-500">
                Masukkan alamat email akun Anda. Kami akan mengirim tautan untuk mengatur ulang kata sandi.
                Jika email tidak sampai, hubungi admin pada jam kerja.
            </p>
            <p class="mt-2 text-xs leading-6 text-slate-400">
                {{ institution.adminNote }}
            </p>
        </div>

        <div
            v-if="status"
            class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800"
        >
            {{ status }}
        </div>

        <form class="space-y-5" @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Alamat email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-2 block w-full rounded-lg border-slate-300 px-4 py-3 shadow-sm focus:border-brand-blue focus:ring-brand-blue"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <Link
                    :href="route('login')"
                    class="text-sm font-semibold text-slate-500 transition duration-200 hover:text-brand-blue"
                >
                    Kembali ke masuk
                </Link>
                <PrimaryButton :disabled="form.processing">
                    Kirim tautan reset
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
