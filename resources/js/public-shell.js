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

    document.querySelectorAll('[data-fathom-event]').forEach(element => {
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
        },
        { once: true },
    );
} else {
    initializeFathomEvents();
}
