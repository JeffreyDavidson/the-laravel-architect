export function registerSiteHeader(Alpine) {
    Alpine.data('siteHeader', () => ({
        menuOpen: false,
        isDark: document.documentElement.classList.contains('dark'),

        get menuClosed() {
            return !this.menuOpen;
        },
        get isLight() {
            return !this.isDark;
        },
        get themeLabel() {
            return this.isDark ? 'Light Mode' : 'Dark Mode';
        },

        init() {
            this.updateThemeColor();
        },
        toggleMenu() {
            this.menuOpen = !this.menuOpen;
        },
        closeMenu() {
            if (!this.menuOpen) {
                return;
            }
            this.menuOpen = false;
            this.$refs.menuButton.focus();
        },
        toggleTheme() {
            this.isDark = !this.isDark;
            document.documentElement.classList.toggle('dark', this.isDark);
            try {
                localStorage.theme = this.isDark ? 'dark' : 'light';
            } catch {
                // The current page can still change theme when storage is unavailable.
            }
            this.updateThemeColor();
        },
        updateThemeColor() {
            const meta = document.querySelector('meta[name="theme-color"]');
            if (meta) {
                meta.content =
                    getComputedStyle(document.documentElement).getPropertyValue('--bg-primary').trim() || 'transparent';
            }
        },
    }));
}
