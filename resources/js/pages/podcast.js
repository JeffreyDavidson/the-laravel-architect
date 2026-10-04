import { copyText } from '../utils/clipboard';

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

function highlightTranscriptMatches(content, query) {
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

            const mark = document.createElement('mark');
            mark.dataset.transcriptMatch = '';
            mark.className = 'bg-amber-500/10 text-amber-700 dark:text-amber-300';
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

                const anchor = document.createElement('a');
                anchor.href = `#${id}`;
                anchor.dataset.transcriptAnchor = '';
                anchor.className =
                    'inline-flex text-sm font-semibold text-gray-500 transition-colors hover:text-blue-500 dark:text-gray-400';
                anchor.setAttribute('aria-label', `Copy link to section: ${label}`);
                anchor.textContent = ' #';

                anchor.addEventListener('click', async () => {
                    const url = `${window.location.origin}${window.location.pathname}${window.location.search}#${id}`;

                    if (await copyText(url)) {
                        anchor.setAttribute('aria-label', 'Section link copied');

                        window.setTimeout(() => {
                            anchor.setAttribute('aria-label', `Copy link to section: ${label}`);
                        }, 2000);
                    }
                });

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

            const matches = highlightTranscriptMatches(this.$refs.content, query);

            this.status = matches === 0 ? 'No matches found' : `${matches} match${matches === 1 ? '' : 'es'} found`;
        },
    }));
}
