<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <div class="px-4 py-12 text-center sm:px-6">
        <p class="text-sm font-semibold text-slate-700">{{ title }}</p>
        <p v-if="description" class="mx-auto mt-2 max-w-md text-sm leading-7 text-slate-500">
            {{ description }}
        </p>
        <div v-if="$slots.action" class="mt-5 flex justify-center">
            <slot name="action" />
        </div>
    </div>
</template>
