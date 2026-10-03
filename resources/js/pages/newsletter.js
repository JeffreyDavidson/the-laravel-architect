const failures = {
    419: 'This page has expired. Reload it and try again.',
    429: 'Too many attempts. Please try again in a little while.',
};

export function registerNewsletterForm(Alpine) {
    Alpine.data('newsletterForm', () => ({
        busy: false,
        status: '',
        message: '',

        get hasMessage() {
            return this.status !== '';
        },
        get notSuccess() {
            return this.status !== 'success';
        },
        get notError() {
            return this.status !== 'error';
        },
        get ariaInvalid() {
            if (this.status === '') {
                return this.$root.dataset.invalid;
            }

            return this.status === 'error' ? 'true' : 'false';
        },
        get describedBy() {
            if (this.status === '') {
                return this.$root.dataset.describedBy;
            }

            return this.status === 'error' ? 'newsletter-email-error-live newsletter-privacy' : 'newsletter-privacy';
        },
        init() {
            this.$refs.form.dataset.ready = 'true';
        },
        show(status, message) {
            this.status = status;
            this.message = message;
        },
        async send() {
            if (this.busy) {
                return;
            }

            this.busy = true;

            try {
                const response = await fetch(this.$refs.form.action, {
                    method: 'POST',
                    body: new URLSearchParams(new FormData(this.$refs.form)),
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                const data = await response.json().catch(() => ({}));

                if (response.ok && typeof data.message === 'string') {
                    this.show('success', data.message);
                    this.$refs.form.reset();
                } else {
                    this.show(
                        'error',
                        data.errors?.email?.[0] ??
                            failures[response.status] ??
                            'Something went wrong. Please try again.',
                    );
                }
            } catch {
                this.show('error', 'We could not reach the server. Check your connection and try again.');
            } finally {
                this.busy = false;
            }
        },
    }));
}
