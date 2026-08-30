const widget = document.querySelector('[data-turnstile-widget]');

if (widget) {
    const form = widget.closest('form');
    let isLoading = false;

    const renderWidget = () => {
        window.turnstile.render(widget, {
            sitekey: widget.dataset.sitekey,
            action: widget.dataset.action,
            size: 'flexible',
        });
    };

    const loadWidget = () => {
        if (isLoading) {
            return;
        }

        isLoading = true;

        const script = document.createElement('script');
        script.src = 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit';
        script.async = true;
        script.defer = true;
        script.addEventListener('load', renderWidget, { once: true });
        document.head.append(script);
    };

    form?.addEventListener('focusin', loadWidget, { once: true });
    form?.addEventListener('pointerdown', loadWidget, { once: true });

    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            if (!entries.some((entry) => entry.isIntersecting)) {
                return;
            }

            observer.disconnect();
            loadWidget();
        }, { rootMargin: '300px' });

        observer.observe(widget);
    } else {
        loadWidget();
    }
}
