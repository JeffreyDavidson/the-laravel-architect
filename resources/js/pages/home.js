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

        panel.classList.toggle('hidden', !isActive);
        button.classList.toggle('code-editor-tab', isActive);
        button.classList.toggle('code-editor-tab-inactive', !isActive);
        button.classList.toggle('text-gray-300', isActive);
        button.classList.toggle('text-gray-500', !isActive);
    });
}

function initializeCodeTabs() {
    document.querySelectorAll('[data-code-tab]').forEach((button) => {
        button.addEventListener('click', () => switchCodeTab(button.dataset.codeTab));
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
