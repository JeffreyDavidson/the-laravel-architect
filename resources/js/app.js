import Alpine from 'alpinejs';

import './bootstrap';
import './pages/about';
import './pages/blog';
import './pages/podcast';
import './public-shell';

if (document.querySelector('.home-page')) {
    import('./pages/home');
}

import.meta.glob('../images/**', {
    eager: true,
    query: '?url',
    import: 'default',
});

window.Alpine = Alpine;

Alpine.start();
