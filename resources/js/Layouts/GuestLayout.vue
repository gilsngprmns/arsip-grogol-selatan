<script setup>
import ImageLightbox from '@/Components/ImageLightbox.vue';
import { institution } from '@/config/institution';
import { Link, usePage } from '@inertiajs/vue3';
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';

const year = new Date().getFullYear();
const page = usePage();
const SKELETON_MS = 1000;
const isLoading = ref(true);
let skeletonTimer = null;

const armSkeleton = () => {
    if (skeletonTimer) clearTimeout(skeletonTimer);
    skeletonTimer = setTimeout(() => {
        isLoading.value = false;
        skeletonTimer = null;
    }, SKELETON_MS);
};

onMounted(armSkeleton);

watch(
    () => page.url,
    () => {
        if (isLoading.value) return;
        isLoading.value = true;
        armSkeleton();
    }
);

onBeforeUnmount(() => {
    if (skeletonTimer) clearTimeout(skeletonTimer);
});

// Logo kelurahan bisa dilihat utuh: klik lambang → lightbox (gambar penuh,
// latar gelap). Blok teks di sebelahnya tetap link ke beranda.
const lightbox = ref({ show: false, src: '', label: '' });
const openLogoLightbox = () => {
    lightbox.value = { show: true, src: institution.logoPath, label: institution.name };
};
const closeLogoLightbox = () => {
    lightbox.value.show = false;
};
</script>

