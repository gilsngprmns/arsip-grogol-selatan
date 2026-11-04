<script setup>
import ConfirmModal from '@/Components/ConfirmModal.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, onUnmounted, ref, watch } from 'vue';

const props = defineProps({
    berandaSlides: { type: Array, default: () => [] },
});

const page = usePage();

// Flash
const flashSuccess = computed(() => page.props.flash?.success);
const flashVisible = ref(false);
let flashTimer = null;
watch(
    () => page.props.flash?.success,
    (v) => {
        clearTimeout(flashTimer);
        if (v) {
            flashVisible.value = true;
            flashTimer = setTimeout(() => { flashVisible.value = false; }, 5000);
        }
    },
    { immediate: true },
);

const flashDanger = computed(() => page.props.flash?.danger);
const flashDangerVisible = ref(false);
let flashDangerTimer = null;
watch(
    () => page.props.flash?.danger,
    (v) => {
        clearTimeout(flashDangerTimer);
        if (v) {
            flashDangerVisible.value = true;
            flashDangerTimer = setTimeout(() => { flashDangerVisible.value = false; }, 5000);
        }
    },
    { immediate: true },
);

onUnmounted(() => {
    clearTimeout(flashTimer);
    clearTimeout(flashDangerTimer);
});

const processing = ref(false);

const slideForm = ref({ foto: null });
const resetSlideForm = () => { slideForm.value = { foto: null }; };
const submitSlide = (e) => {
    e.preventDefault();
    if (!slideForm.value.foto) return;
    const fd = new FormData();
    fd.append('foto', slideForm.value.foto);
    processing.value = true;
    router.post(route('beranda.slides.store'), fd, {
        preserveScroll: true,
        onSuccess: () => { processing.value = false; resetSlideForm(); slideToDeleteId.value = ''; },
        onFinish: () => { processing.value = false; },
    });
};

const slideToDeleteId = ref('');
const selectSlideForDeletion = (id) => {
    slideToDeleteId.value = String(id);
};
const showDeleteSlideConfirm = ref(false);
const openDeleteSlide = () => { showDeleteSlideConfirm.value = true; };
const closeDeleteSlide = () => { showDeleteSlideConfirm.value = false; slideToDeleteId.value = ''; };
const confirmDeleteSlide = () => {
    const id = slideToDeleteId.value;
    closeDeleteSlide();
    if (id === '') return;
    router.delete(route('beranda.slides.destroy', id), { preserveScroll: true });
};
</script>

<template>
    <Head title="Foto Carousel - Halaman Publik" />
    <AppLayout>
        <template #skeleton-header>
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <div class="h-7 w-48 rounded bg-slate-200" />
                    <div class="h-4 w-72 rounded bg-slate-200" />
                </div>
            </div>
        </template>

        <template #skeleton>
            <div class="space-y-6">
                <div v-for="i in 2" :key="'sk-hp-' + i" class="rounded-2xl bg-slate-100 p-4">
                    <div class="h-5 w-40 rounded bg-slate-200" />
                    <div class="mt-4 h-16 rounded-xl bg-slate-200" />
                </div>
            </div>
        </template>

        <template #header>
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Foto Carousel Beranda</h1>
                <p class="text-sm text-slate-500">Gambar besar di halaman Beranda. Urutan otomatis mengikuti urutan upload.</p>
            </div>
        </template>

        <div
            v-if="flashSuccess && flashVisible"
            class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800"
            role="status"
        >
            {{ flashSuccess }}
        </div>

        <div
            v-if="flashDanger && flashDangerVisible"
            class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800"
            role="alert"
        >
            {{ flashDanger }}
        </div>

        <div v-reveal class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-800">Foto Carousel Beranda</h2>

            <form @submit.prevent="submitSlide" class="mt-4">
                <div class="grid gap-4 sm:grid-cols-[1fr_auto_auto] sm:items-end">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Pilih foto</label>
                        <input
                            type="file"
                            accept="image/*"
                            class="mt-1 block w-full text-sm text-slate-600 file:mr-3 file:rounded-md file:border-0 file:bg-brand-blue file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white"
                            @change="slideForm.foto = $event.target.files?.[0] ?? null"
                        />
                    </div>
                    <button
                        type="submit"
                        :disabled="!slideForm.foto || processing"
                        class="rounded-xl bg-brand-blue px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        Tambah Foto
                    </button>
                    <button
                        type="button"
                        :disabled="slideToDeleteId === '' || props.berandaSlides.length === 0"
                        @click="openDeleteSlide"
                        class="rounded-xl border border-rose-300 px-4 py-2.5 text-sm font-semibold text-rose-600 hover:bg-rose-50 disabled:cursor-not-allowed disabled:opacity-40"
                    >
                        Hapus Foto
                    </button>
                </div>
            </form>

            <p v-if="slideToDeleteId !== ''" class="mt-3 text-xs text-rose-600">
                Slide {{ props.berandaSlides.findIndex(s => String(s.id) === slideToDeleteId) + 1 }} dipilih — klik tombol "Hapus Foto" untuk konfirmasi.
            </p>
            <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="(slide, i) in props.berandaSlides"
                    :key="slide.id"
                    role="button"
                    tabindex="0"
                    :aria-pressed="slideToDeleteId === String(slide.id)"
                    @click="selectSlideForDeletion(slide.id)"
                    @keydown.enter.prevent="selectSlideForDeletion(slide.id)"
                    @keydown.space.prevent="selectSlideForDeletion(slide.id)"
                    :class="[
                        'flex cursor-pointer items-center gap-3 rounded-xl border p-2.5 transition-all',
                        slideToDeleteId === String(slide.id)
                            ? 'border-rose-500 ring-2 ring-rose-500/30 bg-rose-50'
                            : 'border-slate-200 hover:border-slate-300 focus-visible:ring-2 focus-visible:ring-slate-500'
                    ]"
                >
                    <img
                        :src="slide.path"
                        :alt="`Slide ${i + 1}`"
                        class="h-14 w-20 shrink-0 rounded-lg object-cover"
                    />
                    <p :class="['text-xs', slideToDeleteId === String(slide.id) ? 'text-rose-600 font-medium' : 'text-slate-500']">
                        Slide {{ i + 1 }}
                    </p>
                </div>
                <p v-if="props.berandaSlides.length === 0" class="col-span-full py-4 text-center text-sm text-slate-400">
                    Belum ada foto. Tambahkan di atas.
                </p>
            </div>

            <ConfirmModal
                :show="showDeleteSlideConfirm"
                title="Hapus Foto Carousel"
                :message="`Hapus slide ${props.berandaSlides.findIndex(s => String(s.id) === slideToDeleteId) + 1} dari carousel?`"
                confirm-label="Hapus"
                @confirm="confirmDeleteSlide"
                @close="closeDeleteSlide"
            />
        </div>
    </AppLayout>
</template>
