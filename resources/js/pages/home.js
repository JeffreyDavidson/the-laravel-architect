function initializeRevealAnimations(reduceMotion) {
    const revealElements = document.querySelectorAll('.fade-up');
    const countElements = document.querySelectorAll('.count-up');

    revealElements.forEach((element) => element.classList.add('reveal-pending'));

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

async function initializeArchitectureScene(reduceMotion) {
    const sceneElement = document.querySelector('[data-architecture-scene]');
    const canvasElement = sceneElement?.querySelector('[data-architecture-canvas]');

    if (!sceneElement || !canvasElement) {
        return;
    }

    if (reduceMotion) {
        sceneElement.dataset.architectureState = 'fallback';

        return;
    }

    try {
        const { mountArchitectureScene } = await import('./architecture-scene');

        mountArchitectureScene(sceneElement, canvasElement);
        sceneElement.dataset.architectureState = 'ready';
    } catch {
        sceneElement.dataset.architectureState = 'fallback';
    }
}

function scheduleArchitectureScene(reduceMotion) {
    if (reduceMotion) {
        initializeArchitectureScene(reduceMotion);

        return;
    }

    if ('requestIdleCallback' in window) {
        window.requestIdleCallback(() => initializeArchitectureScene(false), { timeout: 1500 });

        return;
    }

    window.setTimeout(() => initializeArchitectureScene(false));
}

function initializeHomepage() {
    const homepage = document.querySelector('[data-architecture-scene]');

    if (!homepage) {
        return;
    }

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    scheduleArchitectureScene(reduceMotion);
    initializeRevealAnimations(reduceMotion);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeHomepage, { once: true });
} else {
    initializeHomepage();
}
