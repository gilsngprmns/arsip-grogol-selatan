<script setup>
import { nextTick, onMounted, onUnmounted, ref, watch } from 'vue';

/**
 * Lightbox bersama untuk menampilkan gambar utuh (logo/avatar) di tengah
 * layar: latar gelap, klik overlay atau Escape menutup.
 *
 * Dirender via <Teleport to="body"> + position:fixed agar lolos dari
 * overflow rail sidebar dan transform drawer mobile (pola yang sama dengan
 * tooltip bersama Fix #44).
 *
 * Pemegang state tetap orang tua (prop `show` + `src`); komponen ini hanya
 * menangani render, fokus, dan penutupan.
 */
const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    src: {
        type: String,
        default: '',
    },
    label: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['close']);

const overlay = ref(null);

// Saat terbuka: alihkan fokus ke overlay (tabindex=-1) agar Tab/Enter logis
// dan Escape tidak jatuh ke elemen lain.
watch(
    () => props.show,
    (open) => {
        if (open) {
            nextTick(() => overlay.value?.focus());
        }
    },
);

const onKey = (e) => {
    if (e.key === 'Escape' && props.show) {
        emit('close');
    }
};

onMounted(() => {
    window.addEventListener('keydown', onKey);
});

onUnmounted(() => {
    window.removeEventListener('keydown', onKey);
});
</script>

<template>
    <Teleport to="body">
        <div
            v-if="show && src"
            ref="overlay"
            role="dialog"
            aria-modal="true"
            :aria-label="`Gambar: ${label}`"
            tabindex="-1"
            class="fixed inset-0 z-[70] flex cursor-zoom-out items-center justify-center bg-slate-900/85 p-6 backdrop-blur-sm outline-none"
            @click.self="emit('close')"
        >
            <img
                :src="src"
                :alt="label"
                class="max-h-full max-w-full cursor-default rounded-lg object-contain shadow-2xl"
                @click.stop
            />
        </div>
    </Teleport>
</template>