<template>
    <div class="min-h-screen bg-[#F4F7FB] text-slate-900">
        <div class="grid min-h-screen lg:grid-cols-[minmax(360px,0.85fr)_minmax(480px,1.15fr)]">
            <!-- Sidebar kiri — skeleton -->
            <aside
                v-if="isLoading"
                class="relative hidden overflow-hidden bg-brand-navy px-10 py-10 text-white lg:flex lg:flex-col lg:justify-between xl:px-16"
            >
                <div class="relative flex flex-col items-start gap-3 motion-safe:animate-pulse">
                    <div class="h-11 w-11 rounded-xl bg-slate-600" />
                    <div class="h-6 w-44 rounded bg-slate-600" />
                    <div class="h-4 w-56 rounded bg-slate-500" />
                </div>
                <div class="relative max-w-md space-y-3 motion-safe:animate-pulse">
                    <div class="h-4 w-40 rounded bg-slate-600" />
                    <div class="h-7 w-52 rounded bg-slate-500" />
                    <div class="space-y-2 pt-3">
                        <div class="h-3.5 w-full rounded bg-slate-500" />
                        <div class="h-3.5 w-3/4 rounded bg-slate-500" />
                    </div>
                </div>
                <div class="relative space-y-2 motion-safe:animate-pulse">
                    <div class="h-3 w-48 rounded bg-slate-600" />
                    <div class="h-3 w-36 rounded bg-slate-600" />
                </div>
            </aside>

            <!-- Sidebar kiri — asli -->
            <aside v-else class="relative hidden overflow-hidden bg-brand-navy px-10 py-10 text-white lg:flex lg:flex-col lg:justify-between xl:px-16">
                <div class="absolute inset-0 opacity-20 [background-image:linear-gradient(rgba(255,255,255,.08)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,.08)_1px,transparent_1px)] [background-size:34px_34px]" />
                <div class="relative flex flex-col items-start gap-3">
                    <div v-reveal class="relative">
                        <div class="inline-flex items-center gap-3">
                            <button
                                type="button"
                                class="h-11 w-11 shrink-0 rounded-xl bg-white p-0.5 transition-colors hover:bg-blue-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/50 focus-visible:ring-offset-2 focus-visible:ring-offset-brand-navy"
                                :aria-label="`Perbesar logo ${institution.name}`"
                                @click="openLogoLightbox()"
                            >
                                <img
                                    :src="institution.logoPath"
                                    :alt="`Logo ${institution.name}`"
                                    class="h-full w-full rounded-xl object-contain"
                                />
                            </button>
                            <Link
                                href="/"
                                class="rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-white/50 focus-visible:ring-offset-2 focus-visible:ring-offset-brand-navy"
                            >
                                <span>
                                    <span class="block text-sm font-bold tracking-tight">{{ institution.headerTitle }}</span>
                                    <span class="block text-xs text-blue-200">{{ institution.headerSubtitle }}</span>
                                </span>
                            </Link>
                        </div>
                    </div>
                </div>

                <div v-reveal :style="{ transitionDelay: '120ms' }" class="relative max-w-md">
                    <p class="mb-4 text-sm font-medium text-blue-200">{{ institution.accessNotice }}</p>
                    <h1 class="text-xl font-semibold leading-tight tracking-tight xl:text-2xl">
                        {{ institution.fullTitle }}
                    </h1>
                    <p class="mt-6 max-w-sm text-sm leading-7 text-slate-300">
                        {{ institution.missionBrief }}
                    </p>
                </div>

                <div v-reveal :style="{ transitionDelay: '240ms' }" class="relative text-xs leading-5 text-slate-400">
                    <p>{{ institution.identityLine }}</p>
                    <p class="mt-2">Jam pelayanan: {{ institution.officeHours }}</p>
                </div>
            </aside>

            <!-- Panel kanan — skeleton -->
            <main v-if="isLoading" class="flex min-h-screen flex-col px-5 py-6 sm:px-10 lg:px-16 xl:px-24">
                <div class="flex justify-end">
                    <div class="h-6 w-16 rounded bg-slate-200" />
                </div>
                <div class="flex flex-1 items-center justify-center py-10">
                    <div class="w-full max-w-md">
                        <slot name="skeleton">
                            <div class="motion-safe:animate-pulse space-y-5">
                                <div class="h-7 w-44 rounded bg-slate-200" />
                                <div class="h-4 w-full rounded bg-slate-100" />
                                <div class="mt-6 space-y-4">
                                    <div class="h-10 rounded-lg bg-slate-100" />
                                    <div class="h-10 rounded-lg bg-slate-100" />
                                    <div class="h-10 rounded-lg bg-slate-100" />
                                </div>
                            </div>
                        </slot>
                    </div>
                </div>
                <div class="h-3 w-48 self-center rounded bg-slate-200" />
            </main>

            <!-- Panel kanan — asli -->
            <main v-else class="flex min-h-screen flex-col px-5 py-6 sm:px-10 lg:px-16 xl:px-24">
                <div class="flex justify-end">
                    <Link
                        href="/"
                        class="inline-flex min-h-11 items-center rounded-lg px-2 text-sm font-semibold text-slate-500 transition duration-200 hover:text-brand-blue focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-blue/40"
                    >
                        Beranda
                    </Link>
                </div>
                <div v-reveal :style="{ transitionDelay: '100ms' }" class="flex flex-1 items-center justify-center py-10">
                    <div class="w-full max-w-md">
                        <div class="mb-8 flex items-center gap-3 lg:hidden">
                            <button
                                type="button"
                                class="h-10 w-10 shrink-0 rounded-xl transition-transform focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-blue/40"
                                :aria-label="`Perbesar logo ${institution.name}`"
                                @click="openLogoLightbox()"
                            >
                                <img
                                    :src="institution.logoPath"
                                    :alt="`Logo ${institution.name}`"
                                    class="h-full w-full rounded-xl object-contain"
                                />
                            </button>
                            <Link
                                href="/"
                                class="rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-blue/40"
                            >
                                <span>
                                    <span class="block text-sm font-bold tracking-tight text-brand-navy">{{ institution.headerTitle }}</span>
                                    <span class="block text-xs text-slate-500">{{ institution.headerSubtitle }}</span>
                                </span>
                            </Link>
                        </div>
                        <slot />
                    </div>
                </div>
                <p class="text-center text-xs text-slate-400">
                    © {{ year }} {{ institution.name }}
                </p>
            </main>
        </div>

        <!-- Lightbox logo kelurahan: gambar utuh di tengah layar, latar gelap. -->
        <ImageLightbox
            :show="lightbox.show"
            :src="lightbox.src"
            :label="lightbox.label"
            @close="closeLogoLightbox()"
        />
    </div>
</template>
