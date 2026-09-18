export function registerAboutCard(Alpine) {
    Alpine.data('aboutCard', () => ({
        flipCount: 0,
        isAnimating: false,
        reduceMotion: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
        tiltX: 0,
        tiltY: 0,
        scale: 1,
        transition: 'none',
        resetTimer: null,

        get flipped() {
            return this.flipCount % 2 === 1;
        },
        get cardStyle() {
            return {
                transition: this.transition,
                transform: `rotateX(${this.tiltX}deg) rotateY(${this.flipCount * 180 + this.tiltY}deg) scale(${this.scale})`,
            };
        },
        flipCard() {
            clearTimeout(this.resetTimer);
            this.flipCount++;
            this.isAnimating = !this.reduceMotion;
            this.tiltX = this.tiltY = 0;
            this.scale = 1;
            this.transition = this.reduceMotion ? 'none' : 'transform 0.8s cubic-bezier(0.16, 1, 0.3, 1)';
            if (!this.reduceMotion) {
                this.resetTimer = setTimeout(() => {
                    this.isAnimating = false;
                }, 800);
            }
        },
        tilt(event) {
            if (this.reduceMotion || this.isAnimating) {
                return;
            }
            const rect = this.$el.getBoundingClientRect();
            this.tiltX = (0.5 - (event.clientY - rect.top) / rect.height) * 15;
            this.tiltY = ((event.clientX - rect.left) / rect.width - 0.5) * 15;
            this.scale = 1.02;
            this.transition = 'transform 0.1s ease-out';
        },
        resetTilt() {
            if (this.reduceMotion || this.isAnimating) {
                return;
            }
            this.tiltX = this.tiltY = 0;
            this.scale = 1;
            this.transition = 'transform 0.6s cubic-bezier(0.16, 1, 0.3, 1)';
        },
        destroy() {
            clearTimeout(this.resetTimer);
        },
    }));
}
