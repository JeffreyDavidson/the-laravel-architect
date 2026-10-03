export function registerHomeReveal(Alpine) {
    Alpine.data('homeReveal', () => ({
        revealObserver: null,
        countObserver: null,

        init() {
            const reveals = document.querySelectorAll('[data-reveal]');
            const counts = document.querySelectorAll('[data-count-up]');
            const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            reveals.forEach(element => (element.dataset.reveal = 'pending'));

            if (reduceMotion || !('IntersectionObserver' in window)) {
                reveals.forEach(element => (element.dataset.reveal = 'visible'));
                this.$root.dataset.ready = 'true';

                return;
            }

            this.revealObserver = new IntersectionObserver(
                entries => {
                    entries.forEach(entry => {
                        if (!entry.isIntersecting) {
                            return;
                        }

                        entry.target.dataset.reveal = 'visible';
                        this.revealObserver.unobserve(entry.target);
                    });
                },
                { threshold: 0.1 },
            );
            this.countObserver = new IntersectionObserver(
                entries => {
                    entries.forEach(entry => {
                        if (!entry.isIntersecting) {
                            return;
                        }

                        this.countUp(entry.target);
                        this.countObserver.unobserve(entry.target);
                    });
                },
                { threshold: 0.5 },
            );

            reveals.forEach(element => this.revealObserver.observe(element));
            counts.forEach(element => this.countObserver.observe(element));
            this.$root.dataset.ready = 'true';
        },
        destroy() {
            this.revealObserver?.disconnect();
            this.countObserver?.disconnect();
        },
        countUp(element) {
            const target = Number(element.dataset.target);
            let current = 0;

            const step = () => {
                current += Math.ceil(target / 30);

                if (current >= target) {
                    element.textContent = target;

                    return;
                }

                element.textContent = current;
                requestAnimationFrame(step);
            };

            step();
        },
    }));
}
