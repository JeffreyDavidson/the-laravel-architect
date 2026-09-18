function initializeRevealAnimations(reduceMotion) {
    const revealElements = document.querySelectorAll('[data-reveal]');
    const countElements = document.querySelectorAll('[data-count-up]');

    revealElements.forEach(element => (element.dataset.reveal = 'pending'));

    if (reduceMotion || !('IntersectionObserver' in window)) {
        revealElements.forEach(element => (element.dataset.reveal = 'visible'));

        return;
    }

    const revealObserver = new IntersectionObserver(
        entries => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) {
                    return;
                }

                entry.target.dataset.reveal = 'visible';
                revealObserver.unobserve(entry.target);
            });
        },
        { threshold: 0.1 },
    );

    revealElements.forEach(element => revealObserver.observe(element));

    const countObserver = new IntersectionObserver(
        entries => {
            entries.forEach(entry => {
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
        },
        { threshold: 0.5 },
    );

    countElements.forEach(element => countObserver.observe(element));
}

function initializeHomepage() {
    const homepage = document.querySelector('[data-home-hero]');

    if (!homepage) {
        return;
    }

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    initializeRevealAnimations(reduceMotion);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeHomepage, { once: true });
} else {
    initializeHomepage();
}
