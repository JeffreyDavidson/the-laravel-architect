function formatTime(seconds) {
    if (!Number.isFinite(seconds)) {
        return '0:00';
    }
    return `${Math.floor(seconds / 60)}:${Math.floor(seconds % 60)
        .toString()
        .padStart(2, '0')}`;
}

export function registerAudioPlayer(Alpine) {
    Alpine.data('audioPlayer', () => ({
        playing: false,
        elapsed: 0,
        duration: 0,
        speed: 1,

        get paused() {
            return !this.playing;
        },
        get playingAttribute() {
            return String(this.playing);
        },
        get playLabel() {
            return this.playing ? 'Pause episode' : 'Play episode';
        },
        get elapsedLabel() {
            return formatTime(this.elapsed);
        },
        get durationLabel() {
            return formatTime(this.duration);
        },
        get seekLabel() {
            return `${this.elapsedLabel} of ${this.durationLabel}`;
        },
        get percentage() {
            return this.duration > 0 ? (this.elapsed / this.duration) * 100 : 0;
        },
        get progressStyle() {
            return { width: `${this.percentage}%` };
        },
        get speedLabel() {
            return `${this.speed}x`;
        },
        get speedDescription() {
            return `Playback speed ${this.speed} times. Activate to change.`;
        },
        init() {
            this.$refs.audio.controls = false;
            this.updateProgress();
            this.updatePlaybackState();
        },
        updateProgress() {
            const audio = this.$refs.audio;
            this.duration = Number.isFinite(audio.duration) ? audio.duration : 0;
            this.elapsed = Number.isFinite(audio.currentTime) ? audio.currentTime : 0;
        },
        updatePlaybackState() {
            this.playing = !this.$refs.audio.paused && !this.$refs.audio.ended;
        },
        async togglePlayback() {
            const audio = this.$refs.audio;
            if (!audio.paused) {
                audio.pause();
                return;
            }
            try {
                await audio.play();
            } catch {
                this.updatePlaybackState();
            }
        },
        seek(event) {
            if (this.duration <= 0) {
                return;
            }
            this.$refs.audio.currentTime = (Number(event.target.value) / 100) * this.duration;
            this.updateProgress();
        },
        skipBack() {
            this.$refs.audio.currentTime = Math.max(0, this.$refs.audio.currentTime - 15);
            this.updateProgress();
        },
        skipForward() {
            const audio = this.$refs.audio;
            const limit = Number.isFinite(audio.duration) ? audio.duration : audio.currentTime + 30;
            audio.currentTime = Math.min(limit, audio.currentTime + 30);
            this.updateProgress();
        },
        cycleSpeed() {
            const speeds = [0.5, 0.75, 1, 1.25, 1.5, 1.75, 2];
            this.speed = speeds[(speeds.indexOf(this.speed) + 1) % speeds.length];
            this.$refs.audio.playbackRate = this.speed;
        },
        destroy() {
            this.$refs.audio.pause();
            this.$refs.audio.controls = true;
        },
    }));
}
