import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

// ===== Auto-logout / Session Timeout karena inaktivitas (idle) =====
// Catatan desain:
// - Module-scope singleton: satu timer & satu lastActivityTime untuk semua
//   instance composable. AppLayout memanggil useAutoLogout() sekali → cukup.
// - Timer menggunakan setTimeout + interval timestamp check (bukan hanya
//   setTimeout) untuk mengatasi Sleep Mode Windows: saat komputer masuk
//   sleep, setTimeout bisa "membeku". Timestamp-based check per menit
//   memperbaiki ini.
// - lastActivityTime disimpan di localStorage agar tetap ada walau page
//   reload, tapi tetap reset saat user beraktivitas kembali.
// - Event listener dipasang ke window; document.hidden guard agar timer
//   tidak memaksa logout saat tab sedang di-background tapi user masih
//   aktif di tab lain (opsional: bisa dimatikan jika ingin lebih ketat).
// - `route()` tidak tersedia di modul JS biasa (hanya template global Vue
//   via ZiggyVue plugin). Mengikuti pola useStafOnline.js: gunakan string
//   URL langsung untuk pemanggilan axios dari non-component code.
// - Flash message: setelah logout sukses, Inertia akan me-render halaman
//   login dengan flash message yang dikirim oleh controller.

const LOGOUT_URL = '/logout';

const IDLE_THRESHOLD_MS = 15 * 60 * 1000; // 15 menit
const CHECK_INTERVAL_MS = 60_000;        // cek setiap 1 menit
const STORAGE_KEY = 'idleLastActivity';

let idleTimeout = null;
let checkInterval = null;
let lastActivityTime = Date.now();
let warnedOnce = false;
const logoutTriggered = ref(false);

/** Reset timer idle — dipanggil tiap user beraktivitas. */
function resetIdleTimer() {
    if (idleTimeout) clearTimeout(idleTimeout);
    idleTimeout = setTimeout(() => {
        forceLogout('timeout');
    }, IDLE_THRESHOLD_MS);
}

/** Pengecekan berbasis timestamp (sleep mode fix). */
function checkTimestamp() {
    if (document.hidden) return;
    const elapsed = Date.now() - lastActivityTime;
    if (elapsed > IDLE_THRESHOLD_MS && !logoutTriggered.value) {
        forceLogout('timestamp');
    }
}

/** Eksekusi logout paksa via Inertia router.post. */
function forceLogout(reason) {
    if (logoutTriggered.value) return;
    logoutTriggered.value = true;
    if (idleTimeout) clearTimeout(idleTimeout);
    if (checkInterval) clearInterval(checkInterval);

    // Post ke /logout (AuthSessionController::destroy) — controller akan
    // invalidate session dan catat ActivityLog.auth.logout
    router.post(LOGOUT_URL, {}, {
        onSuccess: () => {
            // Setelah berhasil, arahkan ke halaman login
            window.location.href = '/login';
        },
        onError: () => {
            // Fallback: langsung redirect jika post gagal
            console.warn('[auto-logout] router.post logout gagal, fallback redirect', reason);
            window.location.href = '/login';
        },
        onFinish: () => {
            logoutTriggered.value = false;
        },
    });
}

/** Update lastActivityTime dan reset timer. */
function onActivity() {
    lastActivityTime = Date.now();
    try {
        localStorage.setItem(STORAGE_KEY, String(lastActivityTime));
    } catch {
        // ignore
    }
    resetIdleTimer();
}

/** Daftar event yang menandakan aktivitas user. */
const ACTIVITY_EVENTS = ['mousemove', 'keydown', 'scroll', 'click', 'touchstart', 'pointerdown'];

function start() {
    // Ambil lastActivityTime dari localStorage jika ada
    try {
        const stored = localStorage.getItem(STORAGE_KEY);
        if (stored) {
            lastActivityTime = parseInt(stored, 10) || Date.now();
        }
    } catch {
        // ignore
    }

    // Daftarkan event listener
    ACTIVITY_EVENTS.forEach((evt) => {
        window.addEventListener(evt, onActivity, { passive: true });
    });

    // visibilitychange: reset timer saat tab kembali aktif
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
            onActivity();
        }
    });

    // Interval pengecekan timestamp (sleep mode fix)
    checkInterval = setInterval(checkTimestamp, CHECK_INTERVAL_MS);

    // Set timer awal
    resetIdleTimer();

    if (!warnedOnce) {
        console.info(`[auto-logout] aktif — threshold ${IDLE_THRESHOLD_MS / 1000 / 60} menit`);
        warnedOnce = true;
    }
}

function stop() {
    if (idleTimeout) clearTimeout(idleTimeout);
    if (checkInterval) clearInterval(checkInterval);
    ACTIVITY_EVENTS.forEach((evt) => {
        window.removeEventListener(evt, onActivity);
    });
    document.removeEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') onActivity();
    });
    logoutTriggered.value = false;
}

export function useAutoLogout() {
    start();

    // Kembalikan objek yang bisa dipakai komponen (misal untuk debug)
    return {
        lastActivityTime,
        logoutTriggered,
        stop, // exposed untuk onUnmounted jika diperlukan
    };
}
