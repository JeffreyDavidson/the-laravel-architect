import Alpine from 'alpinejs';

import './bootstrap';
import './pages/about';
import './pages/blog';
import './pages/home';
import './pages/podcast';
import './public-shell';

import.meta.glob('../images/**', {
    eager: true,
    query: '?url',
    import: 'default',
});

window.Alpine = Alpine;

Alpine.start();
