const filter = document.querySelector('[data-blog-filter]');

if (filter) {
    const search = filter.querySelector('[data-blog-search]');
    const clear = filter.querySelector('[data-blog-clear]');

    if (search && clear) {
        const updateClearVisibility = () => {
            clear.hidden = search.value.trim() === '';
        };

        search.addEventListener('input', updateClearVisibility);
        updateClearVisibility();
    }
}
