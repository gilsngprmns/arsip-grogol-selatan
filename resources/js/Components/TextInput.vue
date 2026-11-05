<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        v-bind="$attrs"
        class="rounded-lg border-slate-300 shadow-sm focus:border-brand-blue focus:ring-brand-blue"
        v-model="model"
        ref="input"
    />
</template>
