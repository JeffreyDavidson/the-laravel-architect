function initializePublicShell() {
    const menuButton = document.getElementById('mobile-menu-btn');
    const menu = document.getElementById('mobile-menu');
    const themeToggle = document.getElementById('theme-toggle');

    if (!menuButton || !menu || !themeToggle) {
        return;
    }

    function updateThemeControls() {
        const isDark = document.documentElement.classList.contains('dark');
        const themeColor = document.querySelector('meta[name="theme-color"]');

        document.getElementById('theme-icon-dark')?.classList.toggle('hidden', isDark);
        document.getElementById('theme-icon-light')?.classList.toggle('hidden', !isDark);
        document.querySelectorAll('.theme-toggle-label').forEach((label) => {
            label.textContent = isDark ? 'Light Mode' : 'Dark Mode';
        });

        if (themeColor) {
            themeColor.content = isDark ? '#0D1117' : '#ffffff';
        }
    }

    function toggleTheme() {
        const isDark = document.documentElement.classList.toggle('dark');

        localStorage.theme = isDark ? 'dark' : 'light';
        updateThemeControls();
    }

    menuButton.addEventListener('click', () => {
        const isOpen = !menu.classList.toggle('hidden');

        menuButton.setAttribute('aria-expanded', String(isOpen));
    });
    themeToggle.addEventListener('click', toggleTheme);
    document.querySelectorAll('.theme-toggle-mobile').forEach((button) => {
        button.addEventListener('click', toggleTheme);
    });
    updateThemeControls();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializePublicShell, { once: true });
} else {
    initializePublicShell();
}
