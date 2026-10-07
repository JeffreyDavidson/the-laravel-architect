import { registerSiteHeader } from './site-header';
import { registerCopyButton } from './copy-button';

// Each page module loads only when its data hook is on the page, and registers its Alpine components.
const pageModules = [
    ['[data-about-card]', () => import('./pages/about').then(module => module.registerAboutCard)],
    ['[data-home-reveal]', () => import('./pages/home').then(module => module.registerHomeReveal)],
    ['[data-contact-form]', () => import('./pages/contact').then(module => module.registerTurnstileWidget)],
    ['[data-newsletter-form]', () => import('./pages/newsletter').then(module => module.registerNewsletterForm)],
    [
        '[data-newsletter-confirm]',
        () => import('./pages/newsletter-confirm').then(module => module.registerNewsletterConfirm),
    ],
    ['[data-blog-filter]', () => import('./pages/blog-index').then(module => module.registerBlogMetadata)],
    ['[data-article]', () => import('./pages/blog').then(module => module.registerBlogArticle)],
    [
        '[data-podcast-copy-url], [data-youtube-facade], [data-transcript]',
        () => import('./pages/podcast').then(module => module.registerPodcast),
    ],
];

async function initializePublicUi() {
    const runtime = window.livewireScriptConfig ? await import('./livewire') : await import('./alpine');

    registerSiteHeader(runtime.Alpine);
    registerCopyButton(runtime.Alpine);

    for (const [selector, load] of pageModules) {
        if (document.querySelector(selector)) {
            const register = await load();
            register(runtime.Alpine);
        }
    }

    runtime.start();
}

initializePublicUi();

import.meta.glob('../images/**', {
    eager: true,
    query: '?url',
    import: 'default',
});
