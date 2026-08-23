function initializeRevealAnimations(reduceMotion) {
    const revealElements = document.querySelectorAll('.fade-up');
    const countElements = document.querySelectorAll('.count-up');

    if (reduceMotion || !('IntersectionObserver' in window)) {
        revealElements.forEach((element) => element.classList.add('visible'));

        return;
    }

    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) {
                return;
            }

            entry.target.classList.add('visible');
            revealObserver.unobserve(entry.target);
        });
    }, { threshold: 0.1 });

    revealElements.forEach((element) => revealObserver.observe(element));

    const countObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) {
                return;
            }

            const target = Number(entry.target.dataset.target);
            let current = 0;

            function step() {
                current += Math.ceil(target / 30);

                if (current >= target) {
                    entry.target.textContent = target;

                    return;
                }

                entry.target.textContent = current;
                requestAnimationFrame(step);
            }

            step();
            countObserver.unobserve(entry.target);
        });
    }, { threshold: 0.5 });

    countElements.forEach((element) => countObserver.observe(element));
}

async function initializeArchitectureScene(reduceMotion) {
    const sceneElement = document.querySelector('[data-architecture-scene]');
    const canvasElement = sceneElement?.querySelector('[data-architecture-canvas]');

    if (!sceneElement || !canvasElement) {
        return;
    }

    if (reduceMotion || !window.WebGLRenderingContext) {
        sceneElement.dataset.architectureState = 'fallback';

        return;
    }

    try {
        const THREE = await import('three');
        const scene = new THREE.Scene();
        const camera = new THREE.PerspectiveCamera(38, 1, 0.1, 100);
        const renderer = new THREE.WebGLRenderer({
            alpha: true,
            antialias: true,
            powerPreference: 'low-power',
        });

        renderer.setClearColor(0x000000, 0);
        renderer.setPixelRatio(Math.min(window.devicePixelRatio, 1.5));
        canvasElement.append(renderer.domElement);

        camera.position.set(0, 0.4, 9.5);

        const nodePositions = [
            [-3.2, 0.8, 0],
            [-1.6, 0.8, 0],
            [0, 0.8, 0],
            [1.6, 0.8, 0],
            [3.2, 0.8, 0],
            [1.6, -1.25, 0],
            [3.2, -1.25, 0],
        ];
        const connections = [
            [0, 1],
            [1, 2],
            [2, 3],
            [3, 4],
            [3, 5],
            [5, 6],
        ];

        const linePositions = [];
        connections.forEach(([from, to]) => {
            linePositions.push(...nodePositions[from], ...nodePositions[to]);
        });

        const lineGeometry = new THREE.BufferGeometry();
        lineGeometry.setAttribute('position', new THREE.Float32BufferAttribute(linePositions, 3));

        const lineMaterial = new THREE.LineBasicMaterial({
            color: 0x526b83,
            transparent: true,
            opacity: 0.58,
        });
        scene.add(new THREE.LineSegments(lineGeometry, lineMaterial));

        const nodeGeometry = new THREE.PlaneGeometry(0.72, 0.72);
        const nodeMaterial = new THREE.ShaderMaterial({
            transparent: true,
            depthWrite: false,
            uniforms: {
                uTime: { value: 0 },
                uColor: { value: new THREE.Color(0x6f9fca) },
            },
            vertexShader: `
                varying vec2 vUv;

                void main() {
                    vUv = uv;
                    gl_Position = projectionMatrix * modelViewMatrix * instanceMatrix * vec4(position, 1.0);
                }
            `,
            fragmentShader: `
                uniform float uTime;
                uniform vec3 uColor;
                varying vec2 vUv;

                void main() {
                    float distanceFromCenter = distance(vUv, vec2(0.5));
                    float ring = 1.0 - smoothstep(0.035, 0.075, abs(distanceFromCenter - 0.34));
                    float core = 1.0 - smoothstep(0.0, 0.10, distanceFromCenter);
                    float pulse = 0.72 + (sin(uTime * 1.35) + 1.0) * 0.08;
                    float alpha = max(ring * pulse, core);

                    if (alpha < 0.02) {
                        discard;
                    }

                    gl_FragColor = vec4(uColor, alpha);
                }
            `,
        });

        const nodes = new THREE.InstancedMesh(nodeGeometry, nodeMaterial, nodePositions.length);
        const nodeMatrix = new THREE.Matrix4();
        nodePositions.forEach((position, index) => {
            nodeMatrix.setPosition(...position);
            nodes.setMatrixAt(index, nodeMatrix);
        });
        scene.add(nodes);

        const packetGeometry = new THREE.SphereGeometry(0.055, 10, 10);
        const packetMaterial = new THREE.MeshBasicMaterial({ color: 0x9fc5e5 });
        const packet = new THREE.Mesh(packetGeometry, packetMaterial);
        scene.add(packet);

        const clock = new THREE.Clock();
        let animationFrame = null;
        let isVisible = true;
        let pointerX = 0;
        let pointerY = 0;

        function resize() {
            const { width, height } = canvasElement.getBoundingClientRect();

            if (width === 0 || height === 0) {
                return;
            }

            camera.aspect = width / height;
            camera.updateProjectionMatrix();
            renderer.setSize(width, height, false);
        }

        function render() {
            if (!isVisible || document.hidden) {
                animationFrame = null;

                return;
            }

            const elapsed = clock.getElapsedTime();
            const progress = (elapsed * 0.24) % 4;
            const segment = Math.floor(progress);
            const segmentProgress = progress - segment;
            const from = new THREE.Vector3(...nodePositions[segment]);
            const to = new THREE.Vector3(...nodePositions[segment + 1]);

            packet.position.lerpVectors(from, to, segmentProgress);
            nodeMaterial.uniforms.uTime.value = elapsed;
            camera.position.x += ((pointerX * 0.22) - camera.position.x) * 0.035;
            camera.position.y += ((0.4 + pointerY * 0.14) - camera.position.y) * 0.035;
            camera.lookAt(0, 0.1, 0);

            renderer.render(scene, camera);
            animationFrame = requestAnimationFrame(render);
        }

        function startRendering() {
            if (!animationFrame && isVisible && !document.hidden) {
                clock.start();
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
            renderer.dispose();
            nodeGeometry.dispose();
            nodeMaterial.dispose();
            lineGeometry.dispose();
            lineMaterial.dispose();
            packetGeometry.dispose();
            packetMaterial.dispose();
        }, { once: true });

        resize();
        sceneElement.dataset.architectureState = 'ready';
        startRendering();
    } catch {
        sceneElement.dataset.architectureState = 'fallback';
    }
}

function initializeHomepage() {
    const homepage = document.querySelector('[data-architecture-scene]');

    if (!homepage) {
        return;
    }

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    initializeArchitectureScene(reduceMotion);
    initializeRevealAnimations(reduceMotion);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeHomepage, { once: true });
} else {
    initializeHomepage();
}
