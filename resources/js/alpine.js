import Alpine from '@alpinejs/csp';

export { Alpine };

export function start() {
    window.Alpine = Alpine;
    Alpine.start();
}
