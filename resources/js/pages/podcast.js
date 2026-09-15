const copyIcon =
    '<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>';
const copiedIcon =
    '<svg class="h-4 w-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';

function initializePodcastCopyButton() {
    const button = document.querySelector('[data-podcast-copy-url]');

    if (!button) {
        return;
    }

    button.addEventListener('click', async () => {
        await navigator.clipboard.writeText(button.dataset.podcastCopyUrl);
        button.innerHTML = copiedIcon;
        button.setAttribute('aria-label', 'Episode link copied');

        window.setTimeout(() => {
            button.innerHTML = copyIcon;
            button.setAttribute('aria-label', 'Copy episode link');
        }, 2000);
    });
}

function initializeYoutubeFacades() {
    document.querySelectorAll('[data-youtube-facade]').forEach((facade) => {
        const button = facade.querySelector('[data-youtube-play]');
        const template = facade.querySelector('[data-youtube-player]');

        if (!button || !(template instanceof HTMLTemplateElement)) {
            return;
        }

        button.addEventListener(
            'click',
            () => {
                template.replaceWith(template.content.cloneNode(true));
                button.remove();
            },
            { once: true },
        );
    });
}

function formatTime(seconds) {
    if (!Number.isFinite(seconds)) {
        return '0:00';
    }

    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = Math.floor(seconds % 60);

    return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
}

function initializeAudioPlayer(player) {
    const audio = player.querySelector('[data-audio]');
    const seek = player.querySelector('[data-audio-seek]');
    const progress = player.querySelector('[data-audio-progress]');
    const currentTime = player.querySelector('[data-audio-current-time]');
    const duration = player.querySelector('[data-audio-duration]');
    const playButton = player.querySelector('[data-audio-play]');
    const playIcon = player.querySelector('[data-audio-play-icon]');
    const pauseIcon = player.querySelector('[data-audio-pause-icon]');
    const skipBackButton = player.querySelector('[data-audio-skip-back]');
    const skipForwardButton = player.querySelector('[data-audio-skip-forward]');
    const speedButton = player.querySelector('[data-audio-speed]');
    const speedLabel = player.querySelector('[data-audio-speed-label]');

    if (
        !(audio instanceof HTMLAudioElement) ||
        !(seek instanceof HTMLInputElement) ||
        !(progress instanceof HTMLElement) ||
        !(currentTime instanceof HTMLElement) ||
        !(duration instanceof HTMLElement) ||
        !(playButton instanceof HTMLButtonElement) ||
        !(playIcon instanceof SVGElement) ||
        !(pauseIcon instanceof SVGElement) ||
        !(skipBackButton instanceof HTMLButtonElement) ||
        !(skipForwardButton instanceof HTMLButtonElement) ||
        !(speedButton instanceof HTMLButtonElement) ||
        !(speedLabel instanceof HTMLElement)
    ) {
        return;
    }

    const playbackSpeeds = [0.5, 0.75, 1, 1.25, 1.5, 1.75, 2];
    let speedIndex = playbackSpeeds.indexOf(1);

    const updateProgress = () => {
        const audioDuration = Number.isFinite(audio.duration) ? audio.duration : 0;
        const elapsed = Number.isFinite(audio.currentTime) ? audio.currentTime : 0;
        const percentage = audioDuration > 0 ? (elapsed / audioDuration) * 100 : 0;

        seek.value = percentage.toString();
        seek.setAttribute('aria-valuetext', `${formatTime(elapsed)} of ${formatTime(audioDuration)}`);
        progress.style.width = `${percentage}%`;
        currentTime.textContent = formatTime(elapsed);
        duration.textContent = formatTime(audioDuration);
    };

    const updatePlaybackState = () => {
        const playing = !audio.paused && !audio.ended;

        player.dataset.playing = playing.toString();
        playButton.setAttribute('aria-label', playing ? 'Pause episode' : 'Play episode');
        playButton.setAttribute('aria-pressed', playing.toString());
        playIcon.hidden = playing;
        pauseIcon.hidden = !playing;
    };

    audio.controls = false;
    audio.addEventListener('loadedmetadata', updateProgress);
    audio.addEventListener('durationchange', updateProgress);
    audio.addEventListener('timeupdate', updateProgress);
    audio.addEventListener('play', updatePlaybackState);
    audio.addEventListener('pause', updatePlaybackState);
    audio.addEventListener('ended', updatePlaybackState);

    playButton.addEventListener('click', async () => {
        if (!audio.paused) {
            audio.pause();

            return;
        }

        try {
            await audio.play();
        } catch {
            updatePlaybackState();
        }
    });

    seek.addEventListener('input', () => {
        if (!Number.isFinite(audio.duration) || audio.duration <= 0) {
            return;
        }

        audio.currentTime = (Number(seek.value) / 100) * audio.duration;
        updateProgress();
    });

    skipBackButton.addEventListener('click', () => {
        audio.currentTime = Math.max(0, audio.currentTime - 15);
        updateProgress();
    });

    skipForwardButton.addEventListener('click', () => {
        const durationLimit = Number.isFinite(audio.duration) ? audio.duration : audio.currentTime + 30;

        audio.currentTime = Math.min(durationLimit, audio.currentTime + 30);
        updateProgress();
    });

    speedButton.addEventListener('click', () => {
        speedIndex = (speedIndex + 1) % playbackSpeeds.length;
        audio.playbackRate = playbackSpeeds[speedIndex];
        speedLabel.textContent = `${audio.playbackRate}x`;
        speedButton.setAttribute('aria-label', `Playback speed ${audio.playbackRate} times. Activate to change.`);
    });

    updateProgress();
    updatePlaybackState();
}

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

            if (!navigator.clipboard?.writeText) {
                return;
            }

            try {
                await navigator.clipboard.writeText(url);
                anchor.setAttribute('aria-label', 'Section link copied');

                window.setTimeout(() => {
                    anchor.setAttribute('aria-label', `Copy link to section: ${label}`);
                }, 2000);
            } catch {
                // The anchor still navigates to the section when clipboard access is unavailable.
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

function initializePodcastPage() {
    initializePodcastCopyButton();
    initializeYoutubeFacades();
    document.querySelectorAll('[data-audio-player]').forEach(initializeAudioPlayer);
    document.querySelectorAll('[data-transcript]').forEach(initializeTranscript);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializePodcastPage, { once: true });
} else {
    initializePodcastPage();
}
