<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

const page = usePage();
const isAdmin = computed(() => page.props.auth?.user?.role === 'admin');

const query = ref('');
const loading = ref(false);
const errorMessage = ref('');
const resultsOpen = ref(false);
const rootEl = ref(null);

const results = ref({
    documents: [],
    users: [],
    racks: [],
    locations: [],
    activities: [],
});

let debounceTimer = null;
let abortController = null;

const hasTerm = computed(() => query.value.trim().length > 0);

const hasAnyResult = computed(() => {
    const r = results.value;
    return (
        r.documents.length +
        r.users.length +
        r.racks.length +
        r.locations.length +
        r.activities.length
    ) > 0;
});

const resetResults = () => {
    results.value = {
        documents: [],
        users: [],
        racks: [],
        locations: [],
        activities: [],
    };
};

const fetchResults = async (term) => {
    if (abortController) {
        abortController.abort();
    }

    if (!term) {
        resetResults();
        errorMessage.value = '';
        loading.value = false;
        return;
    }

    loading.value = true;
    errorMessage.value = '';
    abortController = new AbortController();

    try {
        const { data } = await axios.get('/search', {
            params: { q: term },
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            signal: abortController.signal,
        });

        if (!data || typeof data !== 'object' || Array.isArray(data)) {
            throw new Error('Respons pencarian tidak valid');
        }

        results.value = {
            documents: data.documents ?? [],
            users: data.users ?? [],
            racks: data.racks ?? [],
            locations: data.locations ?? [],
            activities: data.activities ?? [],
        };
        resultsOpen.value = true;
    } catch (error) {
        if (error?.code === 'ERR_CANCELED' || error?.name === 'CanceledError') {
            return;
        }
        resetResults();
        errorMessage.value = 'Gagal memuat hasil pencarian.';
        resultsOpen.value = true;
    } finally {
        loading.value = false;
    }
};

const scheduleSearch = (term) => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        fetchResults(term.trim());
    }, 300);
};

watch(query, (value) => {
    resultsOpen.value = true;
    scheduleSearch(value);
});

const onFocus = () => {
    resultsOpen.value = true;
    if (query.value.trim()) {
        scheduleSearch(query.value);
    }
};

const onSelect = () => {
    resultsOpen.value = false;
    query.value = '';
    resetResults();
};

const onDocumentPointerDown = (event) => {
    if (!rootEl.value?.contains(event.target)) {
        resultsOpen.value = false;
    }
};

const onKeydown = (event) => {
    if (event.key === 'Escape') {
        resultsOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('pointerdown', onDocumentPointerDown);
    window.addEventListener('keydown', onKeydown);
});

onUnmounted(() => {
    clearTimeout(debounceTimer);
    abortController?.abort();
    document.removeEventListener('pointerdown', onDocumentPointerDown);
    window.removeEventListener('keydown', onKeydown);
});
</script>

