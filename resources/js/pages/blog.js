export function registerBlogArticle(Alpine) {
    Alpine.data('blogArticle', () => ({
        observer: null,

        init() {
            this.addCopyButtons();
            this.scheduleHighlighting();
            this.buildContents();
            this.$root.dataset.ready = 'true';
        },
        destroy() {
            this.observer?.disconnect();
        },
        addCopyButtons() {
            const template = this.$root.querySelector('[data-code-copy-template]');

            if (!template) {
                return;
            }

            this.$root.querySelectorAll('.prose pre').forEach(pre => {
                if (pre.querySelector('code') && !pre.querySelector('.copy-btn')) {
                    pre.append(...Array.from(template.content.children, child => child.cloneNode(true)));
                }
            });
        },
        scheduleHighlighting() {
            if (!this.$root.querySelector('.prose code[class*="language-"]')) {
                return;
            }

            document.documentElement.dataset.codeHighlightingState = 'idle';

            if ('requestIdleCallback' in window) {
                window.requestIdleCallback(() => this.highlight(), { timeout: 1200 });

                return;
            }

            window.setTimeout(() => this.highlight());
        },
        async highlight() {
            try {
                await import('../prism');
            } catch {
                document.documentElement.dataset.codeHighlightingState = 'fallback';
            }
        },
        buildContents() {
            const headings = Array.from(this.$root.querySelectorAll('[data-article-prose] h2[id]'));
            const linkTemplate = this.$root.querySelector('[data-article-toc-template]');

            if (headings.length < 2 || !linkTemplate) {
                return;
            }

            this.$root.querySelectorAll('[data-article-toc-list]').forEach(list => {
                headings.forEach(heading => {
                    const link = linkTemplate.content.firstElementChild.cloneNode(true);

                    link.href = `#${heading.id}`;
                    link.textContent = heading.textContent;
                    link.dataset.articleTocLink = heading.id;
                    list.appendChild(link);
                });
            });

            this.$root.querySelectorAll('[data-article-toc]').forEach(container => {
                container.hidden = false;
            });

            this.observer = new IntersectionObserver(
                entries => {
                    const visibleHeading = entries.find(entry => entry.isIntersecting);

                    if (!visibleHeading) {
                        return;
                    }

                    this.$root.querySelectorAll('[data-article-toc-link]').forEach(link => {
                        if (link.dataset.articleTocLink === visibleHeading.target.id) {
                            link.setAttribute('aria-current', 'true');
                        } else {
                            link.removeAttribute('aria-current');
                        }
                    });
                },
                { rootMargin: '-20% 0px -65% 0px' },
            );

            headings.forEach(heading => this.observer.observe(heading));
        },
    }));
}
