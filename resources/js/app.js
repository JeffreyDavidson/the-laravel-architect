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

    if (document.querySelector('[data-home-hero]')) {
        const { registerHomeReveal } = await import('./pages/home');
        registerHomeReveal(runtime.Alpine);
    }

    if (document.querySelector('[data-contact-form]')) {
        const { registerTurnstileWidget } = await import('./pages/contact');
        registerTurnstileWidget(runtime.Alpine);
    }

    if (document.querySelector('[data-newsletter-form]')) {
        const { registerNewsletterForm } = await import('./pages/newsletter');
        registerNewsletterForm(runtime.Alpine);
    }

    if (document.querySelector('[data-blog-filter]')) {
        await import('./pages/blog-index');
    }

    if (document.querySelector('[data-article]')) {
        const { initializeBlog } = await import('./pages/blog');
        initializeBlog();
    }

    if (document.querySelector('[data-podcast-copy-url], [data-youtube-facade], [data-transcript]')) {
        const { initializePodcast } = await import('./pages/podcast');
        initializePodcast(runtime.Alpine);
    }

    runtime.start();
}

initializePublicUi();

import.meta.glob('../images/**', {
    eager: true,
    query: '?url',
    import: 'default',
});
