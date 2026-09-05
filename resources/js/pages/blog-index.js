const filter = document.querySelector('[data-blog-filter]');

if (filter) {
    const search = filter.querySelector('[data-blog-search]');
    const clear = filter.querySelector('[data-blog-clear]');
    const categoryButtons = [...filter.querySelectorAll('[data-blog-category]')];
    const posts = [...filter.querySelectorAll('[data-blog-post]')];
    const emptyState = filter.querySelector('[data-blog-empty]');
    const emptyQuery = filter.querySelector('[data-blog-empty-query]');
    const reset = filter.querySelector('[data-blog-reset]');
    let activeCategory = 'all';

    const update = () => {
        const query = search.value.toLocaleLowerCase();
        let visibleCount = 0;

        for (const post of posts) {
            const categoryMatches = activeCategory === 'all' || post.dataset.category === activeCategory;
            const searchMatches = query === '' || post.dataset.searchContent.includes(query);
            const isVisible = categoryMatches && searchMatches;

            post.hidden = !isVisible;

            if (isVisible) {
                post.dataset.visibleIndex = String(visibleCount);
                visibleCount += 1;
            } else {
                delete post.dataset.visibleIndex;
            }
        }

        clear.hidden = query === '';
        emptyState.hidden = visibleCount !== 0;
        emptyQuery.textContent = query || activeCategory;

        for (const button of categoryButtons) {
            const isActive = button.dataset.blogCategory === activeCategory;

            button.classList.toggle('active', isActive);
            button.setAttribute('aria-pressed', String(isActive));
        }
    };

    search.addEventListener('input', update);
    clear.addEventListener('click', () => {
        search.value = '';
        search.focus();
        update();
    });

    reset?.addEventListener('click', () => {
        activeCategory = 'all';
        search.value = '';
        search.focus();
        update();
    });

    for (const button of categoryButtons) {
        button.addEventListener('click', () => {
            activeCategory = button.dataset.blogCategory;
            update();
        });
    }

    update();
}
