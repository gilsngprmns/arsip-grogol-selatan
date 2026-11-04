<script setup>
import ConfirmModal from '@/Components/ConfirmModal.vue';
import Modal from '@/Components/Modal.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, onUnmounted, ref, watch } from 'vue';

const props = defineProps({
    alurKerja: { type: Array, default: () => [] },
});

const page = usePage();
const errors = computed(() => page.props.errors ?? {});

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

const flashError = computed(() => page.props.flash?.error);
const flashErrorVisible = ref(false);
let flashErrorTimer = null;
watch(
    () => page.props.flash?.error,
    (v) => {
        clearTimeout(flashErrorTimer);
        if (v) {
            flashErrorVisible.value = true;
            flashErrorTimer = setTimeout(() => { flashErrorVisible.value = false; }, 5000);
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
    clearTimeout(flashErrorTimer);
    clearTimeout(flashDangerTimer);
});

const processing = ref(false);

const alurForm = ref({ nomor: '', judul: '', deskripsi: '', hasil: '' });
const resetAlurForm = () => { alurForm.value = { nomor: '', judul: '', deskripsi: '', hasil: '' }; };
const submitAlur = () => {
    processing.value = true;
    router.post(route('alur_kerja.store'), alurForm.value, {
        preserveScroll: true,
        onSuccess: () => { resetAlurForm(); },
        onFinish: () => { processing.value = false; },
    });
};

const showEditAlur = ref(false);
const editingAlur = ref(null);
const alurEditForm = ref({ nomor: '', judul: '', deskripsi: '', hasil: '' });
const openEditAlur = (item) => {
    editingAlur.value = item;
    alurEditForm.value = {
        nomor: item.nomor,
        judul: item.judul,
        deskripsi: item.deskripsi,
        hasil: item.hasil || '',
    };
    showEditAlur.value = true;
};
const closeEditAlur = () => {
    showEditAlur.value = false;
    editingAlur.value = null;
};
const submitEditAlur = () => {
    if (!editingAlur.value) return;
    processing.value = true;
    router.put(route('alur_kerja.update', editingAlur.value.id), alurEditForm.value, {
        preserveScroll: true,
        onSuccess: () => { closeEditAlur(); },
        onFinish: () => { processing.value = false; },
    });
};

const showDeleteConfirm = ref(false);
const pendingDeleteId = ref(null);
const promptDelete = (id) => {
    pendingDeleteId.value = id;
    showDeleteConfirm.value = true;
};
const closeDeleteConfirm = () => {
    showDeleteConfirm.value = false;
    pendingDeleteId.value = null;
};
const confirmDelete = () => {
    const id = pendingDeleteId.value;
    closeDeleteConfirm();
    router.delete(route('alur_kerja.destroy', id), { preserveScroll: true });
};

const scopedErrors = computed(() => (showEditAlur.value ? {} : errors.value));
const modalErrors = computed(() => (showEditAlur.value ? errors.value : {}));
</script>

<template>
    <Head title="Langkah Prosedur — Halaman Publik" />
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
                <div class="rounded-2xl bg-slate-100 p-4">
                    <div class="h-5 w-40 rounded bg-slate-200" />
                    <div class="mt-4 h-16 rounded-xl bg-slate-200" />
                </div>
            </div>
        </template>

        <template #header>
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Langkah Prosedur</h1>
                <p class="text-sm text-slate-500">Tahapan kerja yang ditampilkan di halaman Prosedur.</p>
            </div>
        </template>

        <div v-if="flashSuccess && flashVisible"
            class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800" role="status">
            {{ flashSuccess }}
        </div>
        <div v-if="flashError && flashErrorVisible"
            class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800" role="alert">
            {{ flashError }}
        </div>
        <div v-if="flashDanger && flashDangerVisible"
            class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800" role="alert">
            {{ flashDanger }}
        </div>

        <div v-reveal class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-800">Langkah Prosedur</h2>

            <form @submit.prevent="submitAlur" class="mt-4">
                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Nomor</label>
                        <input v-model="alurForm.nomor" type="text"
                            class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm" required />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Judul</label>
                        <input v-model="alurForm.judul" type="text"
                            class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm" required />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Hasil</label>
                        <input v-model="alurForm.hasil" type="text"
                            class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm" />
                    </div>
                </div>
                <div class="mt-4">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Deskripsi</label>
                    <textarea v-model="alurForm.deskripsi" rows="2"
                        class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm" required></textarea>
                </div>
                <p v-if="scopedErrors.nomor" class="mt-1 text-xs text-red-600">{{ scopedErrors.nomor }}</p>
                <p v-if="scopedErrors.judul" class="mt-1 text-xs text-red-600">{{ scopedErrors.judul }}</p>
                <div class="mt-4 flex justify-end">
                    <button type="submit" :disabled="processing"
                        class="rounded-xl bg-brand-blue px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50">
                        Tambah Langkah
                    </button>
                </div>
            </form>

            <div class="mt-4 space-y-2">
                <div v-for="item in props.alurKerja" :key="item.id"
                    class="flex items-start justify-between gap-3 rounded-xl border border-slate-100 bg-slate-50/60 px-3 py-2.5">
                    <div class="flex min-w-0 items-start gap-3">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-navy text-sm font-bold text-white">
                            {{ item.nomor }}
                        </span>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-slate-800">{{ item.judul }}</p>
                            <p class="text-xs text-slate-500">{{ item.deskripsi }}</p>
                            <p v-if="item.hasil" class="mt-0.5 text-xs text-slate-400">Hasil: {{ item.hasil }}</p>
                        </div>
                    </div>
                    <div class="flex shrink-0 items-center gap-1 pt-1">
                        <button type="button" @click="openEditAlur(item)"
                            class="rounded p-1 text-slate-400 hover:bg-slate-100 hover:text-brand-blue" title="Edit">
                            <AppIcon name="edit" :size="14" :stroke-width="2" class="h-3.5 w-3.5" decorative />
                        </button>
                        <button type="button" @click="promptDelete(item.id)"
                            class="rounded p-1 text-slate-400 hover:bg-rose-100 hover:text-rose-600" title="Hapus">
                            <AppIcon name="trash" :size="14" :stroke-width="2" class="h-3.5 w-3.5" decorative />
                        </button>
                    </div>
                </div>
                <p v-if="props.alurKerja.length === 0" class="py-4 text-center text-sm text-slate-400">
                    Belum ada langkah prosedur.
                </p>
            </div>
        </div>

        <!-- Modal Edit Prosedur -->
        <Modal :show="showEditAlur" max-width="lg" @close="closeEditAlur">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-lg font-bold text-slate-800">Perbarui Langkah Prosedur</h2>
            </div>
            <form class="px-6 py-5" @submit.prevent="submitEditAlur">
                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Nomor</label>
                        <input v-model="alurEditForm.nomor" type="text"
                            class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm" required />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-slate-700">Judul</label>
                        <input v-model="alurEditForm.judul" type="text"
                            class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm" required />
                    </div>
                </div>
                <div class="mt-4">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Deskripsi</label>
                    <textarea v-model="alurEditForm.deskripsi" rows="2"
                        class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm" required></textarea>
                </div>
                <div class="mt-4">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Hasil</label>
                    <input v-model="alurEditForm.hasil" type="text"
                        class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm" />
                </div>
                <p v-if="modalErrors.judul" class="mt-1 text-xs text-red-600">{{ modalErrors.judul }}</p>
                <div class="mt-4 flex justify-end gap-3 border-t border-slate-100 pt-4">
                    <button type="button" class="rounded-lg px-4 py-2 text-sm text-slate-500 hover:bg-slate-100" @click="closeEditAlur">Batal</button>
                    <button type="submit" class="rounded-xl bg-brand-blue px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </Modal>

        <!-- Confirm Hapus -->
        <ConfirmModal
            :show="showDeleteConfirm"
            title="Hapus Langkah Prosedur"
            message="Hapus langkah prosedur ini?"
            confirm-label="Hapus"
            @confirm="confirmDelete"
            @close="closeDeleteConfirm"
        />
    </AppLayout>
</template>
