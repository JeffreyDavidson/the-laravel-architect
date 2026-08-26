import './public-shell';

if (document.querySelector('[x-data]')) {
    import('./alpine');
}

if (document.querySelector('.about-card-flip-container')) {
    import('./pages/about');
}

if (document.querySelector('.prose pre')) {
    import('./pages/blog');
}

if (document.querySelector('[data-podcast-copy-url]')) {
    import('./pages/podcast');
}

if (document.querySelector('.home-page')) {
    import('./pages/home');
}

import.meta.glob('../images/**', {
    eager: true,
    query: '?url',
    import: 'default',
});
