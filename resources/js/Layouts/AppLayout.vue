<script setup>
import Sidebar from '@/Components/Sidebar.vue';
import GlobalSearchBar from '@/Components/GlobalSearchBar.vue';
import { institution } from '@/config/institution';
import { Link, usePage } from '@inertiajs/vue3';
import { onBeforeUnmount, onMounted, onUnmounted, ref, watch } from 'vue';

defineProps({
    activeSeksiId: {
        type: [Number, String, null],
        default: null,
    },
});

const page = usePage();

/* ============ Skeleton loading ====== */
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

const SIDEBAR_STORAGE_KEY = 'siparsip.sidebar.collapsed';

const mobileOpen = ref(false);
const desktopCollapsed = ref(false);

const readCollapsedPreference = () => {
    try {
        return localStorage.getItem(SIDEBAR_STORAGE_KEY) === 'true';
    } catch {
        return false;
    }
};

const toggleDesktopSidebar = () => {
    desktopCollapsed.value = !desktopCollapsed.value;
};

const closeMobile = () => {
    mobileOpen.value = false;
};

const onKeydown = (event) => {
    if (event.key === 'Escape' && mobileOpen.value) {
        mobileOpen.value = false;
    }
};

watch(desktopCollapsed, (value) => {
    try {
        localStorage.setItem(SIDEBAR_STORAGE_KEY, value ? 'true' : 'false');
    } catch {
        // ignore quota / private mode
    }
});

onMounted(() => {
    desktopCollapsed.value = readCollapsedPreference();
    window.addEventListener('keydown', onKeydown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', onKeydown);
});
</script>

<template>
    <div class="flex h-screen overflow-hidden bg-[#EEF2F7]">
        <div
            v-if="mobileOpen"
            class="fixed inset-0 z-40 bg-slate-900/50 lg:hidden"
            @click="closeMobile"
        />

        <!-- Mobile drawer -->
        <div
            id="mobile-sidebar"
            class="fixed inset-y-0 left-0 z-50 flex h-full w-[min(88vw,320px)] transform shadow-2xl transition duration-200 lg:hidden"
            :class="mobileOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <Sidebar
                sidebar-id="mobile-sidebar-panel"
                :active-seksi-id="activeSeksiId"
                :collapsed="false"
                show-close
                @close="closeMobile"
                @navigate="closeMobile"
            />
        </div>

        <!-- Desktop sidebar: tetap di tempat, tidak ikut scroll -->
        <div class="relative hidden h-full min-h-0 shrink-0 lg:block">
            <Sidebar
                sidebar-id="desktop-sidebar"
                :active-seksi-id="activeSeksiId"
                :collapsed="desktopCollapsed"
                collapsible
                @toggle-collapse="toggleDesktopSidebar"
            />
        </div>

        <!-- Panel kanan: satu-satunya area yang scroll -->
        <div class="flex min-h-0 min-w-0 flex-1 flex-col overflow-y-auto">
            <div class="sticky top-0 z-30 border-b border-slate-200 bg-white/95 shadow-sm backdrop-blur">
                <div class="flex flex-col gap-2 px-4 py-2.5 sm:flex-row sm:items-center sm:gap-3 sm:px-6 lg:px-8">
                    <!-- Mobile: baris terpisah agar search tidak menabrak Menu; sm+: ikut flex row -->
                    <div class="flex items-center gap-3 sm:contents">
                        <button
                            type="button"
                            class="inline-flex min-h-11 shrink-0 items-center gap-2 rounded-lg border border-slate-200 px-2.5 py-2 text-sm font-semibold text-slate-700 transition duration-200 hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-blue/40 sm:px-3 lg:hidden"
                            aria-label="Buka menu"
                            :aria-expanded="mobileOpen"
                            aria-controls="mobile-sidebar"
                            @click="mobileOpen = true"
                        >
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16" />
                            </svg>
                            <span class="hidden sm:inline">Menu</span>
                        </button>
                        <div class="flex min-w-0 shrink items-center gap-2 lg:hidden">
                            <img
                                :src="institution.logoPath"
                                alt=""
                                class="h-7 w-7 rounded-md object-contain"
                            />
                            <span class="truncate text-sm font-bold tracking-tight text-brand-navy">
                                {{ institution.systemName }}
                            </span>
                        </div>
                        <Link
                            :href="route('profile.edit')"
                            class="ml-auto max-w-[40%] truncate text-xs text-slate-500 hover:text-slate-700 sm:order-last sm:ml-0 sm:max-w-[8rem] lg:hidden"
                        >
                            {{ page.props.auth?.user?.name }}
                        </Link>
                    </div>
                    <div class="hidden min-w-0 flex-1 sm:block" aria-hidden="true" />
                    <GlobalSearchBar />
                    <div class="hidden min-w-0 flex-1 sm:block" aria-hidden="true" />
                </div>
            </div>

            <!-- Skeleton header -->
            <div
                v-if="isLoading && $slots['skeleton-header']"
                role="status"
                aria-live="polite"
                class="motion-safe:animate-pulse border-b border-slate-200 px-4 py-5 sm:px-6 lg:px-8"
            >
                <slot name="skeleton-header" />
            </div>
            <!-- Header asli -->
            <header
                v-else-if="$slots.header"
                class="border-b border-slate-200/80 bg-white/80 px-4 py-5 backdrop-blur sm:px-6 lg:px-8"
            >
                <slot name="header" />
            </header>

            <main class="min-w-0 flex-1 px-4 py-5 sm:px-6 sm:py-6 lg:px-8">
                <div
                    v-if="isLoading && $slots.skeleton"
                    role="status"
                    aria-live="polite"
                    class="motion-safe:animate-pulse"
                >
                    <slot name="skeleton" />
                </div>
                <template v-else>
                    <slot />
                </template>
            </main>
        </div>
    </div>
</template>
