<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    activities: {
        type: Object,
        default: () => ({ data: [] }),
    },
    filters: {
        type: Object,
        default: () => ({ tanggal_dari: '', tanggal_sampai: '', pengguna: '', aksi: '', q: '' }),
    },
    users: {
        type: Array,
        default: () => [],
    },
    actions: {
        type: Array,
        default: () => [],
    },
});

// --- Format waktu: "21 Okt 2026, 18.52.10" (zona Asia/Jakarta) ---
const formatDateTime = (value) =>
    value
        ? new Intl.DateTimeFormat('id-ID', {
              day: '2-digit',
              month: 'short',
              year: 'numeric',
              hour: '2-digit',
              minute: '2-digit',
              second: '2-digit',
              hour12: false,
              timeZone: 'Asia/Jakarta',
          }).format(new Date(value))
        : '—';

const actionMap = {
    'users.store': 'Tambah pengguna',
    'users.update': 'Perbarui pengguna',
    'users.destroy': 'Nonaktifkan pengguna',
    'users.activate': 'Aktifkan pengguna',
    'users.password': 'Reset kata sandi',
    'users.force-delete': 'Hapus permanen pengguna',
    'arsip.store': 'Tambah dokumen arsip',
    'arsip.update': 'Perbarui dokumen arsip',
    'arsip.destroy': 'Hapus dokumen arsip',
    'arsip.force-delete': 'Hapus permanen dokumen arsip',
    'arsip.pinjam': 'Catat peminjaman',
    'arsip.kembalikan': 'Catat pengembalian',
    'arsip.settersedia': 'Set arsip tersedia',
    'arsip.scan': 'Pindai QR arsip',
    'arsip.cadangkan': 'Cadangkan basis data arsip',
    'backup.export': 'Unduh cadangan',
    'backup.import': 'Impor cadangan',
    'backup.run': 'Buat cadangan (bundel)',
    'backup.files': 'Unduh cadangan tersimpan',
    'profile.update': 'Perbarui profil',
    'profile.destroy': 'Hapus akun',
    'rak.store': 'Tambah rak',
    'rak.update': 'Perbarui rak',
    'rak.destroy': 'Nonaktifkan rak',
    'lokasi.store': 'Tambah lokasi',
    'lokasi.update': 'Perbarui lokasi',
    'lokasi.destroy': 'Nonaktifkan lokasi',
};

const actionLabel = (activity) => {
    if (actionMap[activity.route_name]) return actionMap[activity.route_name];
    const desc = activity.description;
    const m = desc?.match(/^(?:GET|POST|PUT|PATCH|DELETE)\s+(\S+)/);
    if (m) {
        const routeGuess = m[1].replace(/\d+/g, '{id}');
        const byPath = {
            'users/{id}/activate': 'Aktifkan pengguna',
            'users/{id}/password': 'Reset kata sandi',
        };
        if (byPath[routeGuess]) return byPath[routeGuess];
    }
    return desc || 'Aksi tidak dikenal';
};

// Label opsion dropdown aksi (nilainya masih raw action/route name)
const actionOptionLabel = (value) => actionMap[value] ?? value;

// --- Badge kolom Aksi (kelas Tailwind yang sudah dipakai ArsipTable) ---
const actionBadge = (activity) => {
    const key = activity.route_name || activity.action || '';
    const suffix = key.split('.').pop();
    if (suffix === 'store' || key === 'arsip.kembalikan' || key === 'arsip.settersedia' || key === 'users.activate') {
        return 'bg-emerald-50 text-emerald-800 ring-emerald-300'; // tambah / pengembalian
    }
    if (suffix === 'update' || key === 'arsip.pinjam' || key === 'arsip.scan' || key === 'users.password') {
        return 'bg-amber-50 text-amber-900 ring-amber-300'; // ubah / peminjaman
    }
    if (suffix === 'destroy' || suffix === 'force-delete') {
        return 'bg-rose-50 text-rose-800 ring-rose-300'; // hapus / nonaktif
    }
    return 'bg-slate-100 text-slate-700 ring-slate-200'; // sistem / backup / lainnya
};

