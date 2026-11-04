<script setup>
import Modal from '@/Components/Modal.vue';
import { institution } from '@/config/institution';
import { posisiLokasi } from '@/utils/posisiLokasi';
import { computed, ref } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    arsip: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close']);

const busy = ref(false);

const qrUrl = computed(() => {
    if (!props.arsip?.qr_code_path) {
        return null;
    }

    return `/storage/${props.arsip.qr_code_path}`;
});

// QR image error state
const qrImageError = ref(false);

const formatDate = (value) => (value ? String(value).substring(0, 10) : '—');

const seksiLabel = computed(() => {
    const nama = props.arsip?.seksi?.nama_seksi || '';
    const lower = nama.toLowerCase();

    if (lower.includes('kesejahteraan')) return 'KSR — Kesejahteraan Rakyat';
    if (lower.includes('ekonomi')) return 'EKB — Ekonomi & Pembangunan';
    if (lower.includes('pemerintahan')) return 'PEM — Pemerintahan';

    return nama || '—';
});

// Baris posisi fisik stiker: susun dari rak → box/map/slot → keterangan/catatan.
// Warnanya mengikuti ketersediaan — abu bila ada data, merah bila benar-benar kosong.
const posisi = computed(() => posisiLokasi(props.arsip));
const posisiTersedia = computed(() => Boolean(posisi.value));
const rakLabel = computed(() => posisi.value || 'Lokasi belum diisi');

const rakWarna = computed(() =>
    posisiTersedia.value
        ? { bg: '#f1f5f9', text: '#334155' } // slate
        : { bg: '#fff1f2', text: '#be123c' }, // rose (fallback)
);

const escapeHtml = (value) =>
    String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');

const labelDocumentHtml = () => {
    const a = props.arsip;
    const qr = qrUrl.value
        ? `<img src="${escapeHtml(qrUrl.value)}" alt="QR ${escapeHtml(a.kode_arsip)}" />`
        : `<div class="qr-missing">QR belum tersedia</div>`;
    const warn = rakWarna.value;

    return `<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <title>Label ${escapeHtml(a.kode_arsip)}</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    html, body {
      width: 100%;
      height: 100%;
      overflow: hidden;
    }
    @page {
      size: 100mm 62mm;
      margin: 0;
    }
    body {
      font-family: "Segoe UI", Tahoma, Arial, sans-serif;
      background: #fff;
      color: #0B1F3A;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }
    .label {
      width: 100mm;
      height: 62mm;
      max-height: 62mm;
      border: 1.2pt solid #0B1F3A;
      overflow: hidden;
      page-break-inside: avoid;
      break-inside: avoid;
      page-break-after: avoid;
      display: flex;
      flex-direction: column;
    }
    .head {
      background: #0B1F3A;
      color: #fff;
      text-align: center;
      padding: 2.2mm 2mm;
      flex-shrink: 0;
    }
    .head h1 {
      font-size: 8.5pt;
      letter-spacing: 0.06em;
      text-transform: uppercase;
      line-height: 1.15;
    }
    .head p {
      margin-top: 0.6mm;
      font-size: 6.5pt;
      color: #cbd5e1;
      line-height: 1.15;
    }
    .body {
      display: flex;
      gap: 2.5mm;
      padding: 2.5mm;
      align-items: center;
      flex: 1;
      min-height: 0;
      overflow: hidden;
    }
    .qr {
      width: 24mm;
      height: 24mm;
      border: 1pt solid #94a3b8;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #f8fafc;
      flex-shrink: 0;
    }
    .qr img {
      width: 100%;
      height: 100%;
      object-fit: contain;
    }
    .qr-missing {
      font-size: 6.5pt;
      color: #94a3b8;
      text-align: center;
      padding: 1.5mm;
    }
    .meta {
      flex: 1;
      min-width: 0;
      min-height: 0;
      border-left: 1pt solid #e2e8f0;
      padding-left: 2.5mm;
      overflow: hidden;
    }
    .kode {
      font-size: 10pt;
      font-weight: 700;
      line-height: 1.15;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .seksi {
      margin-top: 0.6mm;
      font-size: 7pt;
      color: #64748b;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .rak {
      display: inline-block;
      max-width: 100%;
      margin-top: 1.2mm;
      background: #fff1f2;
      color: #be123c;
      font-size: 7.5pt;
      font-weight: 700;
      padding: 0.5mm 1.2mm;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .nomor, .perihal {
      margin-top: 1mm;
      font-size: 7pt;
      color: #475569;
      line-height: 1.25;
      overflow: hidden;
      display: -webkit-box;
      -webkit-box-orient: vertical;
      -webkit-line-clamp: 2;
    }
    .nomor {
      -webkit-line-clamp: 1;
      white-space: nowrap;
      display: block;
      text-overflow: ellipsis;
    }
    .foot {
      border-top: 1pt dashed #cbd5e1;
      background: #f8fafc;
      text-align: center;
      padding: 1.4mm 2mm;
      font-size: 6.5pt;
      color: #64748b;
      flex-shrink: 0;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
  </style>
</head>
<body>
  <div class="label">
    <div class="head">
      <h1>Kelurahan Grogol Selatan</h1>
      <p>Kota Administrasi Jakarta Selatan · Tata Usaha &amp; Kearsipan</p>
    </div>
    <div class="body">
      <div class="qr">${qr}</div>
      <div class="meta">
        <div class="kode">${escapeHtml(a.kode_arsip)}</div>
        <div class="seksi">${escapeHtml(seksiLabel.value)}</div>
        <div class="rak" style="background:${warn.bg};color:${warn.text};">${escapeHtml(rakLabel.value)}</div>
        <div class="nomor">${escapeHtml(a.nomor_surat)} · ${escapeHtml(formatDate(a.tanggal_surat))}</div>
        <div class="perihal">${escapeHtml(a.perihal)}</div>
      </div>
    </div>
    <div class="foot">${institution.qrFooter} · ${institution.systemName}</div>
  </div>
</body>
</html>`;
};

