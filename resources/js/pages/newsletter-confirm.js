/**
 * Submits the newsletter confirmation form as soon as the page starts in a real
 * browser, so the email's button confirms in one click. The page is rendered in
 * its "Confirming…" state from the first paint, so nothing on it changes before
 * the browser moves on to the confirmed page. Confirming still takes a POST, so
 * link scanners that only fetch the page confirm nothing. Without JavaScript the
 * form's button sits in a noscript block; with it, the component reveals the
 * button only if submitting throws or the page is still here after five seconds.
 * The page is sent with Cache-Control: no-store; if a browser still restores it
 * from the back-forward cache, the button is shown instead of a stuck spinner.
 */
export function registerNewsletterConfirm(Alpine) {
    Alpine.data('newsletterConfirm', () => ({
        fallbackShown: false,

        get fallbackHidden() {
            return !this.fallbackShown;
        },
        init() {
            window.addEventListener('pageshow', event => {
                if (event.persisted) {
                    this.fallbackShown = true;
                }
            });

            window.setTimeout(() => {
                this.fallbackShown = true;
            }, 5000);

            try {
                this.$root.requestSubmit();
            } catch {
                this.fallbackShown = true;
            }
        },
    }));
}