// --- Target di kolom Keterangan ---
const targetPrefix = { dokumen: 'Dokumen', user: 'User', rak: 'Rak', lokasi: 'Lokasi' };

// Baris hapus/permanen: objek kemungkinan sudah tidak ada → jangan di-link
const targetGone = (activity) =>
    activity.route_name === 'arsip.destroy' ||
    activity.route_name === 'arsip.force-delete' ||
    activity.route_name === 'users.force-delete';

const targetLine = (activity) => {
    if (!activity.target_type) return null;
    return {
        prefix: targetPrefix[activity.target_type] ?? activity.target_type,
        label: activity.target_label || '—',
        // Dokumen → link halaman detail (kecuali baris hapus); user/rak/lokasi tidak
        // punya halaman detail sendiri → teks biasa.
        href:
            activity.target_type === 'dokumen' && activity.target_id && !targetGone(activity)
                ? route('arsip.show', activity.target_id)
                : null,
    };
};

const isRawDescription = (activity) =>
    !actionMap[activity.route_name] && /^(?:GET|POST|PUT|PATCH|DELETE)\s/.test(activity.description || '');

const descriptionLabel = (activity) => (isRawDescription(activity) ? actionLabel(activity) : activity.description);

const FIELD_LABELS = {
    is_active: 'Status akun',
    name: 'Nama',
    email: 'Email',
    role: 'Peran',
    nomor_surat: 'Nomor surat',
    judul: 'Judul',
    status_arsip: 'Status arsip',
    batas_pengembalian: 'Batas pengembalian',
};

const formatValue = (val, key) => {
    if (val === null || val === undefined) return '—';
    // DB returns is_active as 0/1 (int or string), not JS boolean
    if (key === 'is_active') {
        const active = val === true || val === 1 || val === '1';
        return active ? 'Aktif' : 'Nonaktif';
    }
    // Snapshot datetime ISO (mis. batas pengembalian) → format yang sama dengan kolom Waktu
    if (typeof val === 'string' && /^\d{4}-\d{2}-\d{2}T/.test(val)) {
        const parsed = new Date(val);
        if (!Number.isNaN(parsed.getTime())) return formatDateTime(val);
    }
    if (typeof val === 'boolean') return val ? 'Ya' : 'Tidak';
    return String(val);
};

const changedDetails = (activity) => {
    const oldValues = activity.old_values || {};
    const newValues = activity.new_values || {};

    return Object.keys(newValues)
        .filter((key) => JSON.stringify(oldValues[key]) !== JSON.stringify(newValues[key]))
        .slice(0, 4)
        .map((key) => ({
            label: FIELD_LABELS[key] ?? key.replaceAll('_', ' '),
            old: formatValue(oldValues[key], key),
            new: formatValue(newValues[key], key),
        }));
};

// --- Filter (state lokal di-seed dari query string server) ---
const localFilters = ref({ ...props.filters });
watch(
    () => props.filters,
    (f) => {
        localFilters.value = { ...f };
    },
);

const hasActiveFilters = computed(() =>
    ['tanggal_dari', 'tanggal_sampai', 'pengguna', 'aksi', 'q'].some((k) => localFilters.value[k]),
);

const applyFilters = () => {
    router.get(
        route('activity.index'),
        {
            tanggal_dari: localFilters.value.tanggal_dari || undefined,
            tanggal_sampai: localFilters.value.tanggal_sampai || undefined,
            pengguna: localFilters.value.pengguna || undefined,
            aksi: localFilters.value.aksi || undefined,
            q: localFilters.value.q || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: ['activities', 'filters', 'users', 'actions'],
        },
    );
};

let searchTimer = null;
const onSearchInput = () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(applyFilters, 350);
};

const resetFilters = () => {
    localFilters.value = { tanggal_dari: '', tanggal_sampai: '', pengguna: '', aksi: '', q: '' };
    applyFilters();
};

// Pintasan tanggal — hari kalender zona Asia/Jakarta (format YYYY-MM-DD)
const jakartaDay = (offset) =>
    new Intl.DateTimeFormat('en-CA', {
        timeZone: 'Asia/Jakarta',
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
    }).format(new Date(Date.now() + offset * 86400000));

