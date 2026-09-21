import { registerSiteHeader } from './site-header';
import { registerCopyButton } from './copy-button';

async function initializePublicUi() {
    const runtime = window.livewireScriptConfig ? await import('./livewire') : await import('./alpine');

    registerSiteHeader(runtime.Alpine);
    registerCopyButton(runtime.Alpine);

    if (document.querySelector('[data-about-card]')) {
        const { registerAboutCard } = await import('./pages/about');
        registerAboutCard(runtime.Alpine);
    }

    if (document.querySelector('[data-blog-filter]')) {
        await import('./pages/blog-index');
    }

    if (document.querySelector('[data-article]')) {
        const { initializeBlog } = await import('./pages/blog');
        initializeBlog();
    }

    if (
        document.querySelector('[data-podcast-copy-url], [data-youtube-facade], [data-audio-player], [data-transcript]')
    ) {
        const { initializePodcast } = await import('./pages/podcast');
        initializePodcast(runtime.Alpine);
    }

    runtime.start();
}

initializePublicUi();

if (document.querySelector('[data-turnstile-widget]')) {
    import('./pages/contact');
}

if (document.querySelector('[data-home-hero]')) {
    import('./pages/home');
}

import.meta.glob('../images/**', {
    eager: true,
    query: '?url',
    import: 'default',
});