<template>
    <div ref="rootEl" class="relative min-w-0 w-full flex-1 sm:max-w-xl">
        <div
            class="flex w-full items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-1.5 shadow-sm focus-within:border-brand-blue/40 focus-within:ring-2 focus-within:ring-brand-blue/20"
        >
            <svg viewBox="0 0 24 24" class="h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8">
                <circle cx="10.5" cy="10.5" r="6.5" />
                <path stroke-linecap="round" d="M16 16l5 5" />
            </svg>
            <input
                v-model="query"
                type="search"
                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-0"
                placeholder="Cari dokumen, rak, user..."
                autocomplete="off"
                @focus="onFocus"
            >
            <span v-if="loading" class="shrink-0 text-[11px] text-slate-400">...</span>
        </div>

        <div
            v-if="resultsOpen"
            class="absolute right-0 z-50 mt-2 max-h-[min(70vh,28rem)] w-full min-w-[16rem] overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-xl"
        >
            <div v-if="loading" class="px-4 py-6 text-center text-sm text-slate-500">
                Mencari...
            </div>
            <div v-else-if="errorMessage" class="px-4 py-6 text-center text-sm text-rose-600">
                {{ errorMessage }}
            </div>
            <div v-else-if="!hasTerm" class="px-4 py-6 text-center text-sm text-slate-500">
                Ketik kata kunci untuk mencari.
            </div>
            <div v-else-if="!hasAnyResult" class="px-4 py-6 text-center text-sm text-slate-500">
                Tidak ada hasil untuk “{{ query.trim() }}”.
            </div>
            <div v-else class="divide-y divide-slate-100 py-1">
                <section v-if="results.documents.length" class="px-2 py-2">
                    <p class="px-2 pb-1 text-[10px] font-semibold uppercase tracking-wider text-slate-400">
                        Dokumen
                    </p>
                    <Link
                        v-for="item in results.documents"
                        :key="`doc-${item.id}`"
                        :href="route('arsip.show', item.id)"
                        class="block rounded-lg px-2 py-2 hover:bg-slate-50"
                        @click="onSelect"
                    >
                        <p class="truncate text-sm font-semibold text-brand-blue">
                            {{ item.kode_arsip }} · {{ item.perihal }}
                        </p>
                        <p class="mt-0.5 truncate text-xs text-slate-500">
                            {{ item.seksi?.nama_seksi || 'Tanpa seksi' }}
                            · {{ item.rak?.kode_rak || 'Belum ditempatkan' }}
                        </p>
                    </Link>
                </section>

                <section v-if="results.racks.length || results.locations.length" class="px-2 py-2">
                    <p class="px-2 pb-1 text-[10px] font-semibold uppercase tracking-wider text-slate-400">
                        Rak & lokasi
                    </p>
                    <component
                        :is="isAdmin ? Link : 'div'"
                        v-for="item in results.racks"
                        :key="`rak-${item.id}`"
                        v-bind="isAdmin ? { href: route('rak.index') } : {}"
                        class="block rounded-lg px-2 py-2"
                        :class="isAdmin ? 'hover:bg-slate-50' : ''"
                        @click="isAdmin ? onSelect() : undefined"
                    >
                        <p class="truncate text-sm font-semibold text-slate-800">
                            {{ item.kode_rak }} · {{ item.nama_rak }}
                        </p>
                        <p class="mt-0.5 truncate text-xs text-slate-500">
                            {{ item.lokasi_arsip?.nama_lokasi || item.lokasiArsip?.nama_lokasi || 'Tanpa lokasi' }}
                        </p>
                    </component>
                    <div
                        v-for="item in results.locations"
                        :key="`lok-${item.id}`"
                        class="rounded-lg px-2 py-2"
                    >
                        <p class="truncate text-sm font-semibold text-slate-800">
                            {{ item.kode_lokasi }} · {{ item.nama_lokasi }}
                        </p>
                        <p class="mt-0.5 truncate text-xs text-slate-500">
                            {{ item.gedung || 'Gedung belum diisi' }} {{ item.ruangan || '' }}
                        </p>
                    </div>
                </section>

                <section v-if="results.users.length" class="px-2 py-2">
                    <p class="px-2 pb-1 text-[10px] font-semibold uppercase tracking-wider text-slate-400">
                        User
                    </p>
                    <Link
                        v-for="item in results.users"
                        :key="`user-${item.id}`"
                        :href="route('users.index')"
                        class="block rounded-lg px-2 py-2 hover:bg-slate-50"
                        @click="onSelect"
                    >
                        <p class="truncate text-sm font-semibold text-slate-800">{{ item.name }}</p>
                        <p class="mt-0.5 truncate text-xs text-slate-500">
                            {{ item.email }} · {{ item.role }}
                        </p>
                    </Link>
                </section>

                <section v-if="results.activities.length" class="px-2 py-2">
                    <p class="px-2 pb-1 text-[10px] font-semibold uppercase tracking-wider text-slate-400">
                        Aktivitas
                    </p>
                    <component
                        :is="isAdmin ? Link : 'div'"
                        v-for="item in results.activities"
                        :key="`act-${item.id}`"
                        v-bind="isAdmin ? { href: route('activity.index') } : {}"
                        class="block rounded-lg px-2 py-2"
                        :class="isAdmin ? 'hover:bg-slate-50' : ''"
                        @click="isAdmin ? onSelect() : undefined"
                    >
                        <p class="line-clamp-2 text-sm font-semibold text-slate-800">{{ item.description }}</p>
                        <p class="mt-0.5 truncate text-xs text-slate-500">
                            {{ item.user?.name || 'Sistem' }}
                        </p>
                    </component>
                </section>
            </div>
        </div>
    </div>
</template>