const applyShortcut = (days) => {
    localFilters.value.tanggal_sampai = jakartaDay(0);
    localFilters.value.tanggal_dari = jakartaDay(-(days - 1));
    applyFilters();
};

// --- Unduh CSV mengikuti filter aktif ---
const csvUrl = computed(() => {
    const params = new URLSearchParams();
    for (const [key, value] of Object.entries(localFilters.value)) {
        if (value) params.set(key, value);
    }
    const qs = params.toString();
    return route('reports.activity') + (qs ? `?${qs}` : '');
});

// Kelas kontrol filter — disamakan dengan pola halaman Users/Index.vue
const inputClass = 'rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700';
const filterSelectClass = 'min-w-[9.5rem] rounded-xl border border-slate-300 py-2 pl-3 pr-10 text-sm text-slate-700';
const buttonClass = 'rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50';
const shortcutClass = 'rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50';
</script>

<template>
    <Head title="Log Activity" />
    <AppLayout>
        <template #skeleton-header>
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-wrap items-center gap-3">
                    <div class="h-7 w-40 rounded bg-slate-200" />
                    <div class="h-4 w-64 rounded bg-slate-200" />
                </div>
                <div class="h-10 w-32 rounded-xl bg-slate-200" />
            </div>
        </template>

        <template #skeleton>
            <div class="space-y-4">
                <div class="flex flex-wrap gap-2 rounded-2xl bg-slate-100 p-3">
                    <div class="h-10 w-28 rounded-xl bg-slate-200" />
                    <div class="h-7 w-16 rounded-lg bg-slate-200" />
                    <div class="h-7 w-16 rounded-lg bg-slate-200" />
                    <div class="h-7 w-16 rounded-lg bg-slate-200" />
                    <div class="h-10 w-32 rounded-xl bg-slate-200" />
                    <div class="h-10 w-32 rounded-xl bg-slate-200" />
                    <div class="h-10 flex-1 rounded-xl bg-slate-200" />
                </div>
                <div class="h-64 rounded-2xl bg-slate-100">
                    <div class="h-9 border-b border-slate-100" />
                    <div v-for="i in 6" :key="'sk-act-' + i" class="h-12 border-b border-slate-100" />
                </div>
            </div>
        </template>

        <template #header>
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl font-bold text-slate-900">Log Activity</h1>
                    <p class="text-sm text-slate-500">Riwayat aktivitas pengguna dan sistem.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a
                        :href="csvUrl"
                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 shadow-sm hover:bg-slate-50 sm:flex-none"
                        title="Unduh log aktivitas sesuai filter aktif"
                    >
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M12 4v12m0 0l-4-4m4 4l4-4" />
                        </svg>
                        Unduh CSV
                    </a>
                </div>
            </div>
        </template>

        <!-- Baris filter (gaya Users/Index.vue) -->
        <div class="mb-4 flex flex-col gap-2.5 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm md:flex-row md:flex-wrap md:items-center md:gap-3 md:p-4">
            <div class="flex items-center gap-1.5">
                <input v-model="localFilters.tanggal_dari" type="date" :class="inputClass" title="Dari" @change="applyFilters" />
                <span class="text-xs text-slate-400">–</span>
                <input v-model="localFilters.tanggal_sampai" type="date" :class="inputClass" title="Sampai" @change="applyFilters" />
            </div>
            <div class="flex items-center gap-1.5">
                <button type="button" :class="shortcutClass" @click="applyShortcut(1)">Hari ini</button>
                <button type="button" :class="shortcutClass" @click="applyShortcut(7)">7 hari</button>
                <button type="button" :class="shortcutClass" @click="applyShortcut(30)">30 hari</button>
            </div>
            <select v-model="localFilters.pengguna" :class="filterSelectClass" @change="applyFilters">
                <option value="">Semua pengguna</option>
                <option value="sistem">Sistem</option>
                <option v-for="u in users" :key="u.id" :value="String(u.id)">{{ u.name }}</option>
            </select>
            <select v-model="localFilters.aksi" :class="filterSelectClass" @change="applyFilters">
                <option value="">Semua aksi</option>
                <option v-for="a in actions" :key="a" :value="a">{{ actionOptionLabel(a) }}</option>
            </select>
            <input
                v-model="localFilters.q"
                type="search"
                placeholder="Cari keterangan atau target"
                :class="inputClass + ' md:min-w-[12rem] md:flex-1'"
                @input="onSearchInput"
                @keyup.enter="applyFilters"
            >
            <button type="button" :class="buttonClass" @click="resetFilters">Reset</button>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-600">
                            <th class="px-3 py-3 font-semibold">Waktu</th>
                            <th class="px-3 py-3 font-semibold">Pengguna</th>
                            <th class="px-3 py-3 font-semibold">Aksi</th>
                            <th class="px-3 py-3 font-semibold">Keterangan</th>
                            <th class="px-3 py-3 font-semibold">IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="activity in activities.data" :key="activity.id" class="border-b border-slate-100">
                            <td class="whitespace-nowrap px-3 py-3 text-slate-600">{{ formatDateTime(activity.created_at) }}</td>
                            <td class="px-3 py-3">
                                <div class="font-medium text-slate-800">{{ activity.user?.name || 'Sistem' }}</div>
                                <div class="text-xs uppercase text-slate-400">{{ activity.user?.role || '' }}</div>
                            </td>
                            <td class="px-3 py-3">
                                <span
                                    class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold ring-1 ring-inset"
                                    :class="actionBadge(activity)"
                                >{{ actionLabel(activity) }}</span>
                            </td>
                            <td class="px-3 py-3">
                                <div v-if="targetLine(activity)" class="text-xs">
                                    <template v-if="targetLine(activity).href">
                                        <Link
                                            :href="targetLine(activity).href"
                                            preserve-scroll
                                            class="font-medium text-brand-blue hover:underline"
                                        >{{ targetLine(activity).prefix }}: {{ targetLine(activity).label }}</Link>
                                    </template>
                                    <template v-else>
                                        <span class="font-medium text-slate-700">{{ targetLine(activity).prefix }}:
                                            {{ targetLine(activity).label }}</span>
                                    </template>
                                </div>
                                <div class="text-xs text-slate-400" v-else>Target: —</div>
                                <div class="mt-0.5">{{ descriptionLabel(activity) }}</div>
                                <div v-for="d in changedDetails(activity)" :key="d.label" class="mt-0.5 text-xs text-slate-500">
                                    {{ d.label }}: {{ d.old }} → {{ d.new }}
                                </div>
                            </td>
                            <td class="px-3 py-3 text-slate-500">{{ activity.ip_address || '—' }}</td>
                        </tr>
                        <tr v-if="!activities.data?.length">
                            <td colspan="5" class="px-3 py-10 text-center text-sm leading-7 text-slate-500">
                                <template v-if="hasActiveFilters">
                                    Tidak ada aktivitas yang cocok dengan filter.
                                    <button type="button" class="mt-1 block text-xs font-semibold text-brand-blue hover:underline" @click="resetFilters">
                                        Reset filter
                                    </button>
                                </template>
                                <template v-else>Belum ada riwayat kegiatan. Aktivitas akan muncul setelah petugas mengelola arsip.</template>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Paginasi (filter ikut lewat query string server) -->
            <div
                v-if="activities.last_page > 1"
                class="mt-3 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 px-4 pt-3 sm:px-5"
            >
                <p class="text-xs text-slate-500">
                    Halaman {{ activities.current_page }} dari {{ activities.last_page }}
                    <span class="text-slate-400">· {{ activities.total }} aktivitas</span>
                </p>
                <div class="flex flex-wrap gap-1">
                    <template v-for="link in activities.links" :key="`${link.label}-${link.url}`">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            preserve-scroll
                            v-html="link.label"
                            class="rounded-md px-2.5 py-1 text-xs"
                            :class="link.active ? 'bg-brand-blue text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        />
                        <span v-else v-html="link.label" class="rounded-md px-2.5 py-1 text-xs text-slate-400"></span>
                    </template>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
