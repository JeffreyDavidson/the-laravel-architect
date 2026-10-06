/**
 * Submits the newsletter confirmation form as soon as the page starts in a real
 * browser, so the email's button confirms in one click. Confirming still takes a
 * POST, so link scanners that only fetch the page confirm nothing, and the form's
 * own button stays as the fallback without JavaScript or if submitting fails.
 * The page is sent with Cache-Control: no-store; if a browser still restores it
 * from the back-forward cache, the button is enabled again instead of staying
 * stuck on "Confirming…".
 */
export function registerNewsletterConfirm(Alpine) {
    Alpine.data('newsletterConfirm', () => ({
        confirming: false,

        get notConfirming() {
            return !this.confirming;
        },
        init() {
            window.addEventListener('pageshow', event => {
                if (event.persisted) {
                    this.confirming = false;
                }
            });

            this.confirming = true;

            try {
                this.$root.requestSubmit();
            } catch {
                this.confirming = false;
            }
        },
    }));
}
