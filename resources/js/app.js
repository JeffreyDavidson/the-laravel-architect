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

    if (document.querySelector('[data-newsletter-confirm]')) {
        const { registerNewsletterConfirm } = await import('./pages/newsletter-confirm');
        registerNewsletterConfirm(runtime.Alpine);
    }

    if (document.querySelector('[data-blog-filter]')) {
        const { registerBlogMetadata } = await import('./pages/blog-index');
        registerBlogMetadata(runtime.Alpine);
    }

    if (document.querySelector('[data-article]')) {
        const { registerBlogArticle } = await import('./pages/blog');
        registerBlogArticle(runtime.Alpine);
    }

    if (document.querySelector('[data-podcast-copy-url], [data-youtube-facade], [data-transcript]')) {
        const { registerPodcast } = await import('./pages/podcast');
        registerPodcast(runtime.Alpine);
    }

    runtime.start();
}

initializePublicUi();

import.meta.glob('../images/**', {
    eager: true,
    query: '?url',
    import: 'default',
});
