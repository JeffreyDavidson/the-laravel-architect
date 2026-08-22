import './bootstrap';
import './pages/home';

import.meta.glob('../images/**', {
    eager: true,
    query: '?url',
    import: 'default',
});
