/**
 * Susun teks posisi fisik arsip dari data terstruktur:
 * Rak (nama/kode) · Box / Map / Slot · keterangan_posisi / catatan.
 * Return null bila tidak ada satupun bagian — pemakai menampilkan fallback sendiri.
 */
export function posisiLokasi(arsip) {
    const rak = arsip?.rak?.nama_rak || arsip?.rak?.kode_rak || '';
    const detail = [arsip?.nomor_box, arsip?.nomor_map, arsip?.nomor_slot]
        .filter(Boolean)
        .join(' / ');
    const ekstra = [arsip?.keterangan_posisi, arsip?.catatan]
        .filter(Boolean)
        .join(' · ');
    const bagian = [rak, detail, ekstra].filter(Boolean);

    return bagian.length ? bagian.join(' · ') : null;
}
