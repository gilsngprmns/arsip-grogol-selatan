<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const page = usePage();

const user = computed(() => page.props.auth?.user);
const avatarPath = computed(() => page.props.auth?.user?.avatar_path);

const form = useForm({
    name: user.value?.name ?? '',
    email: user.value?.email ?? '',
    avatar: null,
});

const onAvatarChange = (event) => {
    form.avatar = event.target.files?.[0] ?? null;
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-semibold text-brand-navy">
                Informasi profil
            </h2>

            <p class="mt-1 text-sm leading-7 text-slate-500">
                Perbarui nama, email, dan foto profil akun Anda.
            </p>
        </header>

        <form
            @submit.prevent="form.transform((data) => ({ ...data, _method: 'patch' })).post(route('profile.update'), { forceFormData: true, preserveScroll: true, onSuccess: () => form.reset('avatar') })"
            class="mt-6 space-y-6"
        >
            <div>
                <InputLabel for="name" value="Nama" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full rounded-lg"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Alamat email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full rounded-lg"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="avatar" value="Foto profil" />
                <div class="mt-2 flex items-center gap-4">
                    <img
                        v-if="avatarPath"
                        :src="`/storage/${avatarPath}`"
                        alt="Foto profil"
                        class="h-14 w-14 rounded-full object-cover"
                    />
                    <input
                        id="avatar"
                        type="file"
                        accept=".jpg,.jpeg,.png,.webp,image/*"
                        class="block w-full text-sm text-slate-600"
                        @change="onAvatarChange"
                    />
                </div>
                <InputError class="mt-2" :message="form.errors.avatar" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="mt-2 text-sm text-slate-700">
                    Alamat email Anda belum diverifikasi.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="rounded-md text-sm font-semibold text-brand-blue underline hover:text-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-blue/40"
                    >
                        Kirim ulang email verifikasi
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-emerald-700"
                >
                    Tautan verifikasi baru telah dikirim ke email Anda.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Simpan</PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-brand-blue"
                    >
                        Tersimpan.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
