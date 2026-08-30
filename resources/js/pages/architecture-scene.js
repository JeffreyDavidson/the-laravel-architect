const NODE_POSITIONS = [
    [-3.2, 0.8],
    [-1.6, 0.8],
    [0, 0.8],
    [1.6, 0.8],
    [3.2, 0.8],
    [1.6, -1.25],
    [3.2, -1.25],
];

const CONNECTIONS = [
    [0, 1],
    [1, 2],
    [2, 3],
    [3, 4],
    [3, 5],
    [5, 6],
];

const PRIMARY_PATH = NODE_POSITIONS.slice(0, 5);

export function mountArchitectureScene(sceneElement, canvasElement) {
    const canvas = document.createElement('canvas');
    const context = canvas.getContext('2d');

    if (!context || !('ResizeObserver' in window) || !('IntersectionObserver' in window)) {
        throw new Error('The architecture scene is not supported by this browser.');
    }

    canvasElement.append(canvas);

    let animationFrame = null;
    let width = 0;
    let height = 0;
    let isVisible = true;
    let pointerX = 0;
    let pointerY = 0;
    let cameraX = 0;
    let cameraY = 0;
    let startedAt = performance.now();
    let palette = readPalette();

    function readPalette() {
        const styles = getComputedStyle(sceneElement);

        return {
            line: styles.getPropertyValue('--architecture-line').trim(),
            node: styles.getPropertyValue('--architecture-node').trim(),
            packet: styles.getPropertyValue('--architecture-packet').trim(),
        };
    }

    function project([x, y]) {
        return {
            x: (width / 2) + (x * width * 0.115) + cameraX,
            y: (height / 2) - (y * height * 0.245) + cameraY,
        };
    }

    function resize() {
        const bounds = canvasElement.getBoundingClientRect();

        width = bounds.width;
        height = bounds.height;

        if (width === 0 || height === 0) {
            return;
        }

        const pixelRatio = Math.min(window.devicePixelRatio, 1.5);
        canvas.width = Math.round(width * pixelRatio);
        canvas.height = Math.round(height * pixelRatio);
        context.setTransform(pixelRatio, 0, 0, pixelRatio, 0, 0);
    }

    function drawConnection(from, to) {
        const start = project(NODE_POSITIONS[from]);
        const end = project(NODE_POSITIONS[to]);

        context.beginPath();
        context.moveTo(start.x, start.y);
        context.lineTo(end.x, end.y);
        context.stroke();
    }

    function drawNode(position, pulse) {
        const point = project(position);
        const radius = Math.max(9, Math.min(14, width * 0.022));

        context.beginPath();
        context.arc(point.x, point.y, radius + pulse, 0, Math.PI * 2);
        context.stroke();

        context.globalAlpha = 0.9;
        context.beginPath();
        context.arc(point.x, point.y, 3, 0, Math.PI * 2);
        context.fill();
        context.globalAlpha = 1;
    }

    function drawPacket(elapsed) {
        const progress = (elapsed * 0.24) % (PRIMARY_PATH.length - 1);
        const segment = Math.floor(progress);
        const segmentProgress = progress - segment;
        const start = project(PRIMARY_PATH[segment]);
        const end = project(PRIMARY_PATH[segment + 1]);
        const x = start.x + ((end.x - start.x) * segmentProgress);
        const y = start.y + ((end.y - start.y) * segmentProgress);

        context.fillStyle = palette.packet;
        context.beginPath();
        context.arc(x, y, 3.5, 0, Math.PI * 2);
        context.fill();
    }

    function render(timestamp) {
        if (!isVisible || document.hidden) {
            animationFrame = null;

            return;
        }

        const elapsed = (timestamp - startedAt) / 1000;
        const pulse = 1.5 + ((Math.sin(elapsed * 1.35) + 1) * 1.25);

        cameraX += ((pointerX * 10) - cameraX) * 0.04;
        cameraY += ((pointerY * 6) - cameraY) * 0.04;

        context.clearRect(0, 0, width, height);
        context.strokeStyle = palette.line;
        context.lineWidth = 1;
        CONNECTIONS.forEach(([from, to]) => drawConnection(from, to));

        context.strokeStyle = palette.node;
        context.fillStyle = palette.node;
        context.lineWidth = 2;
        NODE_POSITIONS.forEach((position) => drawNode(position, pulse));

        drawPacket(elapsed);

        animationFrame = requestAnimationFrame(render);
    }

    function startRendering() {
        if (!animationFrame && isVisible && !document.hidden) {
            startedAt = performance.now();
            animationFrame = requestAnimationFrame(render);
        }
    }

    const resizeObserver = new ResizeObserver(resize);
    resizeObserver.observe(canvasElement);

    const visibilityObserver = new IntersectionObserver(([entry]) => {
        isVisible = entry.isIntersecting;

        if (isVisible) {
            startRendering();
        }
    }, { threshold: 0.05 });
    visibilityObserver.observe(sceneElement);

    const themeObserver = new MutationObserver(() => {
        palette = readPalette();
    });
    themeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

    sceneElement.addEventListener('pointermove', (event) => {
        const bounds = sceneElement.getBoundingClientRect();

        pointerX = ((event.clientX - bounds.left) / bounds.width) - 0.5;
        pointerY = 0.5 - ((event.clientY - bounds.top) / bounds.height);
    });

    sceneElement.addEventListener('pointerleave', () => {
        pointerX = 0;
        pointerY = 0;
    });

    document.addEventListener('visibilitychange', startRendering);
    window.addEventListener('pagehide', () => {
        cancelAnimationFrame(animationFrame);
        resizeObserver.disconnect();
        visibilityObserver.disconnect();
        themeObserver.disconnect();
        canvas.remove();
    }, { once: true });

    resize();
    startRendering();
}