const printLabel = () => {
    if (!props.arsip) return;

    // Cetak via iframe tersembunyi — tidak memicu pemblokir pop-up
    const existing = document.getElementById('siparsip-print-frame');
    if (existing) {
        existing.remove();
    }

    const iframe = document.createElement('iframe');
    iframe.id = 'siparsip-print-frame';
    iframe.setAttribute('aria-hidden', 'true');
    iframe.style.cssText =
        'position:fixed;right:0;bottom:0;width:0;height:0;border:0;opacity:0;pointer-events:none;';
    document.body.appendChild(iframe);

    const frameDoc = iframe.contentDocument || iframe.contentWindow?.document;
    if (!frameDoc) {
        alert('Browser tidak mendukung cetak label. Coba peramban lain atau unduh stiker PNG.');
        iframe.remove();
        return;
    }

    frameDoc.open();
    frameDoc.write(labelDocumentHtml());
    frameDoc.close();

    const frameWindow = iframe.contentWindow;
    let printed = false;

    const cleanup = () => {
        setTimeout(() => {
            if (iframe.parentNode) iframe.remove();
        }, 1000);
    };

    const triggerPrint = () => {
        if (printed) return;
        printed = true;
        try {
            frameWindow.focus();
            frameWindow.print();
        } catch (error) {
            console.error(error);
            alert('Gagal membuka dialog cetak. Silakan unduh stiker PNG sebagai alternatif.');
        } finally {
            cleanup();
        }
    };

    const img = frameDoc.querySelector('img');
    if (img && !img.complete) {
        img.onload = triggerPrint;
        img.onerror = triggerPrint;
        setTimeout(triggerPrint, 2000);
    } else {
        setTimeout(triggerPrint, 300);
    }
};

