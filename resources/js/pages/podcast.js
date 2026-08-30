const copyIcon = '<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>';
const copiedIcon = '<svg class="h-4 w-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';

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

        button.addEventListener('click', () => {
            template.replaceWith(template.content.cloneNode(true));
            button.remove();
        }, { once: true });
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

    if (!(audio instanceof HTMLAudioElement)
        || !(seek instanceof HTMLInputElement)
        || !(progress instanceof HTMLElement)
        || !(currentTime instanceof HTMLElement)
        || !(duration instanceof HTMLElement)
        || !(playButton instanceof HTMLButtonElement)
        || !(playIcon instanceof SVGElement)
        || !(pauseIcon instanceof SVGElement)
        || !(skipBackButton instanceof HTMLButtonElement)
        || !(skipForwardButton instanceof HTMLButtonElement)
        || !(speedButton instanceof HTMLButtonElement)
        || !(speedLabel instanceof HTMLElement)) {
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

function initializePodcastPage() {
    initializePodcastCopyButton();
    initializeYoutubeFacades();
    document.querySelectorAll('[data-audio-player]').forEach(initializeAudioPlayer);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializePodcastPage, { once: true });
} else {
    initializePodcastPage();
}
