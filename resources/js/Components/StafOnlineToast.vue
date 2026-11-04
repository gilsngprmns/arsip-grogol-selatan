<script setup>
import { ref } from 'vue';
import AppIcon from '@/Components/AppIcon.vue';

// Toast ditrigger oleh AppLayout lewat event: push staff baru, auto-hide.
const toasts = ref([]);

let nextId = 1;

function addToast(staff) {
    const id = nextId++;
    toasts.value.push({ id, staff });
    // Auto-hide 4 detik
    setTimeout(() => {
        toasts.value = toasts.value.filter((t) => t.id !== id);
    }, 4000);
}

function dismiss(id) {
    toasts.value = toasts.value.filter((t) => t.id !== id);
}

// AppLayout memanggil addToast via ref; buka lewat expose
defineExpose({ addToast });
</script>

<template>
    <!-- Fixed, dari atas ke bawah, di atas header (z-30) -->
    <div class="pointer-events-none fixed inset-x-0 top-3 z-[100] flex flex-col items-center gap-2 px-4 sm:items-end sm:pr-4">
        <TransitionGroup name="staf-toast">
            <div
                v-for="t in toasts"
                :key="t.id"
                class="pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-xl border border-brand-blue/20 bg-white p-3 shadow-lg sm:w-auto sm:min-w-[240px]"
            >
                <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-emerald-500" />
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-brand-navy">
                        {{ t.staff.name }} sedang aktif
                    </p>
                    <p class="text-xs text-slate-500">
                        {{ t.staff.seksi || 'Staf' }} · baru login
                    </p>
                </div>
                <button
                    type="button"
                    class="shrink-0 text-slate-400 transition hover:text-slate-600"
                    aria-label="Tutup notifikasi"
                    @click="dismiss(t.id)"
                >
                    <AppIcon name="close" :size="16" :stroke-width="1.8" class="h-4 w-4" decorative />
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>

<style scoped>
/* Dari atas ke bawah: masuk dari atas (-8px) lalu turun ke posisi normal */
.staf-toast-enter-active,
.staf-toast-leave-active {
    transition: transform 0.25s ease-out, opacity 0.25s ease-out;
}
.staf-toast-enter-from,
.staf-toast-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}
.staf-toast-move {
    transition: transform 0.25s ease-out;
}
@media (prefers-reduced-motion: reduce) {
    .staf-toast-enter-active,
    .staf-toast-leave-active,
    .staf-toast-move {
        transition: none;
    }
}
</style>
