<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Konfirmasi kata sandi" />

        <div class="mb-8">
            <p class="text-sm font-medium text-brand-blue">Keamanan</p>
            <h1 class="mt-3 font-display text-3xl font-bold tracking-tight text-brand-navy">
                Konfirmasi kata sandi
            </h1>
            <p class="mt-3 text-sm leading-7 text-slate-500">
                Area ini memerlukan konfirmasi. Masukkan kata sandi Anda untuk melanjutkan.
            </p>
        </div>

        <form class="space-y-5" @submit.prevent="submit">
            <div>
                <InputLabel for="password" value="Kata sandi" />
                <TextInput
                    id="password"
                    type="password"
                    class="mt-2 block w-full rounded-lg border-slate-300 px-4 py-3 shadow-sm focus:border-brand-blue focus:ring-brand-blue"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    autofocus
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="pt-2">
                <PrimaryButton class="w-full" :disabled="form.processing">
                    Konfirmasi
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
