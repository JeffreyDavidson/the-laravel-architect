export function registerTurnstileWidget(Alpine) {
    Alpine.data('turnstileWidget', () => ({
        loading: false,
        observer: null,

        init() {
            if (!this.$refs.widget) {
                return;
            }

            if (!('IntersectionObserver' in window)) {
                this.load();

                return;
            }

            this.observer = new IntersectionObserver(
                entries => {
                    if (entries.some(entry => entry.isIntersecting)) {
                        this.load();
                    }
                },
                { rootMargin: '300px' },
            );
            this.observer.observe(this.$refs.widget);
            this.$root.dataset.ready = 'true';
        },
        destroy() {
            this.observer?.disconnect();
        },
        load() {
            const widget = this.$refs.widget;

            if (this.loading || !widget) {
                return;
            }

            this.loading = true;
            this.observer?.disconnect();

            const script = document.createElement('script');
            script.src = 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit';
            script.async = true;
            script.defer = true;
            script.addEventListener(
                'load',
                () =>
                    window.turnstile.render(widget, {
                        sitekey: widget.dataset.sitekey,
                        action: widget.dataset.action,
                        size: 'flexible',
                    }),
                { once: true },
            );
            document.head.append(script);
        },
    }));
}
