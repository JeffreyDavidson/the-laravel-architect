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
        document.querySelectorAll('[data-theme-toggle]').forEach((toggle) => {
            toggle.setAttribute('aria-pressed', String(isDark));
        });

        if (themeColor) {
            themeColor.content =
                getComputedStyle(document.documentElement).getPropertyValue('--bg-primary').trim() || 'transparent';
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

function trackFathomEvent(eventName) {
    if (typeof window.fathom?.trackEvent === 'function') {
        window.fathom.trackEvent(eventName);

        return;
    }

    if (document.readyState !== 'complete') {
        window.addEventListener('load', () => trackFathomEvent(eventName), { once: true });
    }
}

function initializeFathomEvents() {
    const pageLoadEvent = document.body.dataset.fathomEventOnLoad;

    if (pageLoadEvent) {
        trackFathomEvent(pageLoadEvent);
    }

    document.querySelectorAll('[data-fathom-event]').forEach((element) => {
        element.addEventListener('click', () => {
            const eventName = element.getAttribute('data-fathom-event');

            if (eventName) {
                trackFathomEvent(eventName);
            }
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener(
        'DOMContentLoaded',
        () => {
            initializeFathomEvents();
            initializePublicShell();
        },
        { once: true },
    );
} else {
    initializeFathomEvents();
    initializePublicShell();
}
