<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import LokasiEditModal from '@/Components/LokasiEditModal.vue';
import RakEditModal from '@/Components/RakEditModal.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, onUnmounted, ref, watch } from 'vue';

const props = defineProps({
    rak: {
        type: Array,
        default: () => [],
    },
    lokasi: {
        type: Array,
        default: () => [],
    },
    lokasiList: {
        type: Array,
        default: () => [],
    },
    seksiOptions: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const errors = computed(() => page.props.errors ?? {});

// Flash message — success
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

// Flash message — error
const flashError = computed(() => page.props.flash?.error);
const flashVisibleError = ref(false);
let flashErrorTimer = null;

watch(
    () => page.props.flash?.error,
    (v) => {
        clearTimeout(flashErrorTimer);
        if (v) {
            flashVisibleError.value = true;
            flashErrorTimer = setTimeout(() => { flashVisibleError.value = false; }, 5000);
        }
    },
    { immediate: true },
);

// Flash message — danger (deletion)
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

// Rak form (create)
const emptyRakForm = () => ({
    lokasi_id: props.lokasiList[0]?.id ?? '',
    seksi_id: props.seksiOptions[0]?.id ?? '',
    kode_rak: '',
    nama_rak: '',
    nomor_rak: '',
    kapasitas: 100,
    deskripsi: '',
    is_active: true,
});

const rakForm = ref(emptyRakForm());

const submitRakForm = () => {
    router.post(route('rak.store'), rakForm.value, {
        preserveScroll: true,
        onSuccess: () => {
            rakForm.value = emptyRakForm();
        },
    });
};

// Rak edit modal
const showEditModal = ref(false);
const editingRak = ref(null);

const openEditRak = (rak) => {
    editingRak.value = rak;
    showEditModal.value = true;
};

const closeEditRak = () => {
    showEditModal.value = false;
    editingRak.value = null;
};

// Delete state
const showDeleteConfirm = ref(false);
const pendingDeleteId = ref(null);
const pendingDeleteType = ref(null); // 'rak' | 'lokasi'

const promptDeleteRak = (id) => {
    pendingDeleteId.value = id;
    pendingDeleteType.value = 'rak';
    showDeleteConfirm.value = true;
};

const promptDeleteLokasi = (id) => {
    pendingDeleteId.value = id;
    pendingDeleteType.value = 'lokasi';
    showDeleteConfirm.value = true;
};

const closeDeleteConfirm = () => {
    showDeleteConfirm.value = false;
    pendingDeleteId.value = null;
    pendingDeleteType.value = null;
};

const pct = (item) => {
    const count = item.dokumen_arsip_count ?? 0;
    const cap = item.kapasitas ?? 200;
    return cap > 0 ? Math.round((count / cap) * 100) : 0;
};

const confirmDelete = () => {
    const id = pendingDeleteId.value;
    const type = pendingDeleteType.value;
    closeDeleteConfirm();

    if (type === 'rak') {
        router.delete(route('rak.destroy', id), {
            preserveScroll: true,
            onError: () => { closeDeleteConfirm(); },
        });
    } else if (type === 'lokasi') {
        router.delete(route('lokasi.destroy', id), {
            preserveScroll: true,
            onError: () => { closeDeleteConfirm(); },
        });
    }
};

// Lokasi form (create)
const lokasiForm = ref({
    kode_lokasi: '',
    nama_lokasi: '',
    gedung: '',
    lantai: '',
    ruangan: '',
    deskripsi: '',
    is_active: true,
});

const submitLokasi = () => {
    router.post(route('lokasi.store'), lokasiForm.value, {
        preserveScroll: true,
        onSuccess: () => {
            lokasiForm.value = {
                kode_lokasi: '',
                nama_lokasi: '',
                gedung: '',
                lantai: '',
                ruangan: '',
                deskripsi: '',
                is_active: true,
            };
        },
    });
};

// Lokasi edit modal
const showLokasiModal = ref(false);
const editingLokasi = ref(null);

// Scoped errors: form inline hanya tampil error saat modal TERTUTUP,
// modal hanya tampil error saat modal TERBUKA (pola Users/Index.vue)
const inlineRakErrors = computed(() => {
    if (showEditModal.value) return {};
    return errors.value;
});
const inlineLokasiErrors = computed(() => {
    if (showLokasiModal.value) return {};
    return errors.value;
});

const openEditLokasi = (lokasi) => {
    editingLokasi.value = lokasi;
    showLokasiModal.value = true;
};

const closeEditLokasi = () => {
    showLokasiModal.value = false;
    editingLokasi.value = null;
};
</script>

<template>
    <Head title="Rak Arsip" />
    <AppLayout>
        <template #skeleton-header>
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <div class="h-7 w-40 rounded bg-slate-200" />
                    <div class="h-4 w-72 rounded bg-slate-200" />
                </div>
            </div>
        </template>

        <template #skeleton>
            <div class="space-y-6">
                <div class="rounded-2xl bg-slate-100 p-4">
                    <div class="h-5 w-48 rounded bg-slate-200" />
                    <div class="mt-1 h-3 w-72 rounded bg-slate-200" />
                    <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-5">
                        <div v-for="i in 5" :key="'sk-lok-' + i" class="h-10 rounded-xl bg-slate-200" />
                    </div>
                    <div class="mt-4 flex justify-end"><div class="h-10 w-28 rounded-xl bg-slate-200" /></div>
                    <div class="mt-4 h-48 rounded-xl bg-slate-200" />
                </div>
                <div class="rounded-2xl bg-slate-100 p-4">
                    <div class="h-5 w-40 rounded bg-slate-200" />
                    <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                        <div v-for="i in 3" :key="'sk-rak-' + i" class="h-10 rounded-xl bg-slate-200" />
                    </div>
                    <div class="mt-4 h-20 rounded-xl bg-slate-200" />
                    <div class="mt-4 flex justify-end"><div class="h-10 w-28 rounded-xl bg-slate-200" /></div>
                </div>
                <div class="rounded-2xl bg-slate-100 p-4">
                    <div class="h-5 w-40 rounded bg-slate-200" />
                    <div class="mt-4 space-y-2">
                        <div v-for="i in 5" :key="'sk-rak-tbl-' + i" class="h-12 rounded bg-slate-200" />
                    </div>
                </div>
            </div>
        </template>

        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Rak Arsip</h1>
                    <p class="text-sm text-slate-500">Kelola rak fisik per seksi — satu record = satu rak bernomor di ruang arsip.</p>
                </div>
            </div>
        </template>

        <!-- Flash success message -->
        <div
            v-if="flashSuccess && flashVisible"
            class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800"
        >
            {{ flashSuccess }}
        </div>

        <!-- Flash error message -->
        <div
            v-if="flashError && flashVisibleError"
            class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800"
        >
            {{ flashError }}
        </div>

        <!-- Flash danger message (deletion) -->
        <div
            v-if="flashDanger && flashDangerVisible"
            class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800"
        >
            {{ flashDanger }}
        </div>

        <!-- Master Lokasi Penyimpanan -->
        <div v-reveal class="mb-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-800">Master Lokasi Penyimpanan</h2>
            <p class="mt-1 text-sm text-slate-500">Tambahkan lokasi di sini sebelum digunakan pada data rak.</p>

            <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                <input v-model="lokasiForm.kode_lokasi" type="text" placeholder="Kode lokasi" class="rounded-xl border border-slate-300 px-3 py-2.5 text-sm" />
                <input v-model="lokasiForm.nama_lokasi" type="text" placeholder="Nama lokasi" class="rounded-xl border border-slate-300 px-3 py-2.5 text-sm" />
                <input v-model="lokasiForm.gedung" type="text" placeholder="Gedung" class="rounded-xl border border-slate-300 px-3 py-2.5 text-sm" />
                <input v-model="lokasiForm.lantai" type="text" placeholder="Lantai" class="rounded-xl border border-slate-300 px-3 py-2.5 text-sm" />
                <input v-model="lokasiForm.ruangan" type="text" placeholder="Ruangan" class="rounded-xl border border-slate-300 px-3 py-2.5 text-sm" />
            </div>
            <div class="mt-4 flex justify-end">
                <button type="button" @click="submitLokasi" class="rounded-xl bg-brand-blue px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                    Simpan Lokasi
                </button>
            </div>

            <!-- Tabel Lokasi -->
            <div class="mt-4 rounded-xl border border-slate-200 overflow-hidden">
                <table class="min-w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50 text-slate-500">
                            <th class="px-3 py-2 font-medium">Nama Lokasi</th>
                            <th class="px-3 py-2 font-medium">Kode</th>
                            <th class="px-3 py-2 font-medium">Gedung</th>
                            <th class="px-3 py-2 font-medium">Lantai</th>
                            <th class="px-3 py-2 font-medium">Ruangan</th>
                            <th class="px-3 py-2 font-medium text-center">Rak</th>
                            <th class="px-3 py-2 font-medium text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in props.lokasi" :key="item.id" class="border-b border-slate-100 hover:bg-slate-50">
                            <td class="px-3 py-2 font-medium text-slate-800">{{ item.nama_lokasi }}</td>
                            <td class="px-3 py-2 text-slate-500">{{ item.kode_lokasi }}</td>
                            <td class="px-3 py-2 text-slate-500">{{ item.gedung || '—' }}</td>
                            <td class="px-3 py-2 text-slate-500">{{ item.lantai || '—' }}</td>
                            <td class="px-3 py-2 text-slate-500">{{ item.ruangan || '—' }}</td>
                            <td class="px-3 py-2 text-center">
                                <span class="inline-flex items-center justify-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-700">
                                    {{ item.rak_arsip_count ?? 0 }}
                                </span>
                            </td>
                            <td class="px-3 py-2">
                                <div class="flex items-center justify-center gap-1">
                                    <button
                                        type="button"
                                        class="rounded p-1 text-slate-400 hover:bg-slate-200 hover:text-slate-600"
                                        title="Edit lokasi"
                                        @click="openEditLokasi(item)"
                                    >
                                        <AppIcon name="edit" :size="14" :stroke-width="2" class="h-3.5 w-3.5" decorative />
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded p-1 text-slate-400 hover:bg-rose-100 hover:text-rose-600"
                                        title="Hapus lokasi"
                                        @click="promptDeleteLokasi(item.id)"
                                    >
                                        <AppIcon name="trash" :size="14" :stroke-width="2" class="h-3.5 w-3.5" decorative />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="props.lokasi.length === 0">
                            <td colspan="7" class="px-3 py-6 text-center text-sm text-slate-400">
                                Belum ada lokasi penyimpanan. Tambahkan di atas.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p v-if="inlineLokasiErrors.kode_lokasi" class="mt-2 text-xs text-red-600">{{ inlineLokasiErrors.kode_lokasi }}</p>
            <p v-if="inlineLokasiErrors.nama_lokasi" class="mt-2 text-xs text-red-600">{{ inlineLokasiErrors.nama_lokasi }}</p>
        </div>

        <!-- Tambah Rak Baru -->
        <div v-reveal :style="{ transitionDelay: '100ms' }" class="mb-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-800">Tambah Rak Baru</h2>
            <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Lokasi</label>
                    <select v-model="rakForm.lokasi_id" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700">
                        <option v-for="lokasi in props.lokasiList" :key="lokasi.id" :value="lokasi.id">
                            {{ lokasi.nama_lokasi }} ({{ lokasi.kode_lokasi }})
                        </option>
                    </select>
                    <p v-if="inlineRakErrors.lokasi_id" class="mt-1 text-xs text-red-600">{{ inlineRakErrors.lokasi_id }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Seksi pemilik rak</label>
                    <select v-model="rakForm.seksi_id" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700">
                        <option v-for="seksi in props.seksiOptions" :key="seksi.id" :value="seksi.id">
                            {{ seksi.nama_seksi }}
                        </option>
                    </select>
                    <p v-if="inlineRakErrors.seksi_id" class="mt-1 text-xs text-red-600">{{ inlineRakErrors.seksi_id }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Kode Rak</label>
                    <input v-model="rakForm.kode_rak" type="text" placeholder="Contoh: RAK-001" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700" />
                    <p v-if="inlineRakErrors.kode_rak" class="mt-1 text-xs text-red-600">{{ inlineRakErrors.kode_rak }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Nama Rak</label>
                    <input v-model="rakForm.nama_rak" type="text" placeholder="Contoh: Rak Arsip No. 1" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700" />
                    <p v-if="inlineRakErrors.nama_rak" class="mt-1 text-xs text-red-600">{{ inlineRakErrors.nama_rak }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Nomor Rak</label>
                    <input v-model="rakForm.nomor_rak" type="text" placeholder="mis. 1" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700" />
                    <p v-if="inlineRakErrors.nomor_rak" class="mt-1 text-xs text-red-600">{{ inlineRakErrors.nomor_rak }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Kapasitas</label>
                    <input v-model="rakForm.kapasitas" type="number" min="1" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700" placeholder="Contoh: 50" />
                    <p v-if="inlineRakErrors.kapasitas" class="mt-1 text-xs text-red-600">{{ inlineRakErrors.kapasitas }}</p>
                </div>

                <div class="flex items-end">
                    <label class="inline-flex items-center gap-2 rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700">
                        <input v-model="rakForm.is_active" type="checkbox" class="h-4 w-4" />
                        Aktif
                    </label>
                </div>
            </div>

            <div class="mt-4">
                <label class="mb-1 block text-sm font-medium text-slate-700">Deskripsi</label>
                <textarea v-model="rakForm.deskripsi" rows="3" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700" placeholder="Tambahkan catatan mengenai rak (opsional)..."></textarea>
                <p v-if="inlineRakErrors.deskripsi" class="mt-1 text-xs text-red-600">{{ inlineRakErrors.deskripsi }}</p>
            </div>

            <div class="mt-4 flex justify-end">
                <button type="button" @click="submitRakForm" class="rounded-xl bg-brand-blue px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                    Simpan Rak
                </button>
            </div>
        </div>

        <!-- Tabel Rak -->
        <div v-reveal :style="{ transitionDelay: '200ms' }" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <h2 class="mb-3 text-lg font-semibold text-slate-800">Daftar Rak Arsip</h2>

            <!-- Mobile / tablet: card list (< lg) -->
            <div class="lg:hidden">
                <ul class="divide-y divide-slate-100">
                    <li
                        v-for="item in props.rak"
                        :key="`card-${item.id}`"
                        class="px-4 py-3"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-semibold text-slate-800">{{ item.kode_rak }}</span>
                                    <span class="text-sm text-slate-600 truncate">{{ item.nama_rak }}</span>
                                </div>
                                <p class="mt-0.5 text-xs text-slate-500">
                                    No. {{ item.nomor_rak || '—' }} · {{ item.seksi?.nama_seksi || '—' }} · {{ item.lokasi_arsip?.nama_lokasi || '—' }}
                                </p>
                                <div class="mt-1.5">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="font-medium text-slate-700">
                                            {{ item.dokumen_arsip_count ?? 0 }} / {{ item.kapasitas ?? 200 }}
                                        </span>
                                        <span
                                            class="font-semibold"
                                            :class="item.is_active
                                                ? (pct(item) >= 100 ? 'text-rose-600' : pct(item) >= 80 ? 'text-amber-600' : 'text-emerald-600')
                                                : 'text-slate-400'"
                                        >{{ pct(item) }}%</span>
                                    </div>
                                    <div class="mt-1 h-1.5 rounded-full bg-slate-200">
                                        <div
                                            class="h-1.5 rounded-full transition-all duration-300"
                                            :class="{
                                                'bg-emerald-500': pct(item) < 80,
                                                'bg-amber-500': pct(item) >= 80 && pct(item) < 100,
                                                'bg-rose-500': pct(item) >= 100
                                            }"
                                            :style="{ width: Math.min(pct(item), 100) + '%' }"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                <span
                                    class="rounded-full px-2 py-0.5 text-xs font-semibold"
                                    :class="item.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600'"
                                >{{ item.is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                <button
                                    type="button"
                                    class="rounded p-1.5 text-slate-400 hover:bg-slate-100 hover:text-brand-blue"
                                    title="Edit rak"
                                    @click="openEditRak(item)"
                                >
                                    <AppIcon name="edit" :size="15" :stroke-width="2" class="h-3.5 w-3.5" decorative />
                                </button>
                                <button
                                    type="button"
                                    class="rounded p-1.5 text-slate-400 hover:bg-rose-100 hover:text-rose-600"
                                    title="Hapus rak"
                                    @click="promptDeleteRak(item.id)"
                                >
                                    <AppIcon name="trash" :size="15" :stroke-width="2" class="h-3.5 w-3.5" decorative />
                                </button>
                            </div>
                        </div>
                    </li>
                    <li v-if="props.rak.length === 0" class="px-4 py-8 text-center text-sm text-slate-500">
                        Belum ada rak arsip. Tambahkan rak baru di atas.
                    </li>
                </ul>
            </div>

            <!-- Desktop: table (≥ lg) -->
            <div class="hidden overflow-x-auto lg:block">
                <table class="min-w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-600">
                            <th class="px-3 py-3 font-semibold">Kode Rak</th>
                            <th class="px-3 py-3 font-semibold">Nama Rak</th>
                            <th class="px-3 py-3 font-semibold">No.</th>
                            <th class="px-3 py-3 font-semibold">Seksi</th>
                            <th class="px-3 py-3 font-semibold">Lokasi</th>
                            <th class="px-3 py-3 font-semibold w-48">Kapasitas</th>
                            <th class="px-3 py-3 font-semibold">Status</th>
                            <th class="px-3 py-3 font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in props.rak" :key="item.id" class="border-b border-slate-100">
                            <td class="px-3 py-3 font-medium text-slate-800">{{ item.kode_rak }}</td>
                            <td class="px-3 py-3">{{ item.nama_rak }}</td>
                            <td class="px-3 py-3">{{ item.nomor_rak || '—' }}</td>
                            <td class="px-3 py-3">{{ item.seksi?.nama_seksi || '—' }}</td>
                            <td class="px-3 py-3">{{ item.lokasi_arsip?.nama_lokasi || '—' }}</td>
                            <td class="px-3 py-3">
                                <div class="flex flex-col gap-1">
                                    <span class="text-sm font-medium text-slate-700">{{ item.dokumen_arsip_count ?? 0 }} / {{ item.kapasitas ?? 200 }}</span>
                                    <div class="w-full h-1.5 rounded-full bg-slate-200">
                                        <div
                                            class="h-1.5 rounded-full transition-all duration-300"
                                            :class="{
                                                'bg-emerald-500': pct(item) < 80,
                                                'bg-amber-500': pct(item) >= 80 && pct(item) < 100,
                                                'bg-rose-500': pct(item) >= 100
                                            }"
                                            :style="{ width: Math.min(pct(item), 100) + '%' }"
                                        ></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-3">
                                <span v-if="item.is_active" class="rounded-full bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-700">Aktif</span>
                                <span v-else class="rounded-full bg-slate-200 px-2 py-1 text-xs font-semibold text-slate-600">Nonaktif</span>
                            </td>
                            <td class="px-3 py-3">
                                <div class="flex items-center gap-2">
                                    <button
                                        type="button"
                                        class="rounded p-1 text-slate-400 hover:bg-slate-100 hover:text-brand-blue"
                                        title="Edit rak"
                                        @click="openEditRak(item)"
                                    >
                                        <AppIcon name="edit" :size="16" :stroke-width="2" class="h-4 w-4" decorative />
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded p-1 text-slate-400 hover:bg-rose-100 hover:text-rose-600"
                                        title="Hapus rak"
                                        @click="promptDeleteRak(item.id)"
                                    >
                                        <AppIcon name="trash" :size="16" :stroke-width="2" class="h-4 w-4" decorative />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="props.rak.length === 0">
                            <td colspan="9" class="px-3 py-8 text-center text-sm text-slate-500">
                                Belum ada rak arsip. Tambahkan rak baru di atas.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Rak Edit Modal -->
        <RakEditModal
            :show="showEditModal"
            mode="edit"
            :initial-data="editingRak"
            :lokasi-list="props.lokasi"
            :seksi-list="props.seksiOptions"
            @close="closeEditRak"
        />

        <!-- Lokasi Edit Modal -->
        <LokasiEditModal
            :show="showLokasiModal"
            mode="edit"
            :initial-data="editingLokasi"
            @close="closeEditLokasi"
        />

        <!-- Delete Confirmation Modal -->
        <ConfirmModal
            :show="showDeleteConfirm"
            title="Hapus Data"
            :message="pendingDeleteType === 'rak' ? 'Hapus rak ini? Dokumen arsip di dalamnya akan tetap tersimpan.' : 'Hapus lokasi ini? Hanya dapat dihapus jika tidak ada rak di dalamnya.'"
            confirm-label="Hapus"
            @confirm="confirmDelete"
            @close="closeDeleteConfirm"
        />
    </AppLayout>
</template>
