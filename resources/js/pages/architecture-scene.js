import {
    BufferGeometry,
    Clock,
    Color,
    Float32BufferAttribute,
    InstancedMesh,
    LineBasicMaterial,
    LineSegments,
    Matrix4,
    Mesh,
    MeshBasicMaterial,
    PerspectiveCamera,
    PlaneGeometry,
    Scene,
    ShaderMaterial,
    SphereGeometry,
    Vector3,
    WebGLRenderer,
} from 'three';

export function mountArchitectureScene(sceneElement, canvasElement) {
    const scene = new Scene();
    const camera = new PerspectiveCamera(38, 1, 0.1, 100);
    const renderer = new WebGLRenderer({
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
    const nodeVectors = nodePositions.map((position) => new Vector3(...position));
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

    const lineGeometry = new BufferGeometry();
    lineGeometry.setAttribute('position', new Float32BufferAttribute(linePositions, 3));

    const lineMaterial = new LineBasicMaterial({
        color: 0x526b83,
        transparent: true,
        opacity: 0.58,
    });
    scene.add(new LineSegments(lineGeometry, lineMaterial));

    const nodeGeometry = new PlaneGeometry(0.72, 0.72);
    const nodeMaterial = new ShaderMaterial({
        transparent: true,
        depthWrite: false,
        uniforms: {
            uTime: { value: 0 },
            uColor: { value: new Color(0x6f9fca) },
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

    const nodes = new InstancedMesh(nodeGeometry, nodeMaterial, nodePositions.length);
    const nodeMatrix = new Matrix4();
    nodePositions.forEach((position, index) => {
        nodeMatrix.setPosition(...position);
        nodes.setMatrixAt(index, nodeMatrix);
    });
    scene.add(nodes);

    const packetGeometry = new SphereGeometry(0.055, 10, 10);
    const packetMaterial = new MeshBasicMaterial({ color: 0x9fc5e5 });
    const packet = new Mesh(packetGeometry, packetMaterial);
    scene.add(packet);

    const clock = new Clock();
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

        packet.position.lerpVectors(nodeVectors[segment], nodeVectors[segment + 1], segmentProgress);
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
    startRendering();
}
