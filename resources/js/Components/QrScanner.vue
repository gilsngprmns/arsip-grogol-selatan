<script setup>
import axios from 'axios';
import { Html5Qrcode } from 'html5-qrcode';
import { nextTick, onBeforeUnmount, ref, watch } from 'vue';

const props = defineProps({
    active: {
        type: Boolean,
        default: false,
    },
    mode: {
        type: String,
        default: 'webcam',
    },
});

const emit = defineEmits(['found']);

const scannerId = 'qr-reader-region';
const statusMessage = ref('');
const statusType = ref('info');
const foundArsip = ref(null);
const isScanning = ref(false);
const fileInput = ref(null);

let html5QrCode = null;
let lastScannedCode = null;
let lastScannedAt = 0;

const setStatus = (message, type = 'info') => {
    statusMessage.value = message;
    statusType.value = type;
};

const lookupKode = async (decodedText) => {
    const kode = String(decodedText || '').trim();

    if (!kode) {
        return;
    }

    const now = Date.now();
    if (kode === lastScannedCode && now - lastScannedAt < 2500) {
        return;
    }

    lastScannedCode = kode;
    lastScannedAt = now;

    setStatus(`Mencari arsip: ${kode}...`, 'info');

    try {
        const { data } = await axios.post('/api/arsip/scan', { kode_arsip: kode });
        foundArsip.value = data;
        setStatus('Arsip ditemukan.', 'success');
        emit('found', data);
    } catch (error) {
        foundArsip.value = null;
        const message = error?.response?.data?.message || 'Kode arsip tidak ditemukan';
        setStatus(message, 'error');
    }
};

const stopScanner = async () => {
    if (!html5QrCode) {
        isScanning.value = false;
        return;
    }

    try {
        if (html5QrCode.isScanning) {
            await html5QrCode.stop();
        }
        await html5QrCode.clear();
    } catch {
        // Kamera mungkin sudah berhenti.
    } finally {
        html5QrCode = null;
        isScanning.value = false;
    }
};

const startWebcam = async () => {
    await stopScanner();
    await nextTick();

    html5QrCode = new Html5Qrcode(scannerId);
    isScanning.value = true;
    setStatus('Arahkan kamera ke QR stiker pada berkas fisik.', 'info');
    foundArsip.value = null;

    try {
        await html5QrCode.start(
            { facingMode: 'environment' },
            {
                fps: 10,
                qrbox: { width: 220, height: 220 },
                aspectRatio: 1.777,
            },
            (decodedText) => {
                lookupKode(decodedText);
            },
            () => {
                // ignore frame miss
            },
        );
    } catch (error) {
        isScanning.value = false;
        setStatus(
            error?.message?.includes('Permission')
                ? 'Izin kamera ditolak. Izinkan kamera di browser atau gunakan tab Upload Gambar QR.'
                : 'Kamera tidak tersedia. Gunakan tab Upload Gambar QR.',
            'error',
        );
    }
};

const onFileSelected = async (event) => {
    const file = event.target.files?.[0];
    if (!file) return;

    await stopScanner();
    await nextTick();

    html5QrCode = new Html5Qrcode(scannerId);
    setStatus('Memindai gambar...', 'info');
    foundArsip.value = null;

    try {
        const decodedText = await html5QrCode.scanFile(file, true);
        await lookupKode(decodedText);
    } catch {
        setStatus('Tidak ada QR terbaca pada gambar. Coba foto yang lebih jelas.', 'error');
    } finally {
        event.target.value = '';
    }
};

watch(
    () => [props.active, props.mode],
    async ([active, mode]) => {
        if (!active) {
            await stopScanner();
            statusMessage.value = '';
            foundArsip.value = null;
            return;
        }

        if (mode === 'webcam') {
            await startWebcam();
            return;
        }

        await stopScanner();
        setStatus('Pilih file gambar QR dari komputer.', 'info');
        foundArsip.value = null;
    },
    { immediate: true },
);

onBeforeUnmount(() => {
    stopScanner();
});
</script>

<template>
    <div class="space-y-4">
        <div
            :id="scannerId"
            class="overflow-hidden rounded-xl bg-slate-900"
            :class="mode === 'webcam' && active ? 'min-h-[260px]' : 'hidden'"
        />

        <div
            v-if="mode === 'upload'"
            class="flex min-h-[220px] flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 px-6 text-center"
        >
            <svg viewBox="0 0 24 24" class="mb-3 h-10 w-10 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="3" y="5" width="18" height="14" rx="2" />
                <circle cx="8.5" cy="10.5" r="1.5" />
                <path stroke-linecap="round" d="M21 16l-5-5-4 4-2-2-5 5" />
            </svg>
            <p class="text-sm font-medium text-slate-600">Pilih file gambar QR dari komputer</p>
            <input
                ref="fileInput"
                type="file"
                accept="image/*"
                class="mt-4 block w-full max-w-xs text-sm text-slate-500 file:mr-3 file:rounded-md file:border-0 file:bg-brand-blue file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white"
                @change="onFileSelected"
            />
        </div>

        <p
            v-if="statusMessage"
            class="rounded-lg px-3 py-2 text-center text-sm"
            :class="{
                'bg-slate-100 text-slate-600': statusType === 'info',
                'bg-emerald-50 text-emerald-700': statusType === 'success',
                'bg-rose-50 text-rose-700': statusType === 'error',
            }"
        >
            {{ statusMessage }}
        </p>

        <div
            v-if="foundArsip"
            class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-950"
        >
            <p class="font-bold text-emerald-800">{{ foundArsip.kode_arsip }}</p>
            <p class="mt-1">{{ foundArsip.nomor_surat }}</p>
            <p class="mt-1 text-emerald-800/80">{{ foundArsip.perihal }}</p>
            <p class="mt-2 capitalize">
                Status:
                <span class="font-semibold">{{ foundArsip.status_arsip }}</span>
                <span v-if="foundArsip.seksi"> • {{ foundArsip.seksi.nama_seksi }}</span>
            </p>
        </div>
    </div>
</template>
