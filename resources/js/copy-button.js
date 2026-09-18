import { copyText } from './utils/clipboard';

export function registerCopyButton(Alpine) {
    Alpine.data('copyButton', () => ({
        copied: false,
        failed: false,
        resetTimer: null,

        get label() {
            if (this.failed) {
                return 'Copy failed';
            }
            return this.copied ? this.$el.dataset.copySuccess : this.$el.dataset.copyLabel;
        },
        get notCopied() {
            return !this.copied;
        },
        get buttonClasses() {
            return { copied: this.copied };
        },
        async copy() {
            clearTimeout(this.resetTimer);
            const text = this.$el.dataset.copyText ?? this.$el.closest('pre')?.querySelector('code')?.innerText ?? '';
            this.copied = await copyText(text);
            this.failed = !this.copied;
            this.resetTimer = setTimeout(() => {
                this.copied = false;
                this.failed = false;
            }, 2000);
        },
        destroy() {
            clearTimeout(this.resetTimer);
        },
    }));
}
