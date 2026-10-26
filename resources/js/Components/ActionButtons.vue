<script setup>
import Dropdown from '@/Components/Dropdown.vue';

const props = defineProps({
    arsip: {
        type: Object,
        required: true,
    },
    /** Sembunyikan Buka (mis. di modal detail yang sudah terbuka) */
    showView: {
        type: Boolean,
        default: true,
    },
    /** Sembunyikan Label di menu (mis. sudah ada tombol Cetak Stiker di luar) */
    showLabelInMenu: {
        type: Boolean,
        default: true,
    },
    /** Sembunyikan dropdown "Tindakan Lain" (mis. di modal detail yang sudah punya tombol khusus) */
    showDropdown: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['edit', 'view', 'label', 'pinjam', 'kembalikan', 'settersedia', 'tandahilang', 'delete']);

const iconBtn =
    'inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-blue/25';

const menuItem =
    'flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-slate-700 transition hover:bg-slate-50';
</script>

<template>
    <div class="inline-flex flex-nowrap items-center gap-1">
        <button
            v-if="showView"
            type="button"
            :class="iconBtn"
            title="Lihat Detail"
            aria-label="Lihat detail dokumen"
            @click="emit('view', arsip)"
        >
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z" />
                <circle cx="12" cy="12" r="3" />
            </svg>
        </button>

        <button
            type="button"
            :class="iconBtn"
            title="Edit Data"
            aria-label="Edit data arsip"
            @click="emit('edit', arsip)"
        >
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.5a2.1 2.1 0 013 3L8 18l-4 1 1-4 11.5-11.5z" />
            </svg>
        </button>

        <button
            v-if="arsip.status_arsip === 'tersedia'"
            type="button"
            :class="iconBtn"
            title="Pinjamkan"
            aria-label="Pinjamkan dokumen"
            @click="emit('pinjam', arsip)"
        >
            <!-- Ikon: dokumen dengan panah keluar (pinjam keluar) -->
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 3h7l5 5v13a1 1 0 01-1 1H7a1 1 0 01-1-1V4a1 1 0 011-1z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M14 3v5h5" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 12v5m0 0l-2-2m2 2l2-2" />
            </svg>
        </button>
        <button
            v-if="arsip.status_arsip === 'dipinjam'"
            type="button"
            :class="iconBtn"
            title="Kembalikan"
            aria-label="Kembalikan dokumen"
            @click="emit('kembalikan', arsip)"
        >
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l-4-4 4-4" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 10h9a5 5 0 010 10h-3" />
            </svg>
        </button>

        <button
            v-if="arsip.status_arsip === 'hilang'"
            type="button"
            :class="iconBtn"
            title="Tandai Tersedia"
            aria-label="Tandai dokumen tersedia kembali"
            @click="emit('settersedia', arsip)"
        >
            <!-- Ikon: centang dalam lingkaran (kembali tersedia) -->
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8">
                <circle cx="12" cy="12" r="9" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.5 12l2 2 5-5" />
            </svg>
        </button>

        <button
            v-if="arsip.status_arsip !== 'hilang'"
            type="button"
            :class="iconBtn"
            title="Tandai Hilang"
            aria-label="Tandai dokumen sebagai hilang"
            @click="emit('tandahilang', arsip)"
        >
            <!-- Ikon: dokumen dengan tanda hilang (garis coret diagonal) -->
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 3h7l5 5v13a1 1 0 01-1 1H7a1 1 0 01-1-1V4a1 1 0 011-1z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M14 3v5h5" />
                <path stroke-linecap="round" d="M8 14l8 4M16 14l-8 4" />
            </svg>
        </button>

        <Dropdown v-if="showDropdown" align="right" width="48">
            <template #trigger>
                <button
                    type="button"
                    :class="iconBtn"
                    title="Tindakan Lain"
                    aria-label="Menu tindakan lain"
                    aria-haspopup="menu"
                >
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="currentColor">
                        <circle cx="5" cy="12" r="1.6" />
                        <circle cx="12" cy="12" r="1.6" />
                        <circle cx="19" cy="12" r="1.6" />
                    </svg>
                </button>
            </template>
            <template #content>
                <div class="py-1" role="menu">
                    <button
                        v-if="showLabelInMenu"
                        type="button"
                        :class="menuItem"
                        role="menuitem"
                        @click="emit('label', arsip)"
                    >
                        <svg viewBox="0 0 24 24" class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h4v4H4zM10 7h4v4h-4zM16 7h4v4h-4zM4 13h4v4H4zM10 13h4v4h-4zM16 13h4v4h-4z" />
                        </svg>
                        Cetak Label
                    </button>
                    <button
                        type="button"
                        class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-rose-600 transition hover:bg-rose-50"
                        role="menuitem"
                        @click="emit('delete', arsip)"
                    >
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" d="M4 7h16M9 7V5h6v2M8 7l1 12h6l1-12" />
                        </svg>
                        Hapus
                    </button>
                </div>
            </template>
        </Dropdown>
    </div>
</template>
