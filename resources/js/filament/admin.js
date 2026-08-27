const expandSidebarGroups = () => {
    window.localStorage.removeItem('collapsedGroups');

    document.querySelectorAll('.fi-sidebar-group').forEach((group) => {
        group.classList.remove('fi-collapsed');

        const items = group.querySelector('.fi-sidebar-group-items');

        if (!items) {
            return;
        }

        items.style.display = '';
        items.style.height = '';
        items.style.maxHeight = '';
    });

    const sidebar = window.Alpine?.store('sidebar');

    if (sidebar) {
        sidebar.collapsedGroups = [];
    }
};

const expandSidebarGroupsAfterRender = () => {
    window.requestAnimationFrame(expandSidebarGroups);
};

document.addEventListener('alpine:initialized', expandSidebarGroupsAfterRender, { once: true });
document.addEventListener('livewire:navigated', expandSidebarGroupsAfterRender);

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

expandSidebarGroupsAfterRender();
