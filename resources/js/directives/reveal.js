const DURATION_MS = 700;
const EASE = 'cubic-bezier(0.16, 1, 0.3, 1)';
const HIDDEN_TRANSFORM = 'translateY(1rem)';

/**
 * v-reveal — fade-up on scroll via IntersectionObserver.
 *
 * Usage:  <div v-reveal>...</div>
 * Stagger: <div v-reveal :style="{ transitionDelay: `${index * 100}ms` }">
 *
 * Elemen berawal invisible (opacity 0 + translate-y) selama belum masuk
 * viewport; saat intersect, class Tailwind transition ditambahkan lalu
 * inline style dihapus, sehingga CSS class menentukan state akhir dan
 * hover/interaction lainnya tetap berfungsi normal.
 */
export const reveal = {
    mounted(el, binding) {
        // Jika IntersectionObserver tak tersedia, biarkan elemen tampil
        // normal tanpa animasi (graceful degradation).
        if (typeof window.IntersectionObserver === 'undefined') return;

        // Respect prefers-reduced-motion: skip animasi, langsung visible.
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return;
        }

        const delay = binding.value ? parseInt(binding.value, 10) || 0 : 0;

        el.style.opacity = '0';
        el.style.transform = HIDDEN_TRANSFORM;
        el.style.willChange = 'opacity, transform';

        const observer = new IntersectionObserver(
            (entries, obs) => {
                for (const entry of entries) {
                    if (entry.isIntersecting) {
                        obs.unobserve(el);

                        // Tambahkan transition class, lalu lepaskan inline
                        // style agar CSS class menentukan state final.
                        el.classList.add('transition-all', 'duration-700', 'ease-out');
                        el.style.transition = `all ${DURATION_MS}ms ${EASE}`;

                        // Next frame: set visible.
                        requestAnimationFrame(() => {
                            el.style.opacity = '1';
                            el.style.transform = 'translateY(0)';

                            // Setelah animasi selesai, hapus semua inline
                            // style agar hover & interaction tidak terblokir
                            // oleh specificity inline > class.
                            setTimeout(() => {
                                el.style.transition = '';
                                el.style.opacity = '';
                                el.style.transform = '';
                                el.style.transitionDelay = '';
                                el.style.willChange = '';
                                el.classList.remove(
                                    'transition-all',
                                    'duration-700',
                                    'ease-out',
                                );
                            }, DURATION_MS + delay + 50);
                        });
                    }
                }
            },
            { threshold: 0.15 },
        );

        el._revealObserver = observer;
        observer.observe(el);
    },

    unmounted(el) {
        if (el._revealObserver) {
            el._revealObserver.disconnect();
            el._revealObserver = null;
        }
        el.style.transition = '';
        el.style.opacity = '';
        el.style.transform = '';
        el.style.transitionDelay = '';
        el.style.willChange = '';
        el.classList.remove('transition-all', 'duration-700', 'ease-out');
    },
};
