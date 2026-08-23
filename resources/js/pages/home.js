const codePanels = {
    routes: 'code-routes',
    architect: 'code-architect',
    test: 'code-test',
};

const codeTabs = {
    routes: 'tab-routes',
    architect: 'tab-architect',
    test: 'tab-test',
};

function switchCodeTab(activeTab) {
    Object.keys(codePanels).forEach((tab) => {
        const panel = document.getElementById(codePanels[tab]);
        const button = document.getElementById(codeTabs[tab]);

        if (!panel || !button) {
            return;
        }

        const isActive = tab === activeTab;

        panel.hidden = !isActive;
        button.setAttribute('aria-selected', String(isActive));
        button.tabIndex = isActive ? 0 : -1;
        button.classList.toggle('code-editor-tab', isActive);
        button.classList.toggle('code-editor-tab-inactive', !isActive);
        button.classList.toggle('text-gray-300', isActive);
        button.classList.toggle('text-gray-500', !isActive);
    });
}

function initializeCodeTabs() {
    const buttons = [...document.querySelectorAll('[data-code-tab]')];

    buttons.forEach((button, index) => {
        button.addEventListener('click', () => switchCodeTab(button.dataset.codeTab));
        button.addEventListener('keydown', (event) => {
            const keyDirections = {
                ArrowLeft: -1,
                ArrowRight: 1,
            };

            if (event.key === 'Home' || event.key === 'End') {
                event.preventDefault();
                const target = event.key === 'Home' ? buttons[0] : buttons.at(-1);
                switchCodeTab(target.dataset.codeTab);
                target.focus();

                return;
            }

            if (!keyDirections[event.key]) {
                return;
            }

            event.preventDefault();
            const target = buttons[(index + keyDirections[event.key] + buttons.length) % buttons.length];
            switchCodeTab(target.dataset.codeTab);
            target.focus();
        });
    });
}

function initializeRevealAnimations(reduceMotion) {
    const revealElements = document.querySelectorAll('.fade-up');
    const countElements = document.querySelectorAll('.count-up');

    if (reduceMotion || !('IntersectionObserver' in window)) {
        revealElements.forEach((element) => element.classList.add('visible'));

        return;
    }

    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) {
                return;
            }

            entry.target.classList.add('visible');
            revealObserver.unobserve(entry.target);
        });
    }, { threshold: 0.1 });

    revealElements.forEach((element) => revealObserver.observe(element));

    const countObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) {
                return;
            }

            const target = Number(entry.target.dataset.target);
            let current = 0;

            function step() {
                current += Math.ceil(target / 30);

                if (current >= target) {
                    entry.target.textContent = target;

                    return;
                }

                entry.target.textContent = current;
                requestAnimationFrame(step);
            }

            step();
            countObserver.unobserve(entry.target);
        });
    }, { threshold: 0.5 });

    countElements.forEach((element) => countObserver.observe(element));
}

function initializeHomepage() {
    if (!document.querySelector('[data-code-tab]')) {
        return;
    }

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    initializeCodeTabs();
    initializeRevealAnimations(reduceMotion);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeHomepage, { once: true });
} else {
    initializeHomepage();
}
