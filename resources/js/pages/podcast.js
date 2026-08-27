const copyIcon = '<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>';
const copiedIcon = '<svg class="h-4 w-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';

function initializePodcastCopyButton() {
    const button = document.querySelector('[data-podcast-copy-url]');

    if (!button) {
        return;
    }

    button.addEventListener('click', async () => {
        await navigator.clipboard.writeText(button.dataset.podcastCopyUrl);
        button.innerHTML = copiedIcon;
        button.setAttribute('aria-label', 'Episode link copied');

        window.setTimeout(() => {
            button.innerHTML = copyIcon;
            button.setAttribute('aria-label', 'Copy episode link');
        }, 2000);
    });
}

function initializeYoutubeFacades() {
    document.querySelectorAll('[data-youtube-facade]').forEach((facade) => {
        const button = facade.querySelector('[data-youtube-play]');
        const template = facade.querySelector('[data-youtube-player]');

        if (!button || !(template instanceof HTMLTemplateElement)) {
            return;
        }

        button.addEventListener('click', () => {
            template.replaceWith(template.content.cloneNode(true));
            button.remove();
        }, { once: true });
    });
}

function initializePodcastPage() {
    initializePodcastCopyButton();
    initializeYoutubeFacades();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializePodcastPage, { once: true });
} else {
    initializePodcastPage();
}
