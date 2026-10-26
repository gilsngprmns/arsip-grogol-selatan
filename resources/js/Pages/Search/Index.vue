<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    term: { type: String, default: '' },
    documents: { type: Array, default: () => [] },
    users: { type: Array, default: () => [] },
    racks: { type: Array, default: () => [] },
    locations: { type: Array, default: () => [] },
    activities: { type: Array, default: () => [] },
});

const query = ref(props.term);
const submit = () => router.get(route('search'), { q: query.value }, { preserveState: true, replace: true });
</script>

<template>
    <Head title="Pencarian Global" />
    <AppLayout>
        <template #skeleton-header>
            <div class="space-y-1">
                <div class="h-7 w-48 rounded bg-slate-200" />
                <div class="h-4 w-64 rounded bg-slate-200" />
            </div>
        </template>

        <template #skeleton>
            <div class="space-y-4">
                <div class="flex gap-3">
                    <div class="h-11 flex-1 rounded-xl bg-slate-200" />
                    <div class="h-11 w-20 rounded-xl bg-slate-200" />
                </div>
                <div class="grid gap-4 xl:grid-cols-2">
                    <div v-for="i in 4" :key="'sk-srch-' + i" class="h-40 rounded-2xl bg-slate-100">
                        <div class="p-5">
                            <div class="h-4 w-32 rounded bg-slate-200" />
                            <div class="mt-4 space-y-2">
                                <div v-for="j in 3" :key="'sk-item-' + i + '-' + j" class="h-10 rounded bg-slate-200" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <template #header>
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Pencarian Global</h1>
                <p class="text-sm text-slate-500">Cari dokumen, user, rak, lokasi, dan aktivitas dalam satu tempat.</p>
            </div>
        </template>

        <form class="mb-6 flex gap-3" @submit.prevent="submit">
            <input v-model="query" type="search" autofocus placeholder="Masukkan kode, nama, email, rak, atau kata kunci" class="min-w-0 flex-1 rounded-xl border border-slate-300 px-4 py-3 text-sm shadow-sm" />
            <button type="submit" class="rounded-xl bg-brand-blue px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">Cari</button>
        </form>

        <div v-if="!props.term" class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-sm text-slate-500">Masukkan kata kunci untuk memulai pencarian.</div>

        <div v-else class="grid gap-6 xl:grid-cols-2">
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="font-semibold text-slate-800">Dokumen ({{ props.documents.length }})</h2>
                <div class="mt-3 divide-y divide-slate-100">
                    <Link v-for="item in props.documents" :key="item.id" :href="route('arsip.show', item.id)" class="block py-3 hover:bg-slate-50">
                        <p class="font-semibold text-brand-blue">{{ item.kode_arsip }} · {{ item.perihal }}</p>
                        <p class="mt-1 text-xs text-slate-500">{{ item.seksi?.nama_seksi || 'Tanpa seksi' }} · {{ item.rak?.kode_rak || 'Belum ditempatkan' }}</p>
                    </Link>
                    <p v-if="!props.documents.length" class="py-6 text-sm text-slate-500">Tidak ditemukan.</p>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="font-semibold text-slate-800">User ({{ props.users.length }})</h2>
                <div class="mt-3 divide-y divide-slate-100">
                    <div v-for="item in props.users" :key="item.id" class="py-3"><p class="font-semibold text-slate-800">{{ item.name }}</p><p class="mt-1 text-xs text-slate-500">{{ item.email }} · {{ item.role }} · {{ item.is_active ? 'Aktif' : 'Nonaktif' }}</p></div>
                    <p v-if="!props.users.length" class="py-6 text-sm text-slate-500">Tidak ditemukan atau hanya dapat dilihat admin.</p>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="font-semibold text-slate-800">Rak dan Lokasi</h2>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <div v-for="item in props.racks" :key="`rak-${item.id}`" class="rounded-xl bg-slate-50 p-3 text-sm"><p class="font-semibold text-slate-800">{{ item.kode_rak }} · {{ item.nama_rak }}</p><p class="mt-1 text-xs text-slate-500">{{ item.lokasi_arsip?.nama_lokasi || 'Tanpa lokasi' }}</p></div>
                    <div v-for="item in props.locations" :key="`lokasi-${item.id}`" class="rounded-xl border border-slate-200 p-3 text-sm"><p class="font-semibold text-slate-800">{{ item.kode_lokasi }} · {{ item.nama_lokasi }}</p><p class="mt-1 text-xs text-slate-500">{{ item.gedung || 'Gedung belum diisi' }} {{ item.ruangan || '' }}</p></div>
                </div>
                <p v-if="!props.racks.length && !props.locations.length" class="py-6 text-sm text-slate-500">Tidak ditemukan.</p>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="font-semibold text-slate-800">Aktivitas ({{ props.activities.length }})</h2>
                <div class="mt-3 divide-y divide-slate-100">
                    <div v-for="item in props.activities" :key="item.id" class="py-3"><p class="font-semibold text-slate-800">{{ item.description }}</p><p class="mt-1 text-xs text-slate-500">{{ item.user?.name || 'Sistem' }}</p></div>
                    <p v-if="!props.activities.length" class="py-6 text-sm text-slate-500">Tidak ditemukan.</p>
                </div>
            </section>
        </div>
    </AppLayout>
</template>