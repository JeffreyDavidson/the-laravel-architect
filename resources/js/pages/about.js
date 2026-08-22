function initializeAboutCard() {
    const container = document.querySelector('.about-card-flip-container');
    const card = document.querySelector('.about-card-flip');

    if (!container || !card) {
        return;
    }

    const maxTilt = 15;
    let flipCount = 0;
    let isAnimating = false;

    container.addEventListener('about-card-flip', () => {
        flipCount += 1;
        isAnimating = true;
        card.style.transition = 'transform 0.8s cubic-bezier(0.16, 1, 0.3, 1)';
        card.style.transform = `rotateX(0deg) rotateY(${flipCount * 180}deg) scale(1)`;
        window.setTimeout(() => {
            isAnimating = false;
        }, 800);
    });

    container.addEventListener('mousemove', (event) => {
        if (isAnimating) {
            return;
        }

        const rect = container.getBoundingClientRect();
        const x = (event.clientX - rect.left) / rect.width;
        const y = (event.clientY - rect.top) / rect.height;
        const tiltX = (0.5 - y) * maxTilt;
        const tiltY = (x - 0.5) * maxTilt;

        card.style.transition = 'transform 0.1s ease-out';
        card.style.transform = `rotateX(${tiltX}deg) rotateY(${flipCount * 180 + tiltY}deg) scale(1.02)`;
    });

    container.addEventListener('mouseleave', () => {
        if (isAnimating) {
            return;
        }

        card.style.transition = 'transform 0.6s cubic-bezier(0.16, 1, 0.3, 1)';
        card.style.transform = `rotateX(0deg) rotateY(${flipCount * 180}deg) scale(1)`;
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeAboutCard, { once: true });
} else {
    initializeAboutCard();
}