const loadImage = (src) =>
    new Promise((resolve, reject) => {
        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload = () => resolve(img);
        img.onerror = reject;
        img.src = src;
    });

const wrapText = (ctx, text, x, y, maxWidth, lineHeight, maxLines = 2) => {
    const words = String(text || '').split(/\s+/).filter(Boolean);
    let line = '';
    let lineCount = 0;
    let cursorY = y;

    for (let i = 0; i < words.length; i += 1) {
        const test = line ? `${line} ${words[i]}` : words[i];
        if (ctx.measureText(test).width > maxWidth && line) {
            ctx.fillText(line, x, cursorY);
            line = words[i];
            cursorY += lineHeight;
            lineCount += 1;
            if (lineCount >= maxLines - 1) {
                let rest = words.slice(i).join(' ');
                while (ctx.measureText(`${rest}…`).width > maxWidth && rest.length > 1) {
                    rest = rest.slice(0, -1);
                }
                ctx.fillText(`${rest}${rest.length < words.slice(i).join(' ').length ? '…' : ''}`, x, cursorY);
                return;
            }
        } else {
            line = test;
        }
    }

    if (line) ctx.fillText(line, x, cursorY);
};

const downloadPng = async () => {
    if (!props.arsip || !qrUrl.value || busy.value) return;

    busy.value = true;

    try {
        const a = props.arsip;
        const scale = 2;
        const width = 560;
        const height = 340;
        const canvas = document.createElement('canvas');
        canvas.width = width * scale;
        canvas.height = height * scale;
        const ctx = canvas.getContext('2d');
        ctx.scale(scale, scale);

        // Latar
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, width, height);
        ctx.strokeStyle = '#0B1F3A';
        ctx.lineWidth = 3;
        ctx.strokeRect(1.5, 1.5, width - 3, height - 3);

        // Header navy
        ctx.fillStyle = '#0B1F3A';
        ctx.fillRect(0, 0, width, 64);
        ctx.fillStyle = '#ffffff';
        ctx.font = 'bold 16px Segoe UI, Arial, sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText('KELURAHAN GROGOL SELATAN', width / 2, 28);
        ctx.font = '12px Segoe UI, Arial, sans-serif';
        ctx.fillStyle = '#cbd5e1';
        ctx.fillText('Kota Administrasi Jakarta Selatan · Tata Usaha & Kearsipan', width / 2, 48);

        // QR
        const qrBox = { x: 24, y: 84, size: 160 };
        ctx.fillStyle = '#f8fafc';
        ctx.fillRect(qrBox.x, qrBox.y, qrBox.size, qrBox.size);
        ctx.strokeStyle = '#94a3b8';
        ctx.lineWidth = 1.5;
        ctx.strokeRect(qrBox.x, qrBox.y, qrBox.size, qrBox.size);

        try {
            const qrImg = await loadImage(qrUrl.value);
            ctx.drawImage(qrImg, qrBox.x + 8, qrBox.y + 8, qrBox.size - 16, qrBox.size - 16);
        } catch {
            ctx.fillStyle = '#94a3b8';
            ctx.font = '12px Segoe UI, Arial, sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText('QR gagal dimuat', qrBox.x + qrBox.size / 2, qrBox.y + qrBox.size / 2);
        }

        // Meta
        const textX = 210;
        ctx.textAlign = 'left';
        ctx.fillStyle = '#0B1F3A';
        ctx.font = 'bold 22px Segoe UI, Arial, sans-serif';
        wrapText(ctx, a.kode_arsip, textX, 110, 320, 24, 1);

        ctx.fillStyle = '#64748b';
        ctx.font = '13px Segoe UI, Arial, sans-serif';
        wrapText(ctx, seksiLabel.value, textX, 138, 320, 16, 1);

        // Kotak posisi fisik — warna mengikuti ketersediaan data (slate/rose)
        ctx.font = 'bold 13px Segoe UI, Arial, sans-serif';
        let rakTeks = rakLabel.value;
        if (rakTeks.length > 40) rakTeks = `${rakTeks.slice(0, 39)}…`;
        const rakWarnaPng = rakWarna.value;
        const rakWidth = Math.min(340, ctx.measureText(rakTeks).width + 20);
        ctx.fillStyle = rakWarnaPng.bg;
        ctx.fillRect(textX, 152, rakWidth, 24);
        ctx.fillStyle = rakWarnaPng.text;
        ctx.fillText(rakTeks, textX + 10, 169);

        ctx.fillStyle = '#475569';
        ctx.font = '13px Segoe UI, Arial, sans-serif';
        wrapText(ctx, `${a.nomor_surat} · ${formatDate(a.tanggal_surat)}`, textX, 198, 320, 16, 1);
        wrapText(ctx, a.perihal, textX, 222, 320, 16, 2);

        // Footer
        ctx.fillStyle = '#f8fafc';
        ctx.fillRect(0, height - 42, width, 42);
        ctx.strokeStyle = '#cbd5e1';
        ctx.beginPath();
        ctx.setLineDash([4, 4]);
        ctx.moveTo(0, height - 42);
        ctx.lineTo(width, height - 42);
        ctx.stroke();
        ctx.setLineDash([]);
        ctx.fillStyle = '#64748b';
        ctx.font = '12px Segoe UI, Arial, sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText(`${institution.qrFooter} · ${institution.systemName}`, width / 2, height - 18);

        const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/png'));
        if (!blob) throw new Error('Gagal membuat PNG');

        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `${a.kode_arsip}-label-stiker.png`;
        link.click();
        URL.revokeObjectURL(url);
    } catch (error) {
        console.error(error);
        alert('Gagal mengunduh label stiker. Coba muat ulang halaman.');
    } finally {
        busy.value = false;
    }
};
</script>

