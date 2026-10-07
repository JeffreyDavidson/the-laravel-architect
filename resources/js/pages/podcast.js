function transcriptSlug(value) {
    return (
        value
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/(^-|-$)/g, '') || 'section'
    );
}

function clearTranscriptMatches(content) {
    content.querySelectorAll('[data-transcript-match]').forEach(match => {
        match.replaceWith(document.createTextNode(match.textContent ?? ''));
    });

    content.normalize();
}

function highlightTranscriptMatches(content, query, matchTemplate) {
    const normalizedQuery = query.toLocaleLowerCase();
    const walker = document.createTreeWalker(content, NodeFilter.SHOW_TEXT);
    const textNodes = [];
    let currentNode = walker.nextNode();

    while (currentNode) {
        const parent = currentNode.parentElement;

        if (parent && !parent.closest('[data-transcript-anchor], script, style')) {
            textNodes.push(currentNode);
        }

        currentNode = walker.nextNode();
    }

    let matches = 0;

    textNodes.forEach(textNode => {
        const text = textNode.textContent ?? '';
        const normalizedText = text.toLocaleLowerCase();
        let searchStart = 0;
        let matchStart = normalizedText.indexOf(normalizedQuery, searchStart);

        if (matchStart === -1) {
            return;
        }

        const fragment = document.createDocumentFragment();

        while (matchStart !== -1) {
            const matchEnd = matchStart + normalizedQuery.length;

            fragment.append(document.createTextNode(text.slice(searchStart, matchStart)));

            const mark = matchTemplate.content.firstElementChild.cloneNode(true);
            mark.textContent = text.slice(matchStart, matchEnd);
            fragment.append(mark);
            matches++;

            searchStart = matchEnd;
            matchStart = normalizedText.indexOf(normalizedQuery, searchStart);
        }

        fragment.append(document.createTextNode(text.slice(searchStart)));
        textNode.replaceWith(fragment);
    });

    return matches;
}

export function registerPodcast(Alpine) {
    Alpine.data('youtubePlayer', () => ({
        loaded: false,
        load() {
            this.loaded = true;
        },
    }));

    Alpine.data('transcript', () => ({
        ready: false,
        status: 'Search the transcript',

        get notReady() {
            return !this.ready;
        },
        init() {
            this.addSectionLinks();
            this.openFromHash();
            this.ready = true;
            this.$root.dataset.ready = 'true';
        },
        addSectionLinks() {
            const anchorTemplate = this.$root.querySelector('[data-transcript-anchor-template]');
            const usedIds = new Set();

            this.$refs.content.querySelectorAll('h2, h3, h4').forEach(heading => {
                const label = heading.textContent?.trim() ?? '';

                if (!label) {
                    return;
                }

                let id = `transcript-${transcriptSlug(label)}`;
                let suffix = 2;

                while (usedIds.has(id) || document.getElementById(id)) {
                    id = `transcript-${transcriptSlug(label)}-${suffix}`;
                    suffix++;
                }

                usedIds.add(id);
                heading.id = id;

                // The copy button component copies the section URL and shows its feedback in the label.
                const anchor = anchorTemplate.content.firstElementChild.cloneNode(true);
                anchor.href = `#${id}`;
                anchor.dataset.copyText = `${window.location.origin}${window.location.pathname}${window.location.search}#${id}`;
                anchor.dataset.copyLabel = `Copy link to section: ${label}`;
                anchor.setAttribute('aria-label', anchor.dataset.copyLabel);

                heading.append(anchor);
            });
        },
        openFromHash() {
            const hashId = window.location.hash.slice(1);
            const hashTarget = hashId ? document.getElementById(hashId) : null;

            if (hashTarget && this.$refs.content.contains(hashTarget)) {
                this.$root.open = true;
                window.setTimeout(() => hashTarget.scrollIntoView({ block: 'start' }), 0);
            }
        },
        search() {
            clearTranscriptMatches(this.$refs.content);

            const query = this.$refs.search.value.trim();

            if (!query) {
                this.status = 'Search the transcript';

                return;
            }

            this.$root.open = true;

            const matches = highlightTranscriptMatches(
                this.$refs.content,
                query,
                this.$root.querySelector('[data-transcript-match-template]'),
            );

            this.status = matches === 0 ? 'No matches found' : `${matches} match${matches === 1 ? '' : 'es'} found`;
        },
    }));
}
