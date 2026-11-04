<script setup>
import { institution } from '@/config/institution';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    canLogin: {
        type: Boolean,
    },
    faq: {
        type: Array,
        default: () => [],
    },
});

const openIndex = ref(-1);
const query = ref('');
const hasPhone = computed(() => Boolean(institution.phone?.trim()));
const hasEmail = computed(() => Boolean(institution.email?.trim()));

const filteredFaqs = computed(() => {
    const q = query.value.trim().toLowerCase();
    const items = props.faq.map((f, index) => ({ ...f, originalIndex: index }));
    if (!q) return items;
    return items.filter(
        (f) =>
            f.pertanyaan.toLowerCase().includes(q) ||
            f.jawaban.toLowerCase().includes(q),
    );
});

const toggle = (originalIndex) => {
    openIndex.value = openIndex.value === originalIndex ? -1 : originalIndex;
};

const noResults = computed(
    () => filteredFaqs.value.length === 0,
);
</script>

<template>
    <Head title="Bantuan" />

    <PublicLayout :can-login="canLogin">
        <template #skeleton>
            <section class="flex-1 border-b border-slate-100 bg-white">
                <div class="mx-auto w-full max-w-3xl px-6 py-20 lg:py-28">
                    <div class="mx-auto h-10 w-2/3 rounded bg-slate-200" />
                    <div class="mt-10 h-4 w-40 rounded bg-slate-200" />
                    <div class="mt-10 space-y-4">
                        <div v-for="i in 3" :key="'sk-' + i" class="h-12 w-full rounded-lg bg-slate-100" />
                    </div>
                    <div class="mt-12 h-64 rounded-2xl bg-slate-100" />
                </div>
            </section>
        </template>

        <section class="border-b border-slate-100 bg-white shadow-sm">
            <div class="mx-auto w-full max-w-3xl px-6 py-20 lg:py-28">
                <h1
                    v-reveal
                    class="text-center font-display text-4xl font-bold leading-[1.15] tracking-tight text-brand-navy md:text-5xl lg:text-6xl"
                >
                    Bantuan untuk petugas
                </h1>

                <!-- Search bar -->
                <div v-reveal :style="{ transitionDelay: '100ms' }" class="relative mt-8">
                    <AppIcon
                        name="search"
                        :size="20"
                        :stroke-width="2"
                        class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400"
                        decorative
                    />
                    <input
                        v-model="query"
                        type="search"
                        placeholder="Cari panduan..."
                        class="w-full rounded-xl border border-slate-200 bg-white py-4 pl-12 pr-4 text-base text-slate-900 shadow-sm transition duration-200 placeholder:text-slate-400 focus:border-brand-blue focus:outline-none focus:ring-2 focus:ring-brand-blue/30"
                    />
                </div>

                <!-- Ketentuan akses akun -->
                <div v-reveal :style="{ transitionDelay: '200ms' }" class="mt-10">
                    <h2 class="text-base font-semibold text-brand-navy">
                        Ketentuan akses akun
                    </h2>
                    <p class="mt-2 text-sm leading-7 text-slate-500">
                        {{ institution.eligibility }}
                    </p>
                </div>

                <!-- Seamless Accordion -->
                <div v-reveal :style="{ transitionDelay: '300ms' }" class="mt-10">
                    <div
                        v-if="noResults"
                        class="py-8 text-center text-sm text-slate-500"
                    >
                        Tidak ada hasil untuk "{{ query }}". Coba kata kunci
                        lain atau hubungi admin.
                    </div>

                    <div
                        v-else
                        class="divide-y divide-slate-200"
                    >
                        <div
                            v-for="faq in filteredFaqs"
                            :key="faq.originalIndex"
                        >
                            <button
                                type="button"
                                class="group flex w-full items-center justify-between gap-4 py-5 text-left transition duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-blue/40"
                                :aria-expanded="openIndex === faq.originalIndex"
                                @click="toggle(faq.originalIndex)"
                            >
                                <span
                                    class="text-base font-semibold tracking-tight text-brand-navy"
                                >
                                    {{ faq.pertanyaan }}
                                </span>
                                <AppIcon
                                    name="chevron-down"
                                    :size="20"
                                    :stroke-width="2"
                                    :class="[
                                        'h-5 w-5 shrink-0 text-slate-400 transition-transform duration-300',
                                        openIndex === faq.originalIndex
                                            ? 'rotate-180 text-brand-blue'
                                            : '',
                                    ]"
                                    decorative
                                />
                            </button>
                            <div
                                :class="[
                                    'grid transition-all duration-300 ease-in-out',
                                    openIndex === faq.originalIndex
                                        ? 'grid-rows-[1fr] opacity-100 pb-5'
                                        : 'grid-rows-[0fr] opacity-0',
                                ]"
                            >
                                <div class="overflow-hidden">
                                    <p class="pr-10 text-sm leading-7 text-slate-500">
                                        {{ faq.jawaban }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kontak admin -->
                <div
                    v-reveal
                    :style="{ transitionDelay: '400ms' }"
                    class="mt-12 rounded-2xl border border-slate-100/50 bg-slate-50 p-6 shadow-sm"
                >
                    <h2
                        class="font-display text-xl font-semibold tracking-tight text-brand-navy"
                    >
                        Kontak admin
                    </h2>
                    <p class="mt-3 text-sm leading-7 text-slate-600">
                        {{ institution.adminRole }}
                    </p>
                    <p class="mt-2 text-sm leading-7 text-slate-500">
                        {{ institution.adminNote }}
                    </p>

                    <dl class="mt-6 space-y-3 text-sm">
                        <div
                            class="flex flex-col gap-0.5 sm:flex-row sm:gap-4"
                        >
                            <dt class="w-28 shrink-0 font-medium text-slate-600">
                                Jam layanan
                            </dt>
                            <dd class="text-slate-500">
                                {{ institution.officeHours }}
                            </dd>
                        </div>
                        <div
                            class="flex flex-col gap-0.5 sm:flex-row sm:gap-4"
                        >
                            <dt class="w-28 shrink-0 font-medium text-slate-600">
                                Tempat
                            </dt>
                            <dd class="text-slate-500">
                                {{ institution.address }}
                            </dd>
                        </div>
                        <div
                            v-if="hasPhone"
                            class="flex flex-col gap-0.5 sm:flex-row sm:gap-4"
                        >
                            <dt class="w-28 shrink-0 font-medium text-slate-600">
                                Telepon
                            </dt>
                            <dd>
                                <a
                                    :href="`tel:${institution.phone}`"
                                    class="text-brand-blue hover:underline"
                                >
                                    {{ institution.phone }}
                                </a>
                            </dd>
                        </div>
                        <div
                            v-if="hasEmail"
                            class="flex flex-col gap-0.5 sm:flex-row sm:gap-4"
                        >
                            <dt class="w-28 shrink-0 font-medium text-slate-600">
                                Email
                            </dt>
                            <dd>
                                <a
                                    :href="`mailto:${institution.email}`"
                                    class="text-brand-blue hover:underline"
                                >
                                    {{ institution.email }}
                                </a>
                            </dd>
                        </div>
                    </dl>

                    <div class="mt-8">
                        <p class="text-sm font-medium text-slate-600">
                            Lokasi kantor
                        </p>
                        <div
                            class="mt-3 overflow-hidden rounded-xl border border-slate-200 bg-slate-100"
                        >
                            <iframe
                                title="Lokasi Kelurahan Grogol Selatan"
                                :src="institution.mapsEmbedUrl"
                                class="h-64 w-full border-0 sm:h-72"
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"
                                allowfullscreen
                            />
                        </div>
                        <a
                            :href="institution.mapsUrl"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="mt-3 inline-flex min-h-11 items-center text-sm font-semibold text-brand-blue transition duration-200 hover:text-blue-800 hover:underline"
                        >
                            Buka di Google Maps
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
