import axios from 'axios';
import { computed, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';

// ===== State modular (singleton) =====
// Halaman /activity-log memanggil useActivityLogLive() untuk menampilkan
// log terbaru secara otomatis tanpa refresh. Mirip useStafOnline:
// satu timer global, guarded document.hidden, tidak double-poll.
//
// Catatan desain: `route()` tidak tersedia di modul JS biasa (hanya template
// global Vue via ZiggyVue plugin). Mengikuti pola useStafOnline.js:
// gunakan string URL langsung untuk pemanggilan axios dari non-component code.

const RECENT_URL = '/api/activity-log/recent';
const POLL_INTERVAL_MS = 10_000;

const newEntries = ref([]);
const lastPolled = ref(null);
const lastId = ref(0); // id terakhir yang sudah terlihat
let timer = null;
let warnedOnce = false;

function isAdmin() {
    return usePage().props.auth?.user?.role === 'admin';
}

// Normalisasi entry dari API (flat) ke bentuk yang sama dengan Inertia
// (nested user, metadata, old_values/new_values) agar template bisa
// memakai helper yang sama (actionBadge, targetLine, changedDetails, dsb.)
function normalizeEntry(entry) {
    return {
        id: entry.id,
        user: entry.user_name
            ? { name: entry.user_name, role: entry.user_role, email: entry.user_email }
            : null,
        action: entry.action,
        route_name: entry.route_name,
        description: entry.description,
        target_type: entry.target_type,
        target_id: entry.target_id,
        target_label: entry.target_label,
        ip_address: entry.ip_address,
        created_at: entry.created_at,
        // Tidak ada metadata/old_values/new_values dari endpoint ringan —
        // helper di template sudah handle null dengan aman.
        metadata: null,
        old_values: null,
        new_values: null,
    };
}

async function poll() {
    if (document.hidden) return;

    try {
        const params = new URLSearchParams();
        params.set('limit', '5');
        if (lastId.value > 0) params.set('since_id', String(lastId.value));

        const { data } = await axios.get(`${RECENT_URL}?${params.toString()}`);

        const entries = (data.entries ?? []).map(normalizeEntry);

        // Update lastId dari respons
        if (data.last_id) lastId.value = data.last_id;

        if (entries.length > 0) {
            // Prepend (terbaru di depan)
            newEntries.value = [...entries, ...newEntries.value];
            // Batasi panjang array agar tidak menumpuk
            if (newEntries.value.length > 20) {
                newEntries.value = newEntries.value.slice(0, 20);
            }
        }

        lastPolled.value = new Date();
    } catch (err) {
        if (!warnedOnce) {
            console.warn('[activity-log-live] poll gagal:', err?.message || err);
            warnedOnce = true;
        }
    }
}

function ensureTimer() {
    if (timer || !isAdmin()) return;
    poll();
    timer = setInterval(poll, POLL_INTERVAL_MS);
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') poll();
    });
}

/**
 * Reset newEntries — dipanggil Activity/Index.vue saat mount & saat apply filter
 * agar hanya menampilkan entry yang baru masuk setelah filter aktif.
 */
function resetNewEntries() {
    newEntries.value = [];
    lastId.value = 0;
}

export function useActivityLogLive() {
    ensureTimer();

    return {
        newEntries,
        lastPolled,
        resetNewEntries,
    };
}
