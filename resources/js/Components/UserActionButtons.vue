<script setup>
import Dropdown from '@/Components/Dropdown.vue';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
    canDeactivate: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['edit', 'delete-permanent', 'reset-password']);

const page = usePage();
const isSelf = computed(() => Number(page.props.auth?.user?.id) === Number(props.user.id));

const showOverflow = computed(() => true);

const iconBtn =
    'inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-blue/25';
</script>

<template>
    <div class="inline-flex flex-nowrap items-center gap-1">
        <button
            type="button"
            :class="iconBtn"
            title="Edit"
            aria-label="Edit pengguna"
            @click="emit('edit', user)"
        >
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.5a2.1 2.1 0 013 3L8 18l-4 1 1-4 11.5-11.5z" />
            </svg>
        </button>

        <Dropdown v-if="showOverflow" align="right" width="48">
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
                        type="button"
                        class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-slate-700 transition hover:bg-slate-50"
                        role="menuitem"
                        @click="emit('reset-password', user)"
                    >
                        <svg viewBox="0 0 24 24" class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" d="M12 2a4 4 0 00-4 4v3h8V6a4 4 0 00-4-4z" />
                            <rect x="8" y="9" width="8" height="11" rx="1.5" />
                            <circle cx="12" cy="14" r="1.5" fill="currentColor" stroke="none" />
                        </svg>
                        Reset Password
                    </button>
                    <button
                        v-if="!isSelf && canDeactivate"
                        type="button"
                        class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-rose-600 transition hover:bg-rose-50"
                        role="menuitem"
                        @click="emit('delete-permanent', user)"
                    >
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" d="M4 7h16M9 7V5h6v2M8 7l1 12h6l1-12" />
                        </svg>
                        Hapus Permanen
                    </button>
                    <p
                        v-else-if="!isSelf && !canDeactivate"
                        class="px-3 py-2 text-xs text-slate-400"
                    >
                        Admin aktif terakhir — tidak bisa dihapus
                    </p>
                </div>
            </template>
        </Dropdown>
    </div>
</template>
