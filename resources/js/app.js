import './public-shell';

if (document.querySelector('.about-card-flip-container')) {
    import('./pages/about');
}

if (document.querySelector('[data-blog-filter]')) {
    import('./pages/blog-index');
}

if (document.querySelector('.prose pre')) {
    import('./pages/blog');
}

if (document.querySelector('[data-podcast-copy-url], [data-youtube-facade], [data-audio-player]')) {
    import('./pages/podcast');
}

if (document.querySelector('[data-turnstile-widget]')) {
    import('./pages/contact');
}

if (document.querySelector('.home-page')) {
    import('./pages/home');
}

import.meta.glob('../images/**', {
    eager: true,
    query: '?url',
    import: 'default',
});
