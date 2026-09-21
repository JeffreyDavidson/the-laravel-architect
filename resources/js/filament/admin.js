document.addEventListener('livewire:navigating', () => {
    const sidebar = document.querySelector('.fi-sidebar-nav');

    if (sidebar) {
        window.__sidebarScroll = sidebar.scrollTop;
    }
});

document.addEventListener('livewire:navigated', () => {
    const sidebar = document.querySelector('.fi-sidebar-nav');

    if (sidebar && window.__sidebarScroll) {
        sidebar.scrollTop = window.__sidebarScroll;
    }
});
