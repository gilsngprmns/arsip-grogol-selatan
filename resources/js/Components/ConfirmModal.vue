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
                    <AppIcon name="close" :size="20" :stroke-width="2" class="h-5 w-5" decorative />
                </button>
            </div>
        </div>

        <div class="px-6 py-5">
            <div class="flex items-start gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-rose-100">
                    <AppIcon name="warning" :size="20" :stroke-width="2" class="h-5 w-5 text-rose-600" decorative />
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
