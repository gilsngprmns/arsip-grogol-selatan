<script setup>
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: 'Konfirmasi',
    },
    message: {
        type: String,
        default: '',
    },
    confirmLabel: {
        type: String,
        default: 'Hapus',
    },
});

const emit = defineEmits(['close', 'confirm']);

const close = () => emit('close');
const confirm = () => emit('confirm');
</script>

<template>
    <Modal :show="show" max-width="sm" @close="close">
        <div class="border-b border-slate-100 px-6 py-4">
            <div class="flex items-start justify-between gap-4">
                <h2 class="text-lg font-bold text-slate-800">{{ title }}</h2>
                <button
                    type="button"
                    class="rounded-md p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                    @click="close"
                >
                    <span class="sr-only">Tutup</span>
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18" />
                    </svg>
                </button>
            </div>
        </div>

        <div class="px-6 py-5">
            <div class="flex items-start gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-rose-100">
                    <svg class="h-5 w-5 text-rose-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <p class="text-sm text-slate-600">{{ message }}</p>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4">
            <SecondaryButton type="button" @click="close">Batal</SecondaryButton>
            <PrimaryButton
                class="bg-rose-600 hover:bg-rose-700"
                @click="confirm"
            >
                {{ confirmLabel }}
            </PrimaryButton>
        </div>
    </Modal>
</template>
