const form = document.querySelector('[data-newsletter-form]');

if (form) {
    const root = form.closest('#newsletter-form');
    const feedback = root.querySelector('[data-newsletter-feedback]');
    const email = form.querySelector('#newsletter-email');
    const button = form.querySelector('[type="submit"]');
    const failures = {
        419: 'This page has expired. Reload it and try again.',
        429: 'Too many attempts. Please try again in a little while.',
    };

    const show = (type, text) => {
        const banner = root
            .querySelector(`[data-newsletter-${type}-template]`)
            .content.firstElementChild.cloneNode(true);
        const failed = type === 'error';

        banner.textContent = text;
        feedback.replaceChildren(banner);
        email.setAttribute('aria-invalid', failed ? 'true' : 'false');
        email.setAttribute(
            'aria-describedby',
            failed ? 'newsletter-email-error newsletter-privacy' : 'newsletter-privacy',
        );
    };

    form.addEventListener('submit', async event => {
        event.preventDefault();

        if (form.dataset.busy === 'true') {
            return;
        }

        form.dataset.busy = 'true';
        button.disabled = true;

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new URLSearchParams(new FormData(form)),
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data = await response.json().catch(() => ({}));

            if (response.ok && typeof data.message === 'string') {
                show('success', data.message);
                form.reset();
            } else {
                show(
                    'error',
                    data.errors?.email?.[0] ?? failures[response.status] ?? 'Something went wrong. Please try again.',
                );
            }
        } catch {
            show('error', 'We could not reach the server. Check your connection and try again.');
        } finally {
            form.dataset.busy = 'false';
            button.disabled = false;
        }
    });

    form.dataset.ready = 'true';
}
