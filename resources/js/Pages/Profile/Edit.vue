<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Profil" />

    <AppLayout>
        <template #skeleton-header>
            <div class="h-7 w-56 rounded bg-slate-200" />
        </template>

        <template #skeleton>
            <div class="mx-auto max-w-3xl space-y-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <div class="h-5 w-40 rounded bg-slate-200" />
                    <div class="mt-4 space-y-3">
                        <div v-for="i in 3" :key="'sk-prof-field-' + i" class="h-10 w-full rounded-xl bg-slate-200" />
                    </div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <div class="h-5 w-36 rounded bg-slate-200" />
                    <div class="mt-4 space-y-3">
                        <div v-for="i in 3" :key="'sk-pwd-field-' + i" class="h-10 w-full rounded-xl bg-slate-200" />
                    </div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <div class="h-5 w-32 rounded bg-slate-200" />
                    <div class="mt-4 h-10 w-full rounded-xl bg-slate-200" />
                </div>
            </div>
        </template>

        <template #header>
            <h1 class="text-2xl font-bold text-slate-900">Profil Pengguna</h1>
        </template>

        <div class="mx-auto max-w-3xl space-y-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <UpdateProfileInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                    class="max-w-xl"
                />
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <UpdatePasswordForm class="max-w-xl" />
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <DeleteUserForm class="max-w-xl" />
            </div>
        </div>
    </AppLayout>
</template>
