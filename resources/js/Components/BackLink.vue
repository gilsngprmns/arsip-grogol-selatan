<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <Link
        :href="href"
        class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600 shadow-sm transition duration-200 hover:bg-slate-50 hover:text-brand-blue focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-blue/40"
    >
        <AppIcon name="arrow-left" :size="16" :stroke-width="2" class="h-4 w-4 shrink-0" decorative />
        <slot>Kembali</slot>
    </Link>
</template>
