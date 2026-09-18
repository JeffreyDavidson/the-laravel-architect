import { copyText } from '../utils/clipboard';
import { registerAudioPlayer } from './audio-player';

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
    content.querySelectorAll('[data-transcript-match]').forEach((match) => {
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

    textNodes.forEach((textNode) => {
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

function initializeTranscript(details) {
    const tools = details.querySelector('[data-transcript-tools]');
    const search = details.querySelector('[data-transcript-search]');
    const status = details.querySelector('[data-transcript-status]');
    const content = details.querySelector('[data-transcript-content]');

    if (
        !(details instanceof HTMLDetailsElement) ||
        !(tools instanceof HTMLElement) ||
        !(search instanceof HTMLInputElement) ||
        !(status instanceof HTMLElement) ||
        !(content instanceof HTMLElement)
    ) {
        return;
    }

    tools.hidden = false;

    const usedIds = new Set();

    content.querySelectorAll('h2, h3, h4').forEach((heading) => {
        if (!(heading instanceof HTMLElement)) {
            return;
        }

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

    const hashId = window.location.hash.slice(1);
    const hashTarget = hashId ? document.getElementById(hashId) : null;

    if (hashTarget && content.contains(hashTarget)) {
        details.open = true;
        window.setTimeout(() => hashTarget.scrollIntoView({ block: 'start' }), 0);
    }

    search.addEventListener('input', () => {
        clearTranscriptMatches(content);

        const query = search.value.trim();

        if (!query) {
            status.textContent = 'Search the transcript';

            return;
        }

        details.open = true;
        const matches = highlightTranscriptMatches(content, query);
        status.textContent = matches === 0 ? 'No matches found' : `${matches} match${matches === 1 ? '' : 'es'} found`;
    });
}

export function initializePodcast(Alpine) {
    registerAudioPlayer(Alpine);
    Alpine.data('youtubePlayer', () => ({
        loaded: false,
        load() {
            this.loaded = true;
        },
    }));
    document.querySelectorAll('[data-transcript]').forEach(initializeTranscript);
}
