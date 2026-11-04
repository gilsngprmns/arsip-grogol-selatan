<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    mode: {
        type: String,
        default: 'edit',
    },
    initialData: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close']);

const page = usePage();

const title = computed(() =>
    props.mode === 'edit' ? 'Perbarui Lokasi' : 'Tambah Lokasi',
);

const emptyForm = () => ({
    kode_lokasi: '',
    nama_lokasi: '',
    gedung: '',
    lantai: '',
    ruangan: '',
    deskripsi: '',
    is_active: true,
});

const form = ref(emptyForm());

const errors = computed(() => {
    if (!props.show) return {};
    return page.props.errors ?? {};
});

const fillForm = () => {
    if (props.mode === 'edit' && props.initialData) {
        form.value = {
            kode_lokasi: props.initialData.kode_lokasi ?? '',
            nama_lokasi: props.initialData.nama_lokasi ?? '',
            gedung: props.initialData.gedung ?? '',
            lantai: props.initialData.lantai ?? '',
            ruangan: props.initialData.ruangan ?? '',
            deskripsi: props.initialData.deskripsi ?? '',
            is_active: Boolean(props.initialData.is_active ?? true),
        };
    } else {
        form.value = emptyForm();
    }
};

watch(
    () => [props.show, props.mode, props.initialData],
    ([show]) => {
        if (show) {
            fillForm();
        }
    },
);

const close = () => emit('close');

const processing = ref(false);

const submit = () => {
    processing.value = true;

    if (props.mode === 'edit' && props.initialData?.id) {
        router.put(
            route('lokasi.update', props.initialData.id),
            form.value,
            {
                preserveScroll: true,
                onSuccess: () => {
                    processing.value = false;
                    close();
                },
                onError: () => {
                    processing.value = false;
                },
            },
        );
        return;
    }

    router.post(
        route('lokasi.store'),
        form.value,
        {
            preserveScroll: true,
            onSuccess: () => {
                processing.value = false;
                close();
            },
            onError: () => {
                processing.value = false;
            },
        },
    );
};
</script>

<template>
    <Modal :show="show" max-width="lg" @close="close">
        <div class="border-b border-slate-100 px-6 py-4">
            <div class="flex items-start justify-between gap-4">
                <h2 class="text-lg font-bold text-slate-800">{{ title }}</h2>
                <button
                    type="button"
                    class="rounded-md p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                    @click="close"
                >
                    <span class="sr-only">Tutup</span>
                    <AppIcon name="close" :size="20" :stroke-width="2" class="h-5 w-5" decorative />
                </button>
            </div>
        </div>

        <form class="space-y-4 px-6 py-5" @submit.prevent="submit">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <InputLabel for="kode_lokasi" value="Kode Lokasi *" />
                    <input
                        id="kode_lokasi"
                        v-model="form.kode_lokasi"
                        type="text"
                        class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-brand-blue focus:ring-brand-blue"
                        required
                        placeholder="Contoh: LOK-001"
                    />
                    <InputError class="mt-1" :message="errors.kode_lokasi" />
                </div>

                <div>
                    <InputLabel for="nama_lokasi" value="Nama Lokasi *" />
                    <input
                        id="nama_lokasi"
                        v-model="form.nama_lokasi"
                        type="text"
                        class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-brand-blue focus:ring-brand-blue"
                        required
                        placeholder="Contoh: Gedung A Lantai 2"
                    />
                    <InputError class="mt-1" :message="errors.nama_lokasi" />
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <InputLabel for="gedung" value="Gedung" />
                    <input
                        id="gedung"
                        v-model="form.gedung"
                        type="text"
                        class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-brand-blue focus:ring-brand-blue"
                        placeholder="Contoh: Gedung Utama"
                    />
                    <InputError class="mt-1" :message="errors.gedung" />
                </div>

                <div>
                    <InputLabel for="lantai" value="Lantai" />
                    <input
                        id="lantai"
                        v-model="form.lantai"
                        type="text"
                        class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-brand-blue focus:ring-brand-blue"
                        placeholder="Contoh: Lantai 1 atau Basement"
                    />
                    <InputError class="mt-1" :message="errors.lantai" />
                </div>

                <div>
                    <InputLabel for="ruangan" value="Ruangan" />
                    <input
                        id="ruangan"
                        v-model="form.ruangan"
                        type="text"
                        class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-brand-blue focus:ring-brand-blue"
                        placeholder="Contoh: Ruang Kerja"
                    />
                    <InputError class="mt-1" :message="errors.ruangan" />
                </div>
            </div>

            <div>
                <InputLabel for="deskripsi" value="Deskripsi" />
                <textarea
                    id="deskripsi"
                    v-model="form.deskripsi"
                    rows="3"
                    class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-brand-blue focus:ring-brand-blue"
                    placeholder="Tambahkan detail lokasi (opsional)..."
                ></textarea>
                <InputError class="mt-1" :message="errors.deskripsi" />
            </div>

            <div>
                <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
                    <input v-model="form.is_active" type="checkbox" class="h-4 w-4 rounded border-slate-300" />
                    Lokasi aktif
                </label>
                <InputError class="mt-1" :message="errors.is_active" />
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-4">
                <SecondaryButton type="button" @click="close">Batal</SecondaryButton>
                <PrimaryButton
                    class="bg-brand-blue hover:bg-blue-700"
                    :disabled="processing"
                >
                    {{ mode === 'edit' ? 'Simpan Perubahan' : 'Simpan Lokasi' }}
                </PrimaryButton>
            </div>
        </form>
    </Modal>
</template>