<template>
    <Modal :show="show" max-width="lg" @close="emit('close')">
        <div v-if="arsip">
            <div class="relative overflow-hidden border-b border-slate-200 bg-gradient-to-r from-brand-navy to-[#16355c] px-6 py-5 text-white">
                <div class="pointer-events-none absolute -right-8 -top-8 h-28 w-28 rounded-full bg-white/5" />
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <img
                            :src="institution.logoPath"
                            :alt="`Logo ${institution.name}`"
                            class="h-11 w-11 shrink-0 rounded-full bg-white object-contain p-0.5 shadow-sm"
                        />
                        <div>
                            <p class="text-xs font-medium text-sky-200/90">
                                {{ institution.systemName }}
                            </p>
                            <h2 class="mt-0.5 text-lg font-bold tracking-tight">
                                Label Stiker Berkas
                            </h2>
                            <p class="mt-0.5 text-xs text-slate-300">
                                Unduh sebagai gambar stiker atau cetak ke printer
                            </p>
                        </div>
                    </div>
                    <button
                        type="button"
                        class="rounded-lg p-1.5 text-slate-300 transition hover:bg-white/10 hover:text-white focus:outline-none focus:ring-2 focus:ring-white/30"
                        aria-label="Tutup"
                        @click="emit('close')"
                    >
                        <AppIcon name="close" :size="20" :stroke-width="2" class="h-5 w-5" decorative />
                    </button>
                </div>
            </div>

            <div class="bg-[#EEF2F7] px-6 py-6">
                <p class="mb-3 text-center text-[11px] font-medium uppercase tracking-wider text-slate-500">
                    Pratinjau stiker resmi
                </p>

                <div
                    id="stiker-label"
                    class="mx-auto max-w-md overflow-hidden rounded-2xl border border-slate-300 bg-white shadow-md shadow-slate-200/80"
                >
                    <div class="bg-brand-navy px-4 py-2.5 text-center text-white">
                        <h3 class="text-[11px] font-bold uppercase tracking-[0.12em]">
                            Kelurahan Grogol Selatan
                        </h3>
                        <p class="mt-0.5 text-[10px] text-slate-300">
                            Kota Administrasi Jakarta Selatan · Tata Usaha &amp; Kearsipan
                        </p>
                    </div>

                    <div class="flex items-stretch gap-4 px-4 py-4">
                        <div class="flex h-28 w-28 shrink-0 items-center justify-center rounded-xl border-2 border-slate-200 bg-slate-50 p-1.5">
                            <img
                                v-if="qrUrl"
                                :src="qrUrl"
                                @error="qrImageError = true"
                                :alt="`QR ${arsip.kode_arsip}`"
                                class="h-full w-full object-contain"
                            />
                            <span v-else class="px-2 text-center text-[10px] leading-snug text-slate-400">
                                QR belum tersedia
                            </span>
                        </div>

                        <div class="min-w-0 flex-1 border-l border-slate-100 pl-4">
                            <p class="truncate text-base font-bold tracking-tight text-brand-navy">
                                {{ arsip.kode_arsip }}
                            </p>
                            <p class="mt-1 text-[11px] font-medium text-slate-500">{{ seksiLabel }}</p>
                            <div
                                class="mt-2 inline-flex max-w-full items-center rounded-md px-2 py-0.5"
                                :class="posisiTersedia ? 'bg-slate-100' : 'bg-rose-50'"
                            >
                                <span
                                    class="truncate text-xs font-bold"
                                    :class="posisiTersedia ? 'text-slate-700' : 'text-rose-700'"
                                >{{ rakLabel }}</span>
                            </div>
                            <p class="mt-2 line-clamp-1 text-[11px] text-slate-600">
                                {{ arsip.nomor_surat }} · {{ formatDate(arsip.tanggal_surat) }}
                            </p>
                            <p class="mt-0.5 line-clamp-2 text-[11px] leading-snug text-slate-500">
                                {{ arsip.perihal }}
                            </p>
                        </div>
                    </div>

                    <div class="border-t border-dashed border-slate-200 bg-slate-50 px-4 py-2 text-center">
                        <p class="text-[10px] font-medium text-slate-500">
                            {{ institution.qrFooter }} · {{ institution.systemName }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-200 bg-white px-6 py-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs leading-relaxed text-slate-500">
                        Unduh menghasilkan PNG stiker lengkap. Cetak membuka jendela label khusus (1 halaman).
                    </p>
                    <div class="flex flex-wrap items-center justify-end gap-2.5">
                        <button
                            type="button"
                            class="group inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-brand-navy shadow-sm transition hover:border-brand-navy/30 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-brand-blue/30 disabled:cursor-not-allowed disabled:opacity-50"
                            :disabled="!qrUrl || busy"
                            title="Unduh label stiker PNG"
                            @click="downloadPng"
                        >
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-brand-navy transition group-hover:bg-brand-navy group-hover:text-white">
                                <AppIcon name="download" :size="16" :stroke-width="1.8" class="h-4 w-4" decorative />
                            </span>
                            <span class="text-left leading-tight">
                                <span class="block text-sm">{{ busy ? 'Menyiapkan…' : 'Unduh Stiker' }}</span>
                                <span class="block text-[10px] font-medium text-slate-400">PNG siap tempel</span>
                            </span>
                        </button>

                        <button
                            type="button"
                            class="group inline-flex items-center gap-2 rounded-xl bg-brand-navy px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-brand-navy/25 transition hover:bg-[#122a4a] focus:outline-none focus:ring-2 focus:ring-brand-blue/40 focus:ring-offset-2"
                            title="Cetak label ke printer"
                            @click="printLabel"
                        >
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-white/15 transition group-hover:bg-white/25">
                                <AppIcon name="print" :size="16" :stroke-width="1.8" class="h-4 w-4" decorative />
                            </span>
                            <span class="text-left leading-tight">
                                <span class="block text-sm">Cetak Printer</span>
                                <span class="block text-[10px] font-medium text-sky-200/80">1 halaman label</span>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Modal>
</template>
