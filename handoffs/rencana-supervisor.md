# Rencana supervisor (review otomatis adaCODE)
Status: BERJALAN · Sumber: review APPROVED (claude) · Diperbarui: 2026-10-26 23:01
<!-- adacode:rencana-supervisor — ditulis ulang tiap review; catatan Anda sendiri taruh di handoff lain -->

## Tugas terakhir
lakukan implementasi screen skeleton juga pada bagian login sisi biru dan putih

## Ringkasan review
Round-1 correction is substantively correct. F1 (medium) is closed on the evidence I read: GuestLayout.vue:9-34 now carries the full timer idiom the previous review demanded — `let skeletonTimer = null;` (line 11), `clearTimeout` inside `armSkeleton` (line 14), `skeletonTimer = null` on fire (line 17), the `if (isLoading.value) return;` guard in the `page.url` watcher (line 26), and `onBeforeUnmount` cleanup (lines 32-34) — structurally identical to PublicLayout.vue:19-44. The feature itself is present on both panels the user asked for: the blue left panel has a dedicated skeleton aside (Guest

## Langkah berikutnya — JUNIOR
- [ ] (sedang) Apply the two placeholder-metric tweaks in GuestLayout.vue (line 122 to `h-11 w-16`, line 139 to `mx-auto h-3 w-48`) so the Beranda link and copyright placeholder match their real counterparts.
- [ ] (sedang) Add the white-panel grep counts (`bg-slate-200` → 3 in GuestLayout.vue, `bg-slate-100` → 4, Login.vue `bg-slate-200|bg-slate-100` → 6) to Fix #70's Verifikasi cell in CHANGELOG_FIXES.md.

## Langkah berikutnya — SENIOR (verifikasi)
- [ ] (tinggi) Verify in a browser that /login shows the blue left-panel skeleton and the white right-panel skeleton for ~1s, then swaps to the real form without a vertical/horizontal jump (record one evidence line per panel as required by R-35).
- [ ] (rendah) Re-check the skeleton→content transition on the remaining guest pages (ForgotPassword, ResetPassword, ConfirmPassword, VerifyEmail) under `prefers-reduced-motion: reduce` to confirm the inherited fallback skeleton does not pulse there.

## Langkah berikutnya — PENGGUNA (keputusan/akses)
- [ ] (rendah) Register a guest test account or confirm the dev server can be reached so the next UI audit can actually open /login (currently the audit opened no page at all).
