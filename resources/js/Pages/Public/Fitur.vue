<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    manfaat: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <Head title="Manfaat" />

    <PublicLayout :can-login="canLogin">
        <template #skeleton>
            <section class="flex-1 border-b border-slate-100 bg-white">
                <div class="mx-auto w-full max-w-5xl px-6 py-20 lg:py-28">
                    <div class="mx-auto h-10 w-2/3 rounded bg-slate-200" />
                    <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        <div v-for="i in 6" :key="'sk-' + i" class="h-40 rounded-2xl bg-slate-100" />
                    </div>
                </div>
            </section>
        </template>

        <section class="border-b border-slate-100 bg-white shadow-sm">
            <div class="mx-auto w-full max-w-5xl px-6 py-20 lg:py-28">
                <h1
                    v-reveal
                    class="text-center font-display text-4xl font-bold leading-[1.15] tracking-tight text-brand-navy md:text-5xl lg:text-6xl"
                >
                    Manfaat dalam kerja pengarsipan
                </h1>

                <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <article
                        v-for="(benefit, index) in manfaat"
                        :key="benefit.id"
                        v-reveal
                        :style="{ transitionDelay: `${index * 100}ms` }"
                        class="rounded-2xl bg-slate-50 p-6 shadow-sm transition-all duration-300 hover:shadow-md hover:-translate-y-0.5"
                    >
                        <span
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 text-blue-900"
                        >
                            <AppIcon :name="benefit.ikon || 'history'" :size="20" :stroke-width="2" class="h-5 w-5" decorative />
                        </span>
                        <h2
                            class="font-display mt-5 text-lg font-semibold tracking-tight text-brand-navy"
                        >
                            {{ benefit.judul }}
                        </h2>
                        <p class="mt-2 text-sm leading-7 text-slate-500">
                            {{ benefit.deskripsi }}
                        </p>
                    </article>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
