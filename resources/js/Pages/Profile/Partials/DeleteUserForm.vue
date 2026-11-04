<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="text-lg font-semibold text-brand-navy">
                Hapus akun
            </h2>

            <p class="mt-1 text-sm leading-7 text-slate-500">
                Setelah akun dihapus, data terkait tidak dapat dikembalikan.
                Pastikan Anda sudah menyimpan informasi yang diperlukan sebelum melanjutkan.
            </p>
        </header>

        <DangerButton @click="confirmUserDeletion">Hapus akun</DangerButton>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-brand-navy">
                    Yakin ingin menghapus akun?
                </h2>

                <p class="mt-2 text-sm leading-7 text-slate-500">
                    Semua data akun akan dihapus secara permanen. Masukkan kata sandi Anda untuk mengonfirmasi.
                </p>

                <div class="mt-6">
                    <InputLabel
                        for="password"
                        value="Kata sandi"
                        class="sr-only"
                    />

                    <TextInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-3/4 rounded-lg"
                        @keyup.enter="deleteUser"
                    />

                    <InputError :message="form.errors.password" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="closeModal">
                        Batal
                    </SecondaryButton>

                    <DangerButton
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        Hapus akun
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </section>
</template>
