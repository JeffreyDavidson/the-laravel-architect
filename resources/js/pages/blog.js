async function initializeCodeHighlighting() {
    try {
        await import('../prism');
    } catch {
        document.documentElement.dataset.codeHighlightingState = 'fallback';
    }
}

function scheduleCodeHighlighting() {
    if (!document.querySelector('.prose code[class*="language-"]')) {
        return;
    }

    document.documentElement.dataset.codeHighlightingState = 'idle';

    if ('requestIdleCallback' in window) {
        window.requestIdleCallback(initializeCodeHighlighting, { timeout: 1200 });

        return;
    }

    window.setTimeout(initializeCodeHighlighting);
}

function initializeCodeCopyButtons() {
    const template = document.querySelector('[data-code-copy-template]');

    if (!template) {
        return;
    }

    document.querySelectorAll('.prose pre').forEach((pre) => {
        if (pre.querySelector('code') && !pre.querySelector('.copy-btn')) {
            pre.appendChild(template.content.cloneNode(true));
        }
    });
}

function initializeArticleNavigation() {
    const article = document.querySelector('[data-article]');

    if (!article) {
        return;
    }

    const headings = Array.from(article.querySelectorAll('[data-article-prose] h2[id]'));
    const tocContainers = document.querySelectorAll('[data-article-toc]');
    const tocLists = document.querySelectorAll('[data-article-toc-list]');
    const linkTemplate = article.querySelector('[data-article-toc-template]');

    if (headings.length > 1 && linkTemplate) {
        tocLists.forEach((list) => {
            headings.forEach((heading) => {
                const link = linkTemplate.content.firstElementChild.cloneNode(true);

                link.href = `#${heading.id}`;
                link.textContent = heading.textContent;
                link.dataset.articleTocLink = heading.id;
                list.appendChild(link);
            });
        });

        tocContainers.forEach((container) => {
            container.hidden = false;
        });

        const observer = new IntersectionObserver(
            (entries) => {
                const visibleHeading = entries.find((entry) => entry.isIntersecting);

                if (!visibleHeading) {
                    return;
                }

                document.querySelectorAll('[data-article-toc-link]').forEach((link) => {
                    if (link.dataset.articleTocLink === visibleHeading.target.id) {
                        link.setAttribute('aria-current', 'true');
                    } else {
                        link.removeAttribute('aria-current');
                    }
                });
            },
            {
                rootMargin: '-20% 0px -65% 0px',
            },
        );

        headings.forEach((heading) => observer.observe(heading));
    }
}

export function initializeBlog() {
    initializeCodeCopyButtons();
    scheduleCodeHighlighting();
    initializeArticleNavigation();
}
