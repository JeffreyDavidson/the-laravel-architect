@extends('layouts.app')

@section('content')
<style>
    /* ===== Gradient Mesh Hero Background ===== */
    @keyframes meshShift {
        0%, 100% { background-position: 0% 50%; }
        25% { background-position: 50% 0%; }
        50% { background-position: 100% 50%; }
        75% { background-position: 50% 100%; }
    }
    .hero-mesh {
        background: linear-gradient(-45deg, #0D1117, #1a1040, #0d2847, #1a0d30, #0D1117);
        background-size: 400% 400%;
        animation: meshShift 20s ease infinite;
    }

    /* ===== Noise/Grain Texture Overlay ===== */
    .noise-overlay {
        position: relative;
    }
    .noise-overlay::after {
        content: '';
        position: absolute;
        inset: 0;
        opacity: 0.04;
        pointer-events: none;
        z-index: 1;
        background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
        background-repeat: repeat;
        background-size: 256px 256px;
    }

    /* ===== Dot Grid Pattern for Content Sections ===== */
    .dot-grid-bg {
        position: relative;
    }
    .dot-grid-bg::before {
        content: '';
        position: absolute;
        inset: 0;
        opacity: 0.03;
        pointer-events: none;
        background-image: radial-gradient(circle, #ffffff 1px, transparent 1px);
        background-size: 24px 24px;
        z-index: 0;
    }
    .dot-grid-bg > * {
        position: relative;
        z-index: 1;
    }

    /* ===== Section Dividers ===== */
    .section-divider {
        height: 80px;
        position: relative;
        overflow: hidden;
    }
    .section-divider-hero {
        background: linear-gradient(to bottom, transparent, #0D1117);
    }
    .section-divider-dark {
        background: linear-gradient(to bottom, #0D1117, rgba(13,17,23,0.95));
    }
    .section-divider-cta {
        background: linear-gradient(to bottom, #0D1117, #4A7FBF);
    }

    /* ===== Laravel Gradient Glow Text ===== */
    .laravel-glow {
        font-family: 'Empera', serif;
        background: linear-gradient(135deg, #4A7FBF, #6fa3d6, #4A7FBF);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        filter: drop-shadow(0 0 20px rgba(74, 127, 191, 0.5)) drop-shadow(0 0 40px rgba(74, 127, 191, 0.25));
    }

    /* ===== Text Scramble Effect ===== */
    .scramble-wrapper {
        display: inline-flex;
        align-items: center;
        min-height: 1.5em;
    }
    .scramble-text {
        white-space: nowrap;
        font-family: 'JetBrains Mono', monospace, ui-monospace;
    }
    .scramble-char-random {
        color: rgba(74, 127, 191, 0.4);
    }
    .scramble-char-resolved {
        color: #7bb8e0;
    }

    /* ===== Code Editor Window ===== */
    .code-editor {
        background: #0d1117;
        border: 1px solid rgba(74, 127, 191, 0.25);
        border-radius: 12px;
        overflow: hidden;
        box-shadow:
            0 0 30px rgba(74, 127, 191, 0.15),
            0 0 60px rgba(74, 127, 191, 0.08),
            0 25px 50px rgba(0, 0, 0, 0.5);
        transition: all 0.4s ease;
        will-change: transform;
    }
    .code-editor:hover {
        border-color: rgba(74, 127, 191, 0.4);
        box-shadow:
            0 0 40px rgba(74, 127, 191, 0.2),
            0 0 80px rgba(74, 127, 191, 0.1),
            0 25px 50px rgba(0, 0, 0, 0.5);
        transform: translateY(-4px);
    }
    .code-editor-bar {
        background: #161b22;
        border-bottom: 1px solid rgba(255,255,255,0.06);
    }
    .code-editor-tab {
        background: #0d1117;
        border-top: 2px solid #4A7FBF;
        border-right: 1px solid rgba(255,255,255,0.06);
    }
    .code-editor-tab-inactive {
        background: #161b22;
        border-right: 1px solid rgba(255,255,255,0.06);
    }
    .code-line-number {
        color: #484f58;
        user-select: none;
        min-width: 2rem;
        text-align: right;
    }
    /* Syntax colors */
    .syn-keyword { color: #ff7b72; }
    .syn-string { color: #a5d6ff; }
    .syn-class { color: #79c0ff; }
    .syn-method { color: #d2a8ff; }
    .syn-comment { color: #484f58; font-style: italic; }
    .syn-variable { color: #ffa657; }
    .syn-function { color: #d2a8ff; }
    .syn-arrow { color: #ff7b72; }
    .syn-bracket { color: #8b949e; }
    .syn-text { color: #c9d1d9; }
    .syn-param { color: #ffa657; }

    @keyframes codeSlideIn {
        from { opacity: 0; transform: translateX(30px); }
        to { opacity: 1; transform: translateX(0); }
    }
    .code-editor {
        animation: codeSlideIn 0.8s ease 0.3s both;
    }

    /* Consistent code panel height */
    .code-panel { min-height: 620px; }

    /* Custom scrollbar for code editor */
    .code-editor ::-webkit-scrollbar { width: 8px; }
    .code-editor ::-webkit-scrollbar-track { background: transparent; }
    .code-editor ::-webkit-scrollbar-thumb { background: rgba(74, 127, 191, 0.2); border-radius: 4px; }
    .code-editor ::-webkit-scrollbar-thumb:hover { background: rgba(74, 127, 191, 0.4); }

    /* Line highlight */
    .code-line-highlight {
        background: rgba(74, 127, 191, 0.08);
        border-left: 2px solid #4A7FBF;
        margin-left: -0.5rem;
        padding-left: calc(0.5rem - 2px);
    }

    /* ===== Glowing Buttons ===== */
    .glow-btn {
        position: relative;
        transition: all 0.3s ease;
        will-change: transform;
    }
    .glow-btn:hover {
        box-shadow: 0 0 20px rgba(74, 127, 191, 0.4), 0 0 40px rgba(74, 127, 191, 0.2);
        transform: translateY(-2px);
    }
    .glow-btn-outline:hover {
        box-shadow: 0 0 20px rgba(74, 127, 191, 0.3), 0 0 40px rgba(74, 127, 191, 0.15);
        transform: translateY(-2px);
        border-color: #4A7FBF;
        color: white;
    }

    /* ===== Glassmorphism Cards with Cursor Glow ===== */
    .glass-card {
        background: rgba(26, 29, 33, 0.6);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(74, 127, 191, 0.15);
        transition: all 0.4s ease;
        transform-style: preserve-3d;
        perspective: 800px;
        --glow-x: 50%;
        --glow-y: 50%;
    }
    .glass-card::after {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: inherit;
        opacity: 0;
        transition: opacity 0.3s ease;
        background: radial-gradient(600px circle at var(--glow-x) var(--glow-y), rgba(74, 127, 191, 0.12), transparent 40%);
        pointer-events: none;
        z-index: 1;
    }
    .glass-card:hover::after {
        opacity: 1;
    }
    .glass-card:hover {
        border-color: rgba(74, 127, 191, 0.5);
        box-shadow: 0 0 30px rgba(74, 127, 191, 0.15), inset 0 0 30px rgba(74, 127, 191, 0.03);
        transform: rotateY(-2deg) rotateX(2deg) scale(1.02);
    }

    /* ===== Gradient Top Border Cards with Cursor Glow ===== */
    .gradient-border-card {
        position: relative;
        overflow: hidden;
        --glow-x: 50%;
        --glow-y: 50%;
    }
    .gradient-border-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, #4A7FBF, #E47A9D);
        z-index: 2;
    }
    .gradient-border-card::after {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: inherit;
        opacity: 0;
        transition: opacity 0.3s ease;
        background: radial-gradient(600px circle at var(--glow-x) var(--glow-y), rgba(74, 127, 191, 0.1), transparent 40%);
        pointer-events: none;
        z-index: 1;
    }
    .gradient-border-card:hover::after {
        opacity: 1;
    }

    /* ===== Scroll Fade Up ===== */
    .fade-up {
        opacity: 0;
        transform: translateY(30px);
        transition: opacity 0.6s ease, transform 0.6s ease;
    }
    .fade-up.visible {
        opacity: 1;
        transform: translateY(0);
    }

    /* ===== Infinite Marquee ===== */
    @keyframes marqueeScroll {
        0% { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }
    .marquee-track {
        display: flex;
        width: max-content;
        animation: marqueeScroll 30s linear infinite;
    }
    .marquee-track:hover {
        animation-play-state: paused;
    }
    .marquee-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0 2.5rem;
        white-space: nowrap;
        color: #484f58;
        font-size: 0.875rem;
        font-weight: 600;
        letter-spacing: 0.05em;
        transition: color 0.3s ease;
    }
    .marquee-item:hover {
        color: #c9d1d9;
    }
    .marquee-item svg,
    .marquee-item img {
        width: 28px;
        height: 28px;
        opacity: 0.4;
        transition: opacity 0.3s ease, filter 0.3s ease;
    }
    .marquee-item:hover svg,
    .marquee-item:hover img {
        opacity: 0.9;
    }
    .marquee-fade-left,
    .marquee-fade-right {
        position: absolute;
        top: 0;
        bottom: 0;
        width: 100px;
        z-index: 2;
        pointer-events: none;
    }
    .marquee-fade-left {
        left: 0;
        background: linear-gradient(to right, #0D1117, transparent);
    }
    .marquee-fade-right {
        right: 0;
        background: linear-gradient(to left, #0D1117, transparent);
    }

    /* ===== Stats Counter ===== */
    .stat-number {
        font-family: 'JetBrains Mono', ui-monospace, monospace;
        font-size: 3rem;
        font-weight: 800;
        line-height: 1;
        background: linear-gradient(135deg, #ffffff 0%, #8b949e 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .stat-divider {
        width: 1px;
        height: 60px;
        background: linear-gradient(to bottom, transparent, rgba(74,127,191,0.3), transparent);
    }

    /* ===== Service Cards ===== */
    .service-card {
        position: relative;
        background: rgba(22, 27, 34, 0.6);
        border: 1px solid rgba(74, 127, 191, 0.12);
        border-radius: 1rem;
        padding: 2rem;
        transition: all 0.4s ease;
        overflow: hidden;
    }
    .service-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 2px;
        background: var(--service-gradient);
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    .service-card:hover::before {
        opacity: 1;
    }
    .service-card:hover {
        border-color: rgba(74, 127, 191, 0.3);
        transform: translateY(-4px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.3);
    }
    .service-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1.25rem;
        transition: transform 0.3s ease;
    }
    .service-card:hover .service-icon {
        transform: scale(1.1) rotate(-3deg);
    }

    /* ===== Testimonials ===== */
    .testimonial-card {
        background: rgba(22, 27, 34, 0.5);
        border: 1px solid rgba(74, 127, 191, 0.1);
        border-radius: 1rem;
        padding: 2rem;
        position: relative;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        min-height: 280px;
    }
    .testimonial-card:hover {
        border-color: rgba(74, 127, 191, 0.25);
        transform: translateY(-2px);
    }
    .testimonial-card::before {
        content: '"';
        position: absolute;
        top: 1rem;
        right: 1.5rem;
        font-size: 4rem;
        font-family: Georgia, serif;
        color: rgba(74, 127, 191, 0.1);
        line-height: 1;
    }

    /* ===== Equalizer Bars ===== */
    @keyframes eq1 { 0%,100% { height: 8px; } 50% { height: 24px; } }
    @keyframes eq2 { 0%,100% { height: 16px; } 50% { height: 8px; } }
    @keyframes eq3 { 0%,100% { height: 12px; } 50% { height: 28px; } }
    @keyframes eq4 { 0%,100% { height: 20px; } 50% { height: 10px; } }
    @keyframes eq5 { 0%,100% { height: 6px; } 50% { height: 22px; } }
    .eq-bar { width: 3px; border-radius: 2px; display: inline-block; vertical-align: bottom; }
    .eq-bar:nth-child(1) { animation: eq1 1.2s ease-in-out infinite; }
    .eq-bar:nth-child(2) { animation: eq2 1.0s ease-in-out infinite 0.1s; }
    .eq-bar:nth-child(3) { animation: eq3 1.4s ease-in-out infinite 0.2s; }
    .eq-bar:nth-child(4) { animation: eq4 0.9s ease-in-out infinite 0.3s; }
    .eq-bar:nth-child(5) { animation: eq5 1.1s ease-in-out infinite 0.15s; }

    /* ===== Browser Frame ===== */
    .browser-frame {
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 0 40px rgba(239, 68, 68, 0.2), 0 0 80px rgba(239, 68, 68, 0.1);
        transition: box-shadow 0.3s;
    }
    .browser-frame:hover {
        box-shadow: 0 0 60px rgba(239, 68, 68, 0.3), 0 0 100px rgba(239, 68, 68, 0.15);
    }
    .browser-dots span {
        width: 10px; height: 10px; border-radius: 50%; display: inline-block;
    }

    /* ===== Count Up ===== */
    .count-up { display: inline-block; }

    /* Blog card art styles are in app.css */

    /* ===== Tech Stack Icons ===== */
    .tech-icon {
        transition: all 0.3s ease;
    }
    .tech-icon:hover {
        transform: translateY(-4px);
        filter: brightness(1.3);
    }

    /* ===== Newsletter Glow ===== */
    .newsletter-input:focus {
        box-shadow: 0 0 0 2px rgba(74, 127, 191, 0.4);
        outline: none;
    }

    /* ===== Magnetic Button ===== */
    .magnetic-btn {
        transition: transform 0.2s ease-out;
        will-change: transform;
    }

    /* ===== Bento Grid for Projects ===== */
    .bento-projects {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 2rem;
    }
    .bento-projects > :first-child {
        grid-column: span 2;
    }
    @media (max-width: 767px) {
        .bento-projects {
            grid-template-columns: 1fr;
        }
        .bento-projects > :first-child {
            grid-column: span 1;
        }
    }

    /* ===== Bento Grid for Posts ===== */
    .bento-posts {
        display: grid;
        grid-template-columns: 1fr 1fr;
        grid-template-rows: auto auto;
        gap: 1.5rem;
    }
    .bento-posts > :first-child {
        grid-row: span 2;
    }
    .bento-posts > :first-child .aspect-video {
        aspect-ratio: auto;
        height: 100%;
        min-height: 200px;
    }
    @media (max-width: 767px) {
        .bento-posts {
            grid-template-columns: 1fr;
        }
        .bento-posts > :first-child {
            grid-row: span 1;
        }
    }
</style>

{{-- ===== HERO ===== --}}
<section class="hero-mesh noise-overlay relative overflow-hidden min-h-screen flex items-center">
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-28 z-10">
        <div class="flex flex-col lg:flex-row items-center lg:items-stretch gap-12 lg:gap-12">
            {{-- Left: Text Content --}}
            <div class="flex-1 text-center lg:text-left">
                <h1 class="text-4xl sm:text-5xl lg:text-7xl font-extrabold tracking-tight leading-tight mb-2 text-white">
                    I don't just write code—
                    <br>
                    I <span class="laravel-glow text-5xl sm:text-6xl lg:text-8xl">architect</span> it.
                </h1>
                <div class="mb-6">
                    <span class="laravel-glow text-2xl sm:text-3xl lg:text-4xl font-bold tracking-wide">Laravel</span>
                </div>

                <div class="text-xl sm:text-2xl text-gray-400 mb-4">
                    Crafting <span class="scramble-wrapper"><span class="scramble-text text-brand-300" id="scramble-text"></span></span>
                </div>

                <p class="text-xl text-gray-500 mb-10 max-w-2xl">
                    15 years of building Laravel applications that scale. I write about the craft,
                    I'm launching two podcasts, and I help developers build things they're proud of.
                </p>

                <div class="flex flex-wrap justify-center lg:justify-start gap-4 mb-10">
                    <a href="{{ route('blog.index') }}" class="magnetic-btn glow-btn inline-flex items-center px-8 py-3.5 bg-brand-600 hover:bg-brand-500 text-white font-semibold rounded-lg">
                        Read the Blog
                    </a>
                    <a href="{{ route('projects.index') }}" class="magnetic-btn glow-btn glow-btn-outline inline-flex items-center px-8 py-3.5 border border-brand-700 text-gray-300 font-semibold rounded-lg transition-all">
                        View Projects
                    </a>
                </div>

                {{-- Mobile Code Editor Preview --}}
                <div class="lg:hidden mb-10 code-editor rounded-lg overflow-hidden text-[11px] leading-5 font-mono" style="animation:none;">
                    <div class="code-editor-bar px-3 py-2 flex items-center gap-2">
                        <div class="flex gap-1">
                            <span class="w-2 h-2 rounded-full bg-red-500/80"></span>
                            <span class="w-2 h-2 rounded-full bg-yellow-500/80"></span>
                            <span class="w-2 h-2 rounded-full bg-green-500/80"></span>
                        </div>
                        <span class="code-editor-tab px-2 py-0.5 text-[10px] text-gray-300">Architect.php</span>
                    </div>
                    <div class="p-3 space-y-0.5">
                        <div class="flex gap-2"><span class="code-line-number text-[10px]">1</span><span><span class="syn-keyword">class</span> <span class="syn-class">Architect</span> <span class="syn-keyword">extends</span> <span class="syn-class">Model</span> <span class="syn-bracket">{</span></span></div>
                        <div class="flex gap-2 code-line-highlight rounded"><span class="code-line-number text-[10px]">2</span><span>&nbsp;&nbsp;<span class="syn-keyword">protected</span> <span class="syn-variable">$name</span> = <span class="syn-string">'Jeffrey Davidson'</span>;</span></div>
                        <div class="flex gap-2"><span class="code-line-number text-[10px]">3</span><span>&nbsp;&nbsp;<span class="syn-keyword">protected</span> <span class="syn-variable">$title</span> = <span class="syn-string">'The Laravel Architect'</span>;</span></div>
                        <div class="flex gap-2 code-line-highlight rounded"><span class="code-line-number text-[10px]">4</span><span>&nbsp;&nbsp;<span class="syn-keyword">protected</span> <span class="syn-variable">$available_for_hire</span> = <span class="syn-keyword">true</span>;</span></div>
                        <div class="flex gap-2"><span class="code-line-number text-[10px]">5</span><span>&nbsp;&nbsp;<span class="syn-keyword">protected</span> <span class="syn-variable">$skills</span> = [<span class="syn-string">'Laravel'</span>, <span class="syn-string">'PHP'</span>, ...];</span></div>
                        <div class="flex gap-2"><span class="code-line-number text-[10px]">6</span><span>&nbsp;</span></div>
                        <div class="flex gap-2"><span class="code-line-number text-[10px]">7</span><span>&nbsp;&nbsp;<span class="syn-keyword">public function</span> <span class="syn-function">philosophy</span>(): <span class="syn-class">string</span></span></div>
                        <div class="flex gap-2"><span class="code-line-number text-[10px]">8</span><span>&nbsp;&nbsp;<span class="syn-bracket">{</span></span></div>
                        <div class="flex gap-2"><span class="code-line-number text-[10px]">9</span><span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-keyword">return</span> <span class="syn-string">'Build it clean, build it right,'</span></span></div>
                        <div class="flex gap-2"><span class="code-line-number text-[10px]">10</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;. <span class="syn-string">' then teach someone how.'</span>;</span></div>
                        <div class="flex gap-2"><span class="code-line-number text-[10px]">11</span><span>&nbsp;&nbsp;<span class="syn-bracket">}</span></span></div>
                        <div class="flex gap-2"><span class="code-line-number text-[10px]">12</span><span><span class="syn-bracket">}</span></span></div>
                    </div>
                </div>

                {{-- Social links --}}
                <div class="flex items-center justify-center lg:justify-start gap-6">
                    <a href="https://youtube.com/channel/UC42H30o7l5QvvCzC86dSu_A" target="_blank" class="text-gray-500 hover:text-red-500 transition-colors" title="YouTube">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                    </a>
                    <a href="https://instagram.com/thelaravelarch" target="_blank" class="text-gray-500 hover:text-pink-500 transition-colors" title="Instagram">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z"/></svg>
                    </a>
                    <a href="https://twitter.com/thelaravelarch" target="_blank" class="text-gray-500 hover:text-white transition-colors" title="X / Twitter">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                    </a>
                    <a href="https://bsky.app/profile/thelaravelarch" target="_blank" class="text-gray-500 hover:text-blue-400 transition-colors" title="Bluesky">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 10.8c-1.087-2.114-4.046-6.053-6.798-7.995C2.566.944 1.561 1.266.902 1.565.139 1.908 0 3.08 0 3.768c0 .69.378 5.65.624 6.479.785 2.627 3.6 3.476 6.153 3.228-4.488.744-8.126 3.46-4.542 7.522 3.84 3.793 5.903-.686 6.765-3.127.181-.511.264-.749.3-.549.036-.2.119.038.3.549.863 2.44 2.925 6.92 6.765 3.127 3.584-4.063-.054-6.778-4.542-7.522 2.554.248 5.368-.601 6.153-3.228C18.622 9.418 19 4.458 19 3.768c0-.688-.139-1.86-.902-2.203-.659-.299-1.664-.621-4.3 1.24C11.046 4.747 8.087 8.686 7 10.8z"/></svg>
                    </a>
                    <a href="https://facebook.com/thelaravelarch" target="_blank" class="text-gray-500 hover:text-blue-500 transition-colors" title="Facebook">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                </div>
            </div>

            {{-- Right: Code Editor --}}
            <div class="hidden lg:flex lg:flex-col flex-1 min-w-0" id="code-editor-wrapper">
                <div class="code-editor flex flex-col h-full" id="code-editor">
                    {{-- Title bar --}}
                    <div class="code-editor-bar px-4 py-2.5 flex items-center gap-3">
                        <div class="flex gap-1.5">
                            <span class="w-3 h-3 rounded-full bg-red-500/80"></span>
                            <span class="w-3 h-3 rounded-full bg-yellow-500/80"></span>
                            <span class="w-3 h-3 rounded-full bg-green-500/80"></span>
                        </div>
                        <div class="flex items-center ml-2">
                            <button onclick="switchTab('routes')" id="tab-routes" class="code-editor-tab px-3 py-1 text-xs text-gray-300 flex items-center gap-1.5 cursor-pointer">
                                <svg class="w-3.5 h-3.5 text-red-400" viewBox="0 0 24 24" fill="currentColor"><path d="M23.642 5.43a.364.364 0 0 1 .014.1v5.149c0 .135-.073.26-.189.326l-4.323 2.49v4.934c0 .135-.073.26-.189.326l-9.037 5.206a.35.35 0 0 1-.128.049c-.01.004-.02.005-.03.01a.35.35 0 0 1-.2 0c-.013-.005-.025-.004-.038-.01a.376.376 0 0 1-.126-.049L.378 18.755a.378.378 0 0 1-.189-.326V3.334c0-.034.005-.07.014-.1.003-.012.01-.02.014-.032a.369.369 0 0 1 .023-.058c.004-.013.015-.022.023-.033.012-.015.021-.032.036-.045.01-.01.025-.018.037-.027.014-.012.027-.024.041-.034h.001L4.896.384a.378.378 0 0 1 .378 0L9.79 3.01h.002l.04.033.038.028c.014.013.023.03.035.045l.024.033c.01.019.015.038.024.058.005.012.011.02.013.033a.363.363 0 0 1 .015.1v9.652l3.76-2.164V5.527c0-.034.005-.07.013-.1l.015-.033c.008-.02.014-.039.023-.058.01-.013.016-.022.024-.033.011-.015.02-.032.035-.045.012-.01.025-.018.038-.027l.04-.034h.002l4.518-2.624a.378.378 0 0 1 .377 0l4.518 2.624c.015.01.027.021.042.033.012.01.025.018.036.028.016.013.025.03.037.045l.023.033c.01.019.017.038.024.058.005.012.011.02.014.033z"/></svg>
                                web.php
                            </button>
                            <button onclick="switchTab('architect')" id="tab-architect" class="code-editor-tab-inactive px-3 py-1 text-xs text-gray-500 flex items-center gap-1.5 cursor-pointer">
                                <svg class="w-3.5 h-3.5 text-purple-400" viewBox="0 0 24 24" fill="currentColor"><path d="M7.01 10.207h-.944l-.515 2.648h.838c.556 0 .97-.105 1.242-.314.272-.21.455-.559.55-1.049.092-.47.05-.802-.124-.995-.175-.193-.523-.29-1.047-.29zM12 5.688C5.373 5.688 0 8.514 0 12s5.373 6.313 12 6.313S24 15.486 24 12c0-3.486-5.373-6.312-12-6.312z"/></svg>
                                Architect.php
                            </button>
                            <button onclick="switchTab('test')" id="tab-test" class="code-editor-tab-inactive px-3 py-1 text-xs text-gray-500 flex items-center gap-1.5 cursor-pointer">
                                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                ArchitectTest.php
                            </button>
                        </div>
                    </div>
                    {{-- Code body: web.php --}}
                    <div id="code-routes" class="code-panel p-5 font-mono text-[13px] leading-6 overflow-y-auto flex-1">
                        <div class="flex gap-4"><span class="code-line-number"> 1</span><span><span class="syn-comment">// routes/web.php</span></span></div>
                        <div class="flex gap-4"><span class="code-line-number"> 2</span><span><span class="syn-keyword">use</span> <span class="syn-class">App\Http\Controllers\ArchitectController</span>;</span></div>
                        <div class="flex gap-4"><span class="code-line-number"> 3</span><span>&nbsp;</span></div>
                        <div class="flex gap-4 code-line-highlight rounded"><span class="code-line-number"> 4</span><span><span class="syn-class">Route</span>::<span class="syn-method">middleware</span>(<span class="syn-string">'architect'</span>)</span></div>
                        <div class="flex gap-4 code-line-highlight rounded"><span class="code-line-number"> 5</span><span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-arrow">-></span><span class="syn-method">group</span>(<span class="syn-keyword">function</span> () <span class="syn-bracket">{</span></span></div>
                        <div class="flex gap-4"><span class="code-line-number"> 6</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-class">Route</span>::<span class="syn-method">get</span>(<span class="syn-string">'/blog'</span>, <span class="syn-bracket">[</span></span></div>
                        <div class="flex gap-4"><span class="code-line-number"> 7</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-class">ArchitectController</span>::<span class="syn-keyword">class</span>,</span></div>
                        <div class="flex gap-4"><span class="code-line-number"> 8</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-string">'share'</span></span></div>
                        <div class="flex gap-4"><span class="code-line-number"> 9</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-bracket">]</span>);</span></div>
                        <div class="flex gap-4"><span class="code-line-number">10</span><span>&nbsp;</span></div>
                        <div class="flex gap-4"><span class="code-line-number">11</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-class">Route</span>::<span class="syn-method">get</span>(<span class="syn-string">'/podcasts'</span>, <span class="syn-bracket">[</span></span></div>
                        <div class="flex gap-4"><span class="code-line-number">12</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-class">ArchitectController</span>::<span class="syn-keyword">class</span>,</span></div>
                        <div class="flex gap-4"><span class="code-line-number">13</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-string">'discuss'</span></span></div>
                        <div class="flex gap-4"><span class="code-line-number">14</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-bracket">]</span>);</span></div>
                        <div class="flex gap-4"><span class="code-line-number">15</span><span>&nbsp;</span></div>
                        <div class="flex gap-4"><span class="code-line-number">16</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-class">Route</span>::<span class="syn-method">get</span>(<span class="syn-string">'/projects'</span>, <span class="syn-bracket">[</span></span></div>
                        <div class="flex gap-4"><span class="code-line-number">17</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-class">ArchitectController</span>::<span class="syn-keyword">class</span>,</span></div>
                        <div class="flex gap-4"><span class="code-line-number">18</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-string">'build'</span></span></div>
                        <div class="flex gap-4"><span class="code-line-number">19</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-bracket">]</span>);</span></div>
                        <div class="flex gap-4"><span class="code-line-number">20</span><span>&nbsp;</span></div>
                        <div class="flex gap-4"><span class="code-line-number">21</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-class">Route</span>::<span class="syn-method">get</span>(<span class="syn-string">'/about'</span>, <span class="syn-bracket">[</span></span></div>
                        <div class="flex gap-4"><span class="code-line-number">22</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-class">ArchitectController</span>::<span class="syn-keyword">class</span>,</span></div>
                        <div class="flex gap-4"><span class="code-line-number">23</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-string">'introduce'</span></span></div>
                        <div class="flex gap-4"><span class="code-line-number">24</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-bracket">]</span>);</span></div>
                        <div class="flex gap-4"><span class="code-line-number">25</span><span>&nbsp;</span></div>
                        <div class="flex gap-4"><span class="code-line-number">26</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-class">Route</span>::<span class="syn-method">get</span>(<span class="syn-string">'/uses'</span>, <span class="syn-bracket">[</span></span></div>
                        <div class="flex gap-4"><span class="code-line-number">27</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-class">ArchitectController</span>::<span class="syn-keyword">class</span>,</span></div>
                        <div class="flex gap-4"><span class="code-line-number">28</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-string">'equipment'</span></span></div>
                        <div class="flex gap-4"><span class="code-line-number">29</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-bracket">]</span>);</span></div>
                        <div class="flex gap-4"><span class="code-line-number">30</span><span>&nbsp;</span></div>
                        <div class="flex gap-4"><span class="code-line-number">31</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-class">Route</span>::<span class="syn-method">get</span>(<span class="syn-string">'/hire-me'</span>, <span class="syn-bracket">[</span></span></div>
                        <div class="flex gap-4"><span class="code-line-number">32</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-class">ArchitectController</span>::<span class="syn-keyword">class</span>,</span></div>
                        <div class="flex gap-4"><span class="code-line-number">33</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-string">'collaborate'</span></span></div>
                        <div class="flex gap-4"><span class="code-line-number">34</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-bracket">]</span>);</span></div>
                        <div class="flex gap-4"><span class="code-line-number">35</span><span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-bracket">}</span>);</span></div>
                    </div>
                    {{-- Code body: Architect.php --}}
                    <div id="code-architect" class="code-panel p-5 font-mono text-[13px] leading-6 overflow-y-auto flex-1 hidden">
                        <div class="flex gap-4"><span class="code-line-number"> 1</span><span><span class="syn-keyword">&lt;?php</span></span></div>
                        <div class="flex gap-4"><span class="code-line-number"> 2</span><span>&nbsp;</span></div>
                        <div class="flex gap-4"><span class="code-line-number"> 3</span><span><span class="syn-keyword">namespace</span> <span class="syn-class">App\Models</span>;</span></div>
                        <div class="flex gap-4"><span class="code-line-number"> 4</span><span>&nbsp;</span></div>
                        <div class="flex gap-4"><span class="code-line-number"> 5</span><span><span class="syn-keyword">class</span> <span class="syn-class">Architect</span> <span class="syn-keyword">extends</span> <span class="syn-class">Model</span></span></div>
                        <div class="flex gap-4"><span class="code-line-number"> 6</span><span><span class="syn-bracket">{</span></span></div>
                        <div class="flex gap-4 code-line-highlight rounded"><span class="code-line-number"> 7</span><span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-keyword">protected</span> <span class="syn-variable">$name</span> = <span class="syn-string">'Jeffrey Davidson'</span>;</span></div>
                        <div class="flex gap-4"><span class="code-line-number"> 8</span><span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-keyword">protected</span> <span class="syn-variable">$title</span> = <span class="syn-string">'The Laravel Architect'</span>;</span></div>
                        <div class="flex gap-4"><span class="code-line-number"> 9</span><span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-keyword">protected</span> <span class="syn-variable">$location</span> = <span class="syn-string">'Florida, USA'</span>;</span></div>
                        <div class="flex gap-4 code-line-highlight rounded"><span class="code-line-number">10</span><span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-keyword">protected</span> <span class="syn-variable">$available_for_hire</span> = <span class="syn-keyword">true</span>;</span></div>
                        <div class="flex gap-4"><span class="code-line-number">11</span><span>&nbsp;</span></div>
                        <div class="flex gap-4"><span class="code-line-number">12</span><span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-keyword">protected</span> <span class="syn-variable">$skills</span> = <span class="syn-bracket">[</span></span></div>
                        <div class="flex gap-4"><span class="code-line-number">13</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-string">'Laravel'</span>, <span class="syn-string">'PHP'</span>, <span class="syn-string">'Filament'</span>,</span></div>
                        <div class="flex gap-4"><span class="code-line-number">14</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-string">'Livewire'</span>, <span class="syn-string">'Tailwind CSS'</span>,</span></div>
                        <div class="flex gap-4"><span class="code-line-number">15</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-string">'MySQL'</span>, <span class="syn-string">'Redis'</span>, <span class="syn-string">'Alpine.js'</span>,</span></div>
                        <div class="flex gap-4"><span class="code-line-number">16</span><span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-bracket">]</span>;</span></div>
                        <div class="flex gap-4"><span class="code-line-number">17</span><span>&nbsp;</span></div>
                        <div class="flex gap-4"><span class="code-line-number">18</span><span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-keyword">protected</span> <span class="syn-variable">$passions</span> = <span class="syn-bracket">[</span></span></div>
                        <div class="flex gap-4"><span class="code-line-number">19</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-string">'Clean Architecture'</span>,</span></div>
                        <div class="flex gap-4"><span class="code-line-number">20</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-string">'Content Creation'</span>,</span></div>
                        <div class="flex gap-4"><span class="code-line-number">21</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-string">'Teaching Others'</span>,</span></div>
                        <div class="flex gap-4"><span class="code-line-number">22</span><span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-bracket">]</span>;</span></div>
                        <div class="flex gap-4"><span class="code-line-number">23</span><span>&nbsp;</span></div>
                        <div class="flex gap-4"><span class="code-line-number">24</span><span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-keyword">protected</span> <span class="syn-variable">$podcasts</span> = <span class="syn-bracket">[</span></span></div>
                        <div class="flex gap-4"><span class="code-line-number">25</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-string">'Coffee With The Laravel Architect'</span>,</span></div>
                        <div class="flex gap-4"><span class="code-line-number">26</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-string">'Embracing Cloudy Days'</span>,</span></div>
                        <div class="flex gap-4"><span class="code-line-number">27</span><span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-bracket">]</span>;</span></div>
                        <div class="flex gap-4"><span class="code-line-number">28</span><span>&nbsp;</span></div>
                        <div class="flex gap-4"><span class="code-line-number">29</span><span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-keyword">public function</span> <span class="syn-function">experience</span>(): <span class="syn-class">int</span></span></div>
                        <div class="flex gap-4"><span class="code-line-number">30</span><span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-bracket">{</span></span></div>
                        <div class="flex gap-4 code-line-highlight rounded"><span class="code-line-number">31</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-keyword">return</span> <span class="syn-class">Carbon</span>::<span class="syn-method">parse</span>(<span class="syn-string">'2011'</span>)</span></div>
                        <div class="flex gap-4 code-line-highlight rounded"><span class="code-line-number">32</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-arrow">-></span><span class="syn-method">diffInYears</span>(<span class="syn-method">now</span>()); <span class="syn-comment">// 15+</span></span></div>
                        <div class="flex gap-4"><span class="code-line-number">33</span><span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-bracket">}</span></span></div>
                        <div class="flex gap-4"><span class="code-line-number">34</span><span>&nbsp;</span></div>
                        <div class="flex gap-4"><span class="code-line-number">35</span><span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-keyword">public function</span> <span class="syn-function">philosophy</span>(): <span class="syn-class">string</span></span></div>
                        <div class="flex gap-4"><span class="code-line-number">36</span><span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-bracket">{</span></span></div>
                        <div class="flex gap-4"><span class="code-line-number">37</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-keyword">return</span> <span class="syn-string">'Build it clean,'</span></span></div>
                        <div class="flex gap-4"><span class="code-line-number">38</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;. <span class="syn-string">' build it right,'</span></span></div>
                        <div class="flex gap-4"><span class="code-line-number">39</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;. <span class="syn-string">' then teach someone how.'</span>;</span></div>
                        <div class="flex gap-4"><span class="code-line-number">40</span><span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-bracket">}</span></span></div>
                        <div class="flex gap-4"><span class="code-line-number">41</span><span><span class="syn-bracket">}</span></span></div>
                    </div>
                    {{-- Code body: ArchitectTest.php --}}
                    <div id="code-test" class="code-panel p-5 font-mono text-[13px] leading-6 overflow-y-auto flex-1 hidden">
                        <div class="flex gap-4"><span class="code-line-number"> 1</span><span><span class="syn-comment">// tests/Feature/ArchitectTest.php</span></span></div>
                        <div class="flex gap-4"><span class="code-line-number"> 2</span><span>&nbsp;</span></div>
                        <div class="flex gap-4"><span class="code-line-number"> 3</span><span><span class="syn-keyword">use</span> <span class="syn-class">App\Models\Architect</span>;</span></div>
                        <div class="flex gap-4"><span class="code-line-number"> 4</span><span>&nbsp;</span></div>
                        <div class="flex gap-4 code-line-highlight rounded"><span class="code-line-number"> 5</span><span><span class="syn-method">test</span>(<span class="syn-string">'architect has required skills'</span>, <span class="syn-keyword">function</span> () <span class="syn-bracket">{</span></span></div>
                        <div class="flex gap-4"><span class="code-line-number"> 6</span><span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-variable">$architect</span> = <span class="syn-class">Architect</span>::<span class="syn-method">first</span>();</span></div>
                        <div class="flex gap-4"><span class="code-line-number"> 7</span><span>&nbsp;</span></div>
                        <div class="flex gap-4"><span class="code-line-number"> 8</span><span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-method">expect</span>(<span class="syn-variable">$architect</span><span class="syn-arrow">-></span><span class="syn-text">skills</span>)</span></div>
                        <div class="flex gap-4"><span class="code-line-number"> 9</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-arrow">-></span><span class="syn-method">toContain</span>(<span class="syn-string">'Laravel'</span>)</span></div>
                        <div class="flex gap-4"><span class="code-line-number">10</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-arrow">-></span><span class="syn-method">toContain</span>(<span class="syn-string">'PHP'</span>)</span></div>
                        <div class="flex gap-4"><span class="code-line-number">11</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-arrow">-></span><span class="syn-method">toContain</span>(<span class="syn-string">'Filament'</span>)</span></div>
                        <div class="flex gap-4"><span class="code-line-number">12</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-arrow">-></span><span class="syn-method">toContain</span>(<span class="syn-string">'Livewire'</span>);</span></div>
                        <div class="flex gap-4"><span class="code-line-number">13</span><span><span class="syn-bracket">}</span>);</span></div>
                        <div class="flex gap-4"><span class="code-line-number">14</span><span>&nbsp;</span></div>
                        <div class="flex gap-4 code-line-highlight rounded"><span class="code-line-number">15</span><span><span class="syn-method">test</span>(<span class="syn-string">'architect is available for hire'</span>, <span class="syn-keyword">function</span> () <span class="syn-bracket">{</span></span></div>
                        <div class="flex gap-4"><span class="code-line-number">16</span><span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-variable">$architect</span> = <span class="syn-class">Architect</span>::<span class="syn-method">find</span>(<span class="syn-string">'jeffrey'</span>);</span></div>
                        <div class="flex gap-4"><span class="code-line-number">17</span><span>&nbsp;</span></div>
                        <div class="flex gap-4"><span class="code-line-number">18</span><span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-method">expect</span>(<span class="syn-variable">$architect</span><span class="syn-arrow">-></span><span class="syn-text">available_for_hire</span>)</span></div>
                        <div class="flex gap-4"><span class="code-line-number">19</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-arrow">-></span><span class="syn-method">toBeTrue</span>();</span></div>
                        <div class="flex gap-4"><span class="code-line-number">20</span><span><span class="syn-bracket">}</span>);</span></div>
                        <div class="flex gap-4"><span class="code-line-number">21</span><span>&nbsp;</span></div>
                        <div class="flex gap-4 code-line-highlight rounded"><span class="code-line-number">22</span><span><span class="syn-method">test</span>(<span class="syn-string">'architect delivers quality work'</span>, <span class="syn-keyword">function</span> () <span class="syn-bracket">{</span></span></div>
                        <div class="flex gap-4"><span class="code-line-number">23</span><span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-variable">$project</span> = <span class="syn-variable">$architect</span><span class="syn-arrow">-></span><span class="syn-method">build</span>(<span class="syn-string">'your-idea'</span>);</span></div>
                        <div class="flex gap-4"><span class="code-line-number">24</span><span>&nbsp;</span></div>
                        <div class="flex gap-4"><span class="code-line-number">25</span><span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-method">expect</span>(<span class="syn-variable">$project</span>)</span></div>
                        <div class="flex gap-4"><span class="code-line-number">26</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-arrow">-></span><span class="syn-method">toBeClean</span>()</span></div>
                        <div class="flex gap-4"><span class="code-line-number">27</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-arrow">-></span><span class="syn-method">toBeScalable</span>()</span></div>
                        <div class="flex gap-4"><span class="code-line-number">28</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-arrow">-></span><span class="syn-method">toBeWellTested</span>()</span></div>
                        <div class="flex gap-4"><span class="code-line-number">29</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-arrow">-></span><span class="syn-method">toBeDeliveredOnTime</span>();</span></div>
                        <div class="flex gap-4"><span class="code-line-number">30</span><span><span class="syn-bracket">}</span>);</span></div>
                        <div class="flex gap-4"><span class="code-line-number">31</span><span>&nbsp;</span></div>
                        <div class="flex gap-4"><span class="code-line-number">32</span><span><span class="syn-method">test</span>(<span class="syn-string">'architect never stops learning'</span>, <span class="syn-keyword">function</span> () <span class="syn-bracket">{</span></span></div>
                        <div class="flex gap-4"><span class="code-line-number">33</span><span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-variable">$architect</span> = <span class="syn-class">Architect</span>::<span class="syn-method">find</span>(<span class="syn-string">'jeffrey'</span>);</span></div>
                        <div class="flex gap-4"><span class="code-line-number">34</span><span>&nbsp;</span></div>
                        <div class="flex gap-4"><span class="code-line-number">35</span><span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-method">expect</span>(<span class="syn-variable">$architect</span><span class="syn-arrow">-></span><span class="syn-text">podcasts</span>)<span class="syn-arrow">-></span><span class="syn-method">toHaveCount</span>(<span class="syn-variable">2</span>);</span></div>
                        <div class="flex gap-4"><span class="code-line-number">36</span><span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-method">expect</span>(<span class="syn-variable">$architect</span><span class="syn-arrow">-></span><span class="syn-text">youtube</span>)<span class="syn-arrow">-></span><span class="syn-method">toBeActive</span>();</span></div>
                        <div class="flex gap-4"><span class="code-line-number">37</span><span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-method">expect</span>(<span class="syn-variable">$architect</span><span class="syn-arrow">-></span><span class="syn-text">blog</span>)<span class="syn-arrow">-></span><span class="syn-method">toBeActive</span>();</span></div>
                        <div class="flex gap-4"><span class="code-line-number">38</span><span><span class="syn-bracket">}</span>);</span></div>
                        <div class="flex gap-4"><span class="code-line-number">39</span><span>&nbsp;</span></div>
                        <div class="flex gap-4"><span class="code-line-number">40</span><span><span class="syn-comment">// ✓ All tests passed (4 tests, 12 assertions)</span></span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ===== TECH STACK MARQUEE ===== --}}
<section class="py-8 overflow-hidden relative" style="background: #0D1117;">
    <div class="marquee-fade-left"></div>
    <div class="marquee-fade-right"></div>
    <div class="marquee-track">
        @for($i = 0; $i < 2; $i++)
        <div class="marquee-item">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M23.642 5.43a.364.364 0 01.014.1v5.149c0 .135-.073.26-.189.326l-4.323 2.49v4.934c0 .135-.073.26-.189.326l-9.037 5.206a.35.35 0 01-.128.049c-.01.004-.02.005-.03.01a.35.35 0 01-.2 0c-.013-.005-.025-.004-.038-.01a.376.376 0 01-.126-.049L.378 18.755a.378.378 0 01-.189-.326V3.334c0-.034.005-.07.014-.1.003-.012.01-.02.014-.032a.369.369 0 01.023-.058c.004-.013.015-.022.023-.033.012-.015.021-.032.036-.045.01-.01.025-.018.037-.027.014-.012.027-.024.041-.034h.001L4.896.384a.378.378 0 01.378 0L9.79 3.01h.002l.04.033.038.028c.014.013.023.03.035.045l.024.033c.01.019.015.038.024.058.005.012.011.02.013.033a.363.363 0 01.015.1v9.652l3.76-2.164V5.527c0-.034.005-.07.013-.1l.015-.033c.008-.02.014-.039.023-.058.01-.013.016-.022.024-.033.011-.015.02-.032.035-.045.012-.01.025-.018.038-.027l.04-.034h.002l4.518-2.624a.378.378 0 01.377 0l4.518 2.624c.015.01.027.021.042.033.012.01.025.018.036.028.016.013.025.03.037.045l.023.033c.01.019.017.038.024.058.005.012.011.02.014.033z"/></svg>
            Laravel
        </div>
        <div class="marquee-item">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M7.01 10.207h-.944l-.515 2.648h.838c.556 0 .97-.105 1.242-.314.272-.21.455-.559.55-1.049.092-.47.05-.802-.124-.995-.175-.193-.523-.29-1.047-.29zM12 5.688C5.373 5.688 0 8.514 0 12s5.373 6.313 12 6.313S24 15.486 24 12c0-3.486-5.373-6.312-12-6.312zm3.542 7.09c-.2.64-.54 1.122-.993 1.418-.46.302-1.048.453-1.74.453h-.876l-.417 2.146H9.869l1.469-7.563h2.535c.715 0 1.222.234 1.509.688.29.454.354 1.072.16 1.858z"/></svg>
            PHP
        </div>
        <div class="marquee-item">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 0L1.608 6v12L12 24l10.392-6V6zm-1.2 17.4H8.4V13.2H6V10.8h2.4V6.6h2.4v4.2h2.4v2.4h-2.4zm6 0h-2.4V13.2H12V10.8h2.4V6.6h2.4v4.2H19.2v2.4h-2.4z"/></svg>
            Filament
        </div>
        <div class="marquee-item">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12.001 4.8c-3.2 0-5.2 1.6-6 4.8 1.2-1.6 2.6-2.2 4.2-1.8.913.228 1.565.89 2.288 1.624C13.666 10.618 15.027 12 18.001 12c3.2 0 5.2-1.6 6-4.8-1.2 1.6-2.6 2.2-4.2 1.8-.913-.228-1.565-.89-2.288-1.624C16.337 6.182 14.976 4.8 12.001 4.8zm-6 7.2c-3.2 0-5.2 1.6-6 4.8 1.2-1.6 2.6-2.2 4.2-1.8.913.228 1.565.89 2.288 1.624 1.177 1.194 2.538 2.576 5.512 2.576 3.2 0 5.2-1.6 6-4.8-1.2 1.6-2.6 2.2-4.2 1.8-.913-.228-1.565-.89-2.288-1.624C10.337 13.382 8.976 12 6.001 12z"/></svg>
            Tailwind CSS
        </div>
        <div class="marquee-item">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M16.5 3c-1.74 0-3.41.81-4.5 2.09C10.91 3.81 9.24 3 7.5 3 4.42 3 2 5.42 2 8.5c0 3.78 3.4 6.86 8.55 11.54L12 21.35l1.45-1.32C18.6 15.36 22 12.28 22 8.5 22 5.42 19.58 3 16.5 3zm-4.4 15.55l-.1.1-.1-.1C7.14 14.24 4 11.39 4 8.5 4 6.5 5.5 5 7.5 5c1.54 0 3.04.99 3.57 2.36h1.87C13.46 5.99 14.96 5 16.5 5c2 0 3.5 1.5 3.5 3.5 0 2.89-3.14 5.74-7.9 10.05z"/></svg>
            Livewire
        </div>
        <div class="marquee-item">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M13.527.099C6.955-.744.942 3.9.099 10.473c-.843 6.572 3.8 12.584 10.373 13.428 6.573.843 12.587-3.801 13.428-10.374C24.744 6.955 20.101.943 13.527.099zm2.471 7.485a.855.855 0 00-.593.25l-4.453 4.453-.307-.307-.643-.643c4.389-4.376 5.18-4.418 5.996-3.753zm-4.863 4.861l4.44-4.44c.718-.32 1.755-.111 1.755.67 0 .324-.09.636-.344.89l-2.084 2.084-2.084 2.084a1.346 1.346 0 01-.89.344c-.781 0-.99-1.037-.67-1.755l-.123.123z"/></svg>
            Alpine.js
        </div>
        <div class="marquee-item">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.924 8.382l-5.808 9.614a.413.413 0 01-.708 0L5.6 8.382a.413.413 0 01.354-.627h3.742a.413.413 0 01.354.2l1.862 3.084a.413.413 0 00.708 0l1.862-3.084a.413.413 0 01.354-.2h3.742a.413.413 0 01.354.627z"/></svg>
            Vite
        </div>
        <div class="marquee-item">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm-.1 4.5h.2c3.3 0 5.6 2.1 5.6 5.2v.1c0 2-.9 3.4-2.4 4.3l3 4.3h-2.8l-2.6-3.8h-2.2v3.8H8.3V4.5h3.6zm.3 2.2h-1.4v4.8h1.4c2.1 0 3.2-.9 3.2-2.4v-.1c0-1.5-1.1-2.3-3.2-2.3z"/></svg>
            Redis
        </div>
        <div class="marquee-item">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M0 0h24v24H0V0zm22.034 18.276c-.175-1.095-.888-2.015-3.003-2.873-.736-.345-1.554-.585-1.797-1.14-.091-.33-.105-.51-.046-.705.15-.646.915-.84 1.515-.66.39.12.75.42.976.9 1.034-.676 1.034-.676 1.755-1.125-.27-.42-.405-.6-.586-.78-.63-.705-1.469-1.065-2.834-1.034l-.705.089c-.676.165-1.32.525-1.71 1.005-1.14 1.291-.811 3.541.569 4.471 1.365 1.02 3.361 1.244 3.616 2.205.24 1.17-.87 1.545-1.966 1.41-.811-.18-1.26-.586-1.755-1.336l-1.83 1.051c.21.48.45.689.81 1.109 1.74 1.756 6.09 1.666 6.871-1.004.029-.09.24-.705.074-1.65zm-8.983-7.245h-2.248c0 1.938-.009 3.864-.009 5.805 0 1.232.063 2.363-.138 2.711-.33.689-1.18.601-1.566.48-.396-.196-.597-.466-.83-.855-.063-.105-.11-.196-.127-.196l-1.825 1.125c.305.63.75 1.172 1.324 1.517.855.51 2.004.675 3.207.405.783-.226 1.458-.691 1.811-1.411.51-.93.402-2.07.397-3.346.012-2.054 0-4.109 0-6.179z"/></svg>
            JavaScript
        </div>
        <div class="marquee-item">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12.001 4.8c-3.2 0-5.2 1.6-6 4.8 1.2-1.6 2.6-2.2 4.2-1.8.913.228 1.565.89 2.288 1.624C13.666 10.618 15.027 12 18.001 12c3.2 0 5.2-1.6 6-4.8-1.2 1.6-2.6 2.2-4.2 1.8-.913-.228-1.565-.89-2.288-1.624C16.337 6.182 14.976 4.8 12.001 4.8zm-6 7.2c-3.2 0-5.2 1.6-6 4.8 1.2-1.6 2.6-2.2 4.2-1.8.913.228 1.565.89 2.288 1.624 1.177 1.194 2.538 2.576 5.512 2.576 3.2 0 5.2-1.6 6-4.8-1.2 1.6-2.6 2.2-4.2 1.8-.913-.228-1.565-.89-2.288-1.624C10.337 13.382 8.976 12 6.001 12z"/></svg>
            CSS
        </div>
        <div class="marquee-item">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/></svg>
            GitHub
        </div>
        <div class="marquee-item">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M0 0h24v24H0z" fill="none"/><path d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58a.49.49 0 00.12-.61l-1.92-3.32a.488.488 0 00-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54a.484.484 0 00-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.07.62-.07.94s.02.64.07.94l-2.03 1.58a.49.49 0 00-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"/></svg>
            Forge
        </div>
        @endfor
    </div>
</section>

{{-- ===== STATS BAR ===== --}}
<section class="py-16 border-y border-[#1e2a3a]/50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between fade-up">
            <div class="flex-1 text-center">
                <div class="stat-number count-up" data-target="15">0</div>
                <div class="text-xs text-gray-500 uppercase tracking-widest mt-2 font-semibold">Years Experience</div>
            </div>
            <div class="stat-divider hidden sm:block"></div>
            <div class="flex-1 text-center">
                <div class="stat-number count-up" data-target="6">0</div>
                <div class="text-xs text-gray-500 uppercase tracking-widest mt-2 font-semibold">Open Source Projects</div>
            </div>
            <div class="stat-divider hidden sm:block"></div>
            <div class="flex-1 text-center">
                <div class="stat-number count-up" data-target="2">0</div>
                <div class="text-xs text-gray-500 uppercase tracking-widest mt-2 font-semibold">Podcasts Launching</div>
            </div>
            <div class="stat-divider hidden sm:block"></div>
            <div class="flex-1 text-center">
                <div class="stat-number"><span class="count-up" data-target="1000">0</span><span style="background:linear-gradient(135deg,#fff,#8b949e);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">+</span></div>
                <div class="text-xs text-gray-500 uppercase tracking-widest mt-2 font-semibold">Cups of Coffee</div>
            </div>
        </div>
    </div>
</section>

{{-- Section Divider --}}
<div class="section-divider section-divider-dark"></div>

{{-- ===== FEATURED PROJECTS ===== --}}
@if($featuredProjects->count())
<section class="py-20 noise-overlay dot-grid-bg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-12">
            <h2 class="text-4xl font-extrabold text-white">Featured Projects</h2>
            <a href="{{ route('projects.index') }}" class="text-sm text-brand-400 hover:text-brand-300 transition-colors">View all →</a>
        </div>
        <div class="bento-projects">
            @foreach($featuredProjects as $index => $project)
            <a href="{{ route('projects.show', $project) }}" class="glass-card group block rounded-xl overflow-hidden fade-up" data-glow-card>
                @if($project->featured_image)
                <div class="{{ $index === 0 ? 'aspect-[21/9]' : 'aspect-video' }} bg-brand-800 overflow-hidden">
                    <img src="{{ Storage::url($project->featured_image) }}" alt="{{ $project->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                </div>
                @endif
                <div class="p-6">
                    <h3 class="font-semibold {{ $index === 0 ? 'text-xl' : 'text-lg' }} text-white mb-2 group-hover:text-brand-400 transition-colors">{{ $project->title }}</h3>
                    <p class="text-gray-400 text-sm mb-4">{{ $project->description }}</p>
                    @if($project->tech_stack)
                    <div class="flex flex-wrap gap-2">
                        @foreach($project->tech_stack as $tech)
                        <span class="px-2.5 py-1 bg-brand-800/80 text-brand-300 text-xs font-medium rounded-md border border-brand-700/50">{{ $tech }}</span>
                        @endforeach
                    </div>
                    @endif
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- Section Divider --}}
<div class="section-divider section-divider-dark"></div>

{{-- ===== WHAT I DO ===== --}}
<style>
    @keyframes serviceGlow {
        0%, 100% { opacity: 0.4; transform: scale(1); }
        50% { opacity: 0.7; transform: scale(1.15); }
    }
    @keyframes serviceBorderSpin {
        0% { --service-angle: 0deg; }
        100% { --service-angle: 360deg; }
    }
    @property --service-angle {
        syntax: '<angle>';
        initial-value: 0deg;
        inherits: false;
    }
    .service-card-v2 {
        position: relative;
        background: rgba(13, 17, 23, 0.8);
        border-radius: 1rem;
        padding: 2.5rem;
        overflow: hidden;
        transition: all 0.4s ease;
        border: 1px solid rgba(255,255,255,0.06);
    }
    .service-card-v2::before {
        content: '';
        position: absolute;
        inset: -1px;
        border-radius: 1rem;
        padding: 1px;
        background: conic-gradient(from var(--service-angle), transparent 60%, var(--card-color) 80%, transparent 100%);
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        opacity: 0;
        transition: opacity 0.4s ease;
        animation: serviceBorderSpin 4s linear infinite;
    }
    .service-card-v2:hover::before {
        opacity: 1;
    }
    .service-card-v2:hover {
        transform: translateY(-6px);
        box-shadow: 0 25px 50px rgba(0,0,0,0.4), 0 0 40px var(--card-glow);
        border-color: transparent;
    }
    .service-orb {
        position: absolute;
        width: 120px;
        height: 120px;
        border-radius: 50%;
        filter: blur(50px);
        opacity: 0.15;
        animation: serviceGlow 3s ease-in-out infinite;
        pointer-events: none;
    }
    .service-card-v2:hover .service-orb {
        opacity: 0.3;
    }
    .service-number {
        font-family: 'JetBrains Mono', ui-monospace, monospace;
        font-size: 5rem;
        font-weight: 900;
        position: absolute;
        top: -0.5rem;
        right: 1rem;
        line-height: 1;
        opacity: 0.04;
        pointer-events: none;
    }
    .service-card-v2:hover .service-number {
        opacity: 0.08;
    }
    .service-icon-v2 {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1.5rem;
        position: relative;
        z-index: 1;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .service-card-v2:hover .service-icon-v2 {
        transform: scale(1.1) rotate(-5deg);
        box-shadow: 0 0 25px var(--card-glow);
    }
    .service-arrow {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        margin-top: 1.25rem;
        font-size: 0.8125rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    .service-arrow svg {
        transition: transform 0.3s ease;
    }
    .service-card-v2:hover .service-arrow svg {
        transform: translateX(4px);
    }
    /* Floating dots */
    .service-dots {
        position: absolute;
        inset: 0;
        overflow: hidden;
        pointer-events: none;
    }
    .service-dots span {
        position: absolute;
        width: 2px;
        height: 2px;
        border-radius: 50%;
        opacity: 0;
        transition: opacity 0.4s ease;
    }
    .service-card-v2:hover .service-dots span {
        opacity: 0.3;
        animation: floatDot 3s ease-in-out infinite;
    }
    @keyframes floatDot {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-15px); }
    }
</style>

<section class="py-20 noise-overlay">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14 fade-up">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-brand-500/20 bg-brand-500/5 text-brand-400 text-xs font-bold uppercase tracking-widest mb-6">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17l-5.38-5.38a1 1 0 010-1.41l.7-.7a1 1 0 011.41 0L12 11.5l3.85-3.85a1 1 0 011.41 0l.7.7a1 1 0 010 1.41l-5.38 5.38a1 1 0 01-1.16.04z"/></svg>
                Services
            </div>
            <h2 class="text-4xl sm:text-5xl font-extrabold text-white mb-4">What I Do</h2>
            <p class="text-gray-400 max-w-2xl mx-auto text-lg">From greenfield apps to legacy rescues — I help teams build software they can be proud of.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            {{-- Laravel Development --}}
            <div class="service-card-v2 fade-up" style="--card-color: #4A7FBF; --card-glow: rgba(74,127,191,0.15);">
                <div class="service-orb" style="background: #4A7FBF; top: -20px; right: -20px;"></div>
                <div class="service-dots">
                    <span class="bg-brand-400" style="top:20%;left:80%;animation-delay:0s;"></span>
                    <span class="bg-brand-400" style="top:60%;left:90%;animation-delay:0.5s;"></span>
                    <span class="bg-brand-400" style="top:40%;left:15%;animation-delay:1s;"></span>
                    <span class="bg-brand-400" style="top:80%;left:70%;animation-delay:1.5s;"></span>
                </div>
                <span class="service-number text-brand-400">01</span>
                <div class="relative z-10 mb-5 inline-block px-4 py-2.5 rounded-lg bg-[#0a0e14] border border-brand-500/20 font-mono text-sm">
                    <span class="text-gray-500">$</span> <span class="text-brand-400">php artisan</span> <span class="text-white">build</span><span class="animate-pulse text-brand-400">▊</span>
                </div>
                <h3 class="text-xl font-bold text-white mb-3 relative z-10">Laravel Development</h3>
                <p class="text-gray-400 text-sm leading-relaxed mb-5 relative z-10">Custom web applications, REST APIs, SaaS platforms, and admin dashboards built with Laravel and Filament.</p>
                <div class="flex flex-wrap gap-2 relative z-10">
                    <span class="px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wider text-brand-300 bg-brand-500/10 rounded-full border border-brand-500/20">APIs</span>
                    <span class="px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wider text-brand-300 bg-brand-500/10 rounded-full border border-brand-500/20">SaaS</span>
                    <span class="px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wider text-brand-300 bg-brand-500/10 rounded-full border border-brand-500/20">Filament</span>
                    <span class="px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wider text-brand-300 bg-brand-500/10 rounded-full border border-brand-500/20">Livewire</span>
                </div>
                <a href="{{ route('contact') }}" class="service-arrow text-brand-400 relative z-10">
                    Start a project <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
            </div>

            {{-- Legacy Modernization --}}
            <div class="service-card-v2 fade-up" style="--card-color: #E47A9D; --card-glow: rgba(228,122,157,0.15);">
                <div class="service-orb" style="background: #E47A9D; top: -20px; right: -20px;"></div>
                <div class="service-dots">
                    <span class="bg-accent-400" style="top:25%;left:85%;animation-delay:0.3s;"></span>
                    <span class="bg-accent-400" style="top:55%;left:10%;animation-delay:0.8s;"></span>
                    <span class="bg-accent-400" style="top:75%;left:75%;animation-delay:1.3s;"></span>
                    <span class="bg-accent-400" style="top:15%;left:20%;animation-delay:1.8s;"></span>
                </div>
                <span class="service-number text-accent-400">02</span>
                <div class="relative z-10 mb-5 inline-block px-4 py-2.5 rounded-lg bg-[#0a0e14] border border-accent-500/20 font-mono text-sm">
                    <span class="text-gray-500">$</span> <span class="text-accent-400">php artisan</span> <span class="text-white">modernize</span><span class="animate-pulse text-accent-400">▊</span>
                </div>
                <h3 class="text-xl font-bold text-white mb-3 relative z-10">Legacy Modernization</h3>
                <p class="text-gray-400 text-sm leading-relaxed mb-5 relative z-10">Migrating CodeIgniter, vanilla PHP, or aging frameworks to modern Laravel with tests, proper architecture, and CI/CD.</p>
                <div class="flex flex-wrap gap-2 relative z-10">
                    <span class="px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wider text-accent-300 bg-accent-500/10 rounded-full border border-accent-500/20">Migration</span>
                    <span class="px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wider text-accent-300 bg-accent-500/10 rounded-full border border-accent-500/20">Refactoring</span>
                    <span class="px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wider text-accent-300 bg-accent-500/10 rounded-full border border-accent-500/20">Testing</span>
                </div>
                <a href="{{ route('contact') }}" class="service-arrow text-accent-400 relative z-10">
                    Modernize now <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
            </div>

            {{-- Content & Teaching --}}
            <div class="service-card-v2 fade-up" style="--card-color: #22c55e; --card-glow: rgba(34,197,94,0.15);">
                <div class="service-orb" style="background: #22c55e; top: -20px; right: -20px;"></div>
                <div class="service-dots">
                    <span class="bg-green-400" style="top:30%;left:80%;animation-delay:0.2s;"></span>
                    <span class="bg-green-400" style="top:65%;left:15%;animation-delay:0.7s;"></span>
                    <span class="bg-green-400" style="top:45%;left:90%;animation-delay:1.2s;"></span>
                    <span class="bg-green-400" style="top:85%;left:60%;animation-delay:1.7s;"></span>
                </div>
                <span class="service-number text-green-400">03</span>
                <div class="relative z-10 mb-5 inline-block px-4 py-2.5 rounded-lg bg-[#0a0e14] border border-green-500/20 font-mono text-sm">
                    <span class="text-gray-500">$</span> <span class="text-green-400">php artisan</span> <span class="text-white">teach</span><span class="animate-pulse text-green-400">▊</span>
                </div>
                <h3 class="text-xl font-bold text-white mb-3 relative z-10">Content & Teaching</h3>
                <p class="text-gray-400 text-sm leading-relaxed mb-5 relative z-10">Blog posts, two podcasts, and a YouTube channel dedicated to helping developers level up their Laravel skills.</p>
                <div class="flex flex-wrap gap-2 relative z-10">
                    <span class="px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wider text-green-300 bg-green-500/10 rounded-full border border-green-500/20">Blog</span>
                    <span class="px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wider text-green-300 bg-green-500/10 rounded-full border border-green-500/20">Podcasts</span>
                    <span class="px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wider text-green-300 bg-green-500/10 rounded-full border border-green-500/20">YouTube</span>
                </div>
                <a href="{{ route('blog.index') }}" class="service-arrow text-green-400 relative z-10">
                    Start learning <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
            </div>
        </div>
    </div>
</section>

{{-- Section Divider --}}
<div class="section-divider section-divider-dark"></div>

{{-- ===== LATEST POSTS ===== --}}
@if($latestPosts->count())
<section class="py-20 dot-grid-bg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-12">
            <h2 class="text-4xl font-extrabold text-white">Latest Posts</h2>
            <a href="{{ route('blog.index') }}" class="text-sm text-brand-400 hover:text-brand-300 transition-colors">View all →</a>
        </div>
        <div class="bento-posts">
            @foreach($latestPosts as $index => $post)
            <article class="group fade-up gradient-border-card bg-brand-900/60 rounded-xl overflow-hidden border border-brand-800/50 hover:border-brand-600/40 transition-all duration-300 {{ $index === 0 ? 'flex flex-col' : '' }}" data-glow-card>
                <a href="{{ route('blog.show', $post) }}" class="block {{ $index === 0 ? 'flex flex-col h-full' : '' }}">
                    <div class="{{ $index === 0 ? 'flex-1 min-h-[200px]' : 'aspect-video' }} overflow-hidden">
                        @if($post->featured_image)
                        <img src="{{ Storage::url($post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        @else
                        @php $catSlug = $post->category?->slug ?? 'default'; $catInitial = strtoupper(substr($post->category?->name ?? 'B', 0, 1)); @endphp
                        <div class="w-full h-full post-art post-art-{{ $catSlug }} {{ $index === 0 ? 'min-h-[200px]' : '' }}">
                            {{-- Gradient orbs --}}
                            <div class="post-art-orb" style="width:200px;height:200px;background:var(--art-color);top:-50px;right:-40px;"></div>
                            <div class="post-art-orb" style="width:140px;height:140px;background:var(--art-color2);bottom:-30px;left:-30px;"></div>
                            <div class="post-art-orb" style="width:100px;height:100px;background:var(--art-color);top:50%;left:50%;opacity:0.15;"></div>
                            {{-- Geometric shapes --}}
                            <div class="post-art-shape rounded-lg" style="width:70px;height:70px;border-color:var(--art-color);top:15%;left:12%;transform:rotate(15deg);"></div>
                            <div class="post-art-shape rounded-full" style="width:50px;height:50px;border-color:var(--art-color2);top:55%;right:18%;"></div>
                            <div class="post-art-shape" style="width:90px;height:90px;border-color:var(--art-color);bottom:10%;left:35%;transform:rotate(45deg);"></div>
                            <div class="post-art-shape rounded-full" style="width:30px;height:30px;border-color:var(--art-color2);top:25%;right:35%;"></div>
                            <div class="post-art-shape rounded-lg" style="width:45px;height:45px;border-color:var(--art-color);bottom:30%;left:65%;transform:rotate(-20deg);"></div>
                            {{-- Accent lines --}}
                            <div class="post-art-line" style="background:var(--art-color);width:30%;top:30%;left:0;"></div>
                            <div class="post-art-line" style="background:var(--art-color2);width:20%;bottom:25%;right:0;left:auto;opacity:0.15;"></div>
                            {{-- Category monogram --}}
                            <span class="post-art-mono" style="color:var(--art-color);">{{ $catInitial }}</span>
                        </div>
                        @endif
                    </div>
                    <div class="p-5">
                        @if($post->category)
                        <span class="text-xs font-semibold text-brand-400 uppercase tracking-wide">{{ $post->category->name }}</span>
                        @endif
                        <h3 class="font-semibold {{ $index === 0 ? 'text-xl' : 'text-lg' }} text-white mt-1 mb-2 group-hover:text-brand-400 transition-colors">{{ $post->title }}</h3>
                        <p class="text-gray-400 text-sm line-clamp-2">{{ $post->excerpt }}</p>
                        <div class="mt-3 flex items-center gap-3 text-xs text-gray-500">
                            <time>{{ $post->published_at->format('M d, Y') }}</time>
                            <span>·</span>
                            <span>{{ $post->reading_time }} min read</span>
                        </div>
                    </div>
                </a>
            </article>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- Section Divider --}}
<div class="section-divider section-divider-dark"></div>

{{-- ===== PODCASTS ===== --}}
<section class="py-20 noise-overlay dot-grid-bg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-12">
            <h2 class="text-4xl font-extrabold text-white">Podcasts</h2>
            <a href="{{ route('podcast.index') }}" class="text-sm text-brand-400 hover:text-brand-300 transition-colors">View all →</a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            {{-- Coffee podcast --}}
            <a href="{{ route('podcast.index') }}" class="fade-up group relative rounded-xl p-8 overflow-hidden border border-brand-600/30 transition-all duration-300 hover:border-brand-600/50" style="background: linear-gradient(135deg, rgba(74,127,191,0.15), rgba(13,17,23,0.9));" data-glow-card>
                <div class="flex items-start justify-between mb-4">
                    <img src="/images/podcast-coffee-logo.png" alt="Coffee With The Laravel Architect" class="w-16 h-16 rounded-xl object-cover">
                    <div class="flex items-end gap-1 h-8">
                        <span class="eq-bar bg-brand-400"></span>
                        <span class="eq-bar bg-brand-400"></span>
                        <span class="eq-bar bg-brand-400"></span>
                        <span class="eq-bar bg-brand-400"></span>
                        <span class="eq-bar bg-brand-400"></span>
                    </div>
                </div>
                <h3 class="text-xl font-bold text-white mb-2 group-hover:text-brand-300 transition-colors">Coffee With The Laravel Architect</h3>
                <p class="text-gray-400 text-sm mb-3">Conversations about Laravel, web development, and the developer life — one cup at a time.</p>
                @if(isset($coffeeEpisodeCount) && $coffeeEpisodeCount > 0)
                    <span class="text-xs text-brand-300 mb-2 inline-block">{{ $coffeeEpisodeCount }} episodes</span>
                @endif
                <span class="text-sm text-brand-400 group-hover:text-brand-300 font-medium transition-colors block">Listen now →</span>
            </a>
            {{-- Cloudy Days podcast --}}
            <a href="{{ route('podcast.index') }}" class="fade-up group relative rounded-xl p-8 overflow-hidden border border-accent-600/30 transition-all duration-300 hover:border-accent-600/50" style="background: linear-gradient(135deg, rgba(196,112,136,0.12), rgba(13,17,23,0.9));" data-glow-card>
                <div class="flex items-start justify-between mb-4">
                    <img src="/images/podcast-cloudy-logo-white.jpg" alt="Embracing Cloudy Days" class="w-16 h-16 rounded-xl object-cover">
                    <div class="flex items-end gap-1 h-8">
                        <span class="eq-bar bg-accent-500"></span>
                        <span class="eq-bar bg-accent-500"></span>
                        <span class="eq-bar bg-accent-500"></span>
                        <span class="eq-bar bg-accent-500"></span>
                        <span class="eq-bar bg-accent-500"></span>
                    </div>
                </div>
                <h3 class="text-xl font-bold text-white mb-2 group-hover:text-accent-400 transition-colors">Embracing Cloudy Days</h3>
                <p class="text-gray-400 text-sm mb-3">Real talk about mental health, burnout, work-life balance, and finding your way through the fog.</p>
                @if(isset($cloudyEpisodeCount) && $cloudyEpisodeCount > 0)
                    <span class="text-xs text-accent-400 mb-2 inline-block">{{ $cloudyEpisodeCount }} episodes</span>
                @endif
                <span class="text-sm text-accent-500 group-hover:text-accent-400 font-medium transition-colors block">Listen now →</span>
            </a>
        </div>
    </div>
</section>

{{-- Section Divider --}}
<div class="section-divider section-divider-dark"></div>

{{-- ===== YOUTUBE ===== --}}
<style>
    @keyframes glitch {
        0%, 100% { transform: translate(0); filter: none; }
        20% { transform: translate(-2px, 1px); filter: hue-rotate(90deg); }
        40% { transform: translate(2px, -1px); filter: hue-rotate(-90deg); }
        60% { transform: translate(-1px, -1px); }
        80% { transform: translate(1px, 2px); }
    }
    .yt-heading:hover .glitch-text {
        animation: glitch 0.3s ease-in-out;
    }
    @keyframes flipIn {
        0% { transform: rotateX(90deg); opacity: 0; }
        100% { transform: rotateX(0); opacity: 1; }
    }
    .countdown-digit {
        perspective: 200px;
    }
    .countdown-digit span {
        display: inline-block;
        animation: flipIn 0.6s ease-out both;
    }
    .rec-dot {
        animation: pulse 1.5s ease-in-out infinite;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.3; }
    }
    .thumbnail-card {
        transition: all 0.3s ease;
    }
    .thumbnail-card:hover {
        transform: translateY(-4px) scale(1.02);
        border-color: rgba(239, 68, 68, 0.3);
    }
    .subscriber-bar-fill {
        animation: fillBar 2s ease-out 0.5s both;
    }
    @keyframes fillBar {
        from { width: 0%; }
    }
</style>

<section class="relative py-24 overflow-hidden">
    {{-- Red ambient glow --}}
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[600px] rounded-full opacity-[0.06] blur-[120px]" style="background: radial-gradient(circle, #ff0000, transparent 70%);"></div>

    {{-- Scanlines --}}
    <div class="absolute inset-0 opacity-[0.02] pointer-events-none" style="background: repeating-linear-gradient(0deg, transparent, transparent 2px, #ffffff 2px, #ffffff 3px);"></div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Section header with glitch --}}
        <div class="text-center mb-14 yt-heading">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-red-600/10 text-red-400 text-xs font-bold uppercase tracking-widest mb-6 border border-red-500/20">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-500 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                </span>
                Launching March 2
            </div>
            <h2 class="text-4xl md:text-5xl font-extrabold text-white mb-4 cursor-default">
                Watch on <span class="glitch-text inline-block text-red-500">YouTube</span>
            </h2>
            <p class="text-gray-400 max-w-xl mx-auto text-lg">
                Tutorials, live coding, and honest conversations about building with Laravel.
            </p>

            {{-- Countdown timer --}}
            <div class="flex items-center justify-center gap-4 mt-8" x-data="countdown()" x-init="start()">
                <div class="text-center">
                    <div class="countdown-digit w-16 h-16 rounded-xl bg-[#111111] border border-red-500/20 flex items-center justify-center text-2xl font-mono font-bold text-white" style="box-shadow: 0 0 20px rgba(239,68,68,0.05);">
                        <span x-text="days">00</span>
                    </div>
                    <p class="text-[10px] text-gray-600 uppercase tracking-widest mt-2">Days</p>
                </div>
                <span class="text-red-500/40 text-2xl font-bold mt-[-1rem]">:</span>
                <div class="text-center">
                    <div class="countdown-digit w-16 h-16 rounded-xl bg-[#111111] border border-red-500/20 flex items-center justify-center text-2xl font-mono font-bold text-white" style="box-shadow: 0 0 20px rgba(239,68,68,0.05);">
                        <span x-text="hours">00</span>
                    </div>
                    <p class="text-[10px] text-gray-600 uppercase tracking-widest mt-2">Hours</p>
                </div>
                <span class="text-red-500/40 text-2xl font-bold mt-[-1rem]">:</span>
                <div class="text-center">
                    <div class="countdown-digit w-16 h-16 rounded-xl bg-[#111111] border border-red-500/20 flex items-center justify-center text-2xl font-mono font-bold text-white" style="box-shadow: 0 0 20px rgba(239,68,68,0.05);">
                        <span x-text="minutes">00</span>
                    </div>
                    <p class="text-[10px] text-gray-600 uppercase tracking-widest mt-2">Min</p>
                </div>
                <span class="text-red-500/40 text-2xl font-bold mt-[-1rem]">:</span>
                <div class="text-center">
                    <div class="countdown-digit w-16 h-16 rounded-xl bg-[#111111] border border-red-500/20 flex items-center justify-center text-2xl font-mono font-bold text-red-400" style="box-shadow: 0 0 20px rgba(239,68,68,0.08);">
                        <span x-text="seconds">00</span>
                    </div>
                    <p class="text-[10px] text-gray-600 uppercase tracking-widest mt-2">Sec</p>
                </div>
            </div>
        </div>

        {{-- Main video preview --}}
        <div class="relative">
            <a href="https://youtube.com/@thelaravelarchitect" target="_blank" class="group block relative rounded-2xl overflow-hidden border border-[#1e2a3a] hover:border-red-500/30 transition-all duration-500">
                <div class="relative aspect-video bg-[#0a0a0a]">

                    {{-- Split screen layout --}}
                    <div class="absolute inset-0 flex">
                        {{-- Left: Presenter side --}}
                        <div class="w-[45%] relative bg-gradient-to-br from-[#0f1318] via-[#111820] to-[#0a0f14] overflow-hidden">
                            {{-- Ambient glow behind presenter --}}
                            <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-[300px] h-[300px] rounded-full opacity-20 blur-[80px]" style="background: radial-gradient(circle, #4A7FBF, transparent 70%);"></div>

                            {{-- Presenter silhouette --}}
                            <div class="absolute inset-0 flex items-end justify-center">
                                <div class="relative w-[75%] h-[85%]">
                                    {{-- Head --}}
                                    <div class="absolute top-[8%] left-1/2 -translate-x-1/2 w-20 h-20 rounded-full bg-gradient-to-b from-[#2a3444] to-[#1a2232] border border-[#2a3a4a]/30"></div>
                                    {{-- Shoulders/body --}}
                                    <div class="absolute top-[30%] inset-x-0 bottom-0 bg-gradient-to-b from-[#1e2a38] to-[#141c28] rounded-t-[60px] border-t border-x border-[#2a3a4a]/20">
                                        {{-- Shirt collar detail --}}
                                        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-8 h-6 bg-[#0f1620] rounded-b-full"></div>
                                    </div>
                                    {{-- Logo badge on chest --}}
                                    <div class="absolute top-[42%] left-1/2 -translate-x-1/2">
                                        <img src="/images/logo-color.svg" alt="" class="w-8 h-8 opacity-40">
                                    </div>
                                </div>
                            </div>

                            {{-- Webcam frame corners --}}
                            <div class="absolute top-3 left-3 w-4 h-4 border-t-2 border-l-2 border-red-500/30 rounded-tl"></div>
                            <div class="absolute top-3 right-3 w-4 h-4 border-t-2 border-r-2 border-red-500/30 rounded-tr"></div>
                            <div class="absolute bottom-3 left-3 w-4 h-4 border-b-2 border-l-2 border-red-500/30 rounded-bl"></div>
                            <div class="absolute bottom-3 right-3 w-4 h-4 border-b-2 border-r-2 border-red-500/30 rounded-br"></div>
                        </div>

                        {{-- Divider --}}
                        <div class="w-px bg-[#1e2a3a]"></div>

                        {{-- Right: Browser/editor side --}}
                        <div class="flex-1 relative bg-[#0D1117] overflow-hidden flex flex-col">
                            {{-- Browser chrome --}}
                            <div class="flex items-center gap-2 px-4 py-2.5 bg-[#161b22] border-b border-[#1e2a3a]">
                                <div class="flex gap-1.5">
                                    <div class="w-2.5 h-2.5 rounded-full bg-[#f85149]/60"></div>
                                    <div class="w-2.5 h-2.5 rounded-full bg-[#d29922]/60"></div>
                                    <div class="w-2.5 h-2.5 rounded-full bg-[#3fb950]/60"></div>
                                </div>
                                <div class="flex-1 mx-3">
                                    <div class="bg-[#0D1117] rounded-md px-3 py-1 flex items-center gap-2">
                                        <svg class="w-3 h-3 text-green-500/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        <span class="text-[10px] text-gray-500 font-mono">thelaravelarchitect.com</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Editor content --}}
                            <div class="flex-1 p-4 font-mono text-[11px] leading-relaxed overflow-hidden">
                                <div class="flex gap-4 text-gray-600">
                                    <div class="select-none text-right w-6 flex-shrink-0 space-y-0.5">
                                        @for($i = 1; $i <= 18; $i++)
                                            <div>{{ $i }}</div>
                                        @endfor
                                    </div>
                                    <div class="space-y-0.5 overflow-hidden">
                                        <div><span class="text-[#ff7b72]">class</span> <span class="text-[#d2a8ff]">ArchitectController</span></div>
                                        <div>{</div>
                                        <div>&nbsp;&nbsp;<span class="text-[#ff7b72]">public function</span> <span class="text-[#d2a8ff]">index</span>()</div>
                                        <div>&nbsp;&nbsp;{</div>
                                        <div>&nbsp;&nbsp;&nbsp;&nbsp;<span class="text-[#ff7b72]">return</span> <span class="text-[#79c0ff]">view</span>(<span class="text-[#a5d6ff]">'tutorials.index'</span>, [</div>
                                        <div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="text-[#a5d6ff]">'videos'</span> <span class="text-gray-500">=></span> <span class="text-[#79c0ff]">Video</span>::<span class="text-[#d2a8ff]">published</span>()</div>
                                        <div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-><span class="text-[#d2a8ff]">with</span>(<span class="text-[#a5d6ff]">'tags'</span>)</div>
                                        <div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-><span class="text-[#d2a8ff]">latest</span>()</div>
                                        <div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-><span class="text-[#d2a8ff]">paginate</span>(<span class="text-[#79c0ff]">12</span>),</div>
                                        <div>&nbsp;&nbsp;&nbsp;&nbsp;]);</div>
                                        <div>&nbsp;&nbsp;}</div>
                                        <div></div>
                                        <div>&nbsp;&nbsp;<span class="text-gray-600">// 🎬 New episodes every week</span></div>
                                        <div>&nbsp;&nbsp;<span class="text-[#ff7b72]">public function</span> <span class="text-[#d2a8ff]">show</span>(<span class="text-[#79c0ff]">Video</span> <span class="text-[#ffa657]">$video</span>)</div>
                                        <div>&nbsp;&nbsp;{</div>
                                        <div>&nbsp;&nbsp;&nbsp;&nbsp;<span class="text-[#ff7b72]">return</span> <span class="text-[#79c0ff]">view</span>(<span class="text-[#a5d6ff]">'tutorials.show'</span>)</div>
                                        <div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-><span class="text-[#d2a8ff]">with</span>(<span class="text-[#a5d6ff]">'video'</span>, <span class="text-[#ffa657]">$video</span>);</div>
                                        <div>&nbsp;&nbsp;}</div>
                                    </div>
                                </div>

                                {{-- Blinking cursor --}}
                                <div class="absolute bottom-16 left-[4.5rem]">
                                    <div class="w-[2px] h-4 bg-[#4A7FBF] animate-pulse"></div>
                                </div>
                            </div>

                            {{-- Terminal strip at bottom --}}
                            <div class="px-4 py-2 bg-[#161b22] border-t border-[#1e2a3a] font-mono text-[10px] text-gray-600 flex items-center gap-3">
                                <span class="text-green-500">●</span>
                                <span>PHP 8.4</span>
                                <span class="text-[#1e2a3a]">|</span>
                                <span>Laravel 12</span>
                                <span class="text-[#1e2a3a]">|</span>
                                <span class="text-green-400/60">✓ 42 tests passing</span>
                            </div>
                        </div>
                    </div>

                    {{-- REC indicator --}}
                    <div class="absolute top-4 left-4 flex items-center gap-2 z-10">
                        <span class="rec-dot w-2.5 h-2.5 rounded-full bg-red-600"></span>
                        <span class="text-red-500 text-[11px] font-mono font-bold tracking-wider">REC</span>
                    </div>

                    {{-- Coming Soon badge --}}
                    <div class="absolute top-4 right-4 z-10">
                        <span class="px-3 py-1 text-[10px] font-bold uppercase tracking-widest rounded-full bg-red-600/10 text-red-400 border border-red-500/20 backdrop-blur-sm">Coming Soon</span>
                    </div>

                    {{-- Center play button with rings --}}
                    <div class="absolute inset-0 flex items-center justify-center z-10">
                        <div class="absolute w-36 h-36 rounded-full border border-red-500/10 animate-ping" style="animation-duration: 2.5s;"></div>
                        <div class="absolute w-28 h-28 rounded-full border border-red-500/15 animate-ping" style="animation-duration: 3.5s;"></div>
                        <div class="absolute w-20 h-20 rounded-full border border-red-500/20 animate-ping" style="animation-duration: 2s;"></div>

                        <div class="relative w-20 h-20 rounded-full bg-red-600 flex items-center justify-center group-hover:scale-110 transition-transform duration-300" style="box-shadow: 0 0 60px rgba(239,68,68,0.4), 0 0 120px rgba(239,68,68,0.15);">
                            <svg class="w-8 h-8 text-white ml-1" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        </div>
                    </div>

                    {{-- Video title overlay --}}
                    <div class="absolute bottom-0 inset-x-0 p-5 bg-gradient-to-t from-black/90 via-black/60 to-transparent z-10">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <img src="/images/logo-color.svg" alt="" class="w-9 h-9 rounded-full ring-2 ring-red-500/30">
                                <div>
                                    <p class="text-white font-bold text-sm">Welcome to The Laravel Architect</p>
                                    <p class="text-gray-400 text-xs">The Laravel Architect · Channel Trailer</p>
                                </div>
                            </div>
                            <span class="px-4 py-1.5 bg-red-600 text-white text-xs font-bold rounded-full group-hover:bg-red-500 transition-colors">
                                Subscribe
                            </span>
                        </div>

                        {{-- Progress bar --}}
                        <div class="mt-3 flex items-center gap-3">
                            <span class="text-[10px] text-gray-500 font-mono">0:00</span>
                            <div class="flex-1 h-1 rounded-full bg-white/10 overflow-hidden">
                                <div class="h-full w-0 rounded-full bg-red-600"></div>
                            </div>
                            <span class="text-[10px] text-gray-500 font-mono">2:47</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- Subscriber goal --}}
        <div class="mt-8 max-w-md mx-auto">
            <div class="flex items-center justify-between text-xs mb-2">
                <span class="text-gray-500">Subscriber Goal</span>
                <span class="text-red-400 font-mono font-bold">{{ $youtubeSubscribers }} / 100</span>
            </div>
            <div class="h-2 rounded-full bg-[#111111] border border-[#1e2a3a] overflow-hidden">
                <div class="subscriber-bar-fill h-full rounded-full bg-gradient-to-r from-red-600 to-red-400" style="width: {{ min($youtubeSubscribers, 100) }}%;"></div>
            </div>
            <p class="text-[10px] text-gray-600 mt-2 text-center">Help us hit 100 subscribers before launch day 🚀</p>
        </div>

        {{-- Upcoming video thumbnails --}}
        <div class="mt-12">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-6 text-center">Coming to the Channel</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Thumbnail 1 --}}
                <div class="thumbnail-card rounded-xl border border-[#1e2a3a] bg-[#0D1117] overflow-hidden cursor-default">
                    <div class="aspect-video relative overflow-hidden">
                        <img src="/images/yt-thumb-testing.png" alt="Testing Like You Mean It" class="w-full h-full object-cover">
                        <div class="absolute bottom-2 right-2 px-1.5 py-0.5 bg-black/80 rounded text-[10px] font-mono text-gray-400">12:34</div>
                    </div>
                    <div class="p-4">
                        <p class="text-sm font-bold text-white mb-1 line-clamp-2">Testing Like You Mean It: 3 Suites, Zero Excuses</p>
                        <p class="text-[11px] text-gray-500">The Laravel Architect · Coming Mar 2</p>
                    </div>
                </div>

                {{-- Thumbnail 2 --}}
                <div class="thumbnail-card rounded-xl border border-[#1e2a3a] bg-[#0D1117] overflow-hidden cursor-default">
                    <div class="aspect-video relative overflow-hidden">
                        <img src="/images/yt-thumb-saas.png" alt="Build a SaaS from Scratch" class="w-full h-full object-cover">
                        <div class="absolute bottom-2 right-2 px-1.5 py-0.5 bg-black/80 rounded text-[10px] font-mono text-gray-400">18:47</div>
                    </div>
                    <div class="p-4">
                        <p class="text-sm font-bold text-white mb-1 line-clamp-2">Build a SaaS from Scratch with Laravel & Filament</p>
                        <p class="text-[11px] text-gray-500">The Laravel Architect · Coming Mar 9</p>
                    </div>
                </div>

                {{-- Thumbnail 3 --}}
                <div class="thumbnail-card rounded-xl border border-[#1e2a3a] bg-[#0D1117] overflow-hidden cursor-default">
                    <div class="aspect-video relative overflow-hidden">
                        <img src="/images/yt-thumb-codeigniter.png" alt="Why I Left CodeIgniter" class="w-full h-full object-cover">
                        <div class="absolute bottom-2 right-2 px-1.5 py-0.5 bg-black/80 rounded text-[10px] font-mono text-gray-400">24:12</div>
                    </div>
                    <div class="p-4">
                        <p class="text-sm font-bold text-white mb-1 line-clamp-2">Why I Left CodeIgniter (And Never Looked Back)</p>
                        <p class="text-[11px] text-gray-500">The Laravel Architect · Coming Mar 16</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function countdown() {
    return {
        days: '00', hours: '00', minutes: '00', seconds: '00',
        start() {
            const target = new Date('2026-03-02T12:00:00-05:00').getTime();
            const tick = () => {
                const now = Date.now();
                const diff = Math.max(0, target - now);
                this.days = String(Math.floor(diff / 86400000)).padStart(2, '0');
                this.hours = String(Math.floor((diff % 86400000) / 3600000)).padStart(2, '0');
                this.minutes = String(Math.floor((diff % 3600000) / 60000)).padStart(2, '0');
                this.seconds = String(Math.floor((diff % 60000) / 1000)).padStart(2, '0');
            };
            tick();
            setInterval(tick, 1000);
        }
    };
}
</script>

{{-- Section Divider --}}
<div class="section-divider section-divider-dark"></div>

{{-- ===== NEWSLETTER ===== --}}
<section class="py-20 fade-up dot-grid-bg">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="bg-brand-900/50 border border-brand-800/50 rounded-2xl p-10">
            <svg class="w-10 h-10 text-brand-400 mx-auto mb-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
            </svg>
            <h3 class="text-2xl font-bold text-white mb-3">Get Laravel tips in your inbox</h3>
            <p class="text-gray-400 mb-6">
                A weekly-ish newsletter with practical tips, tutorials, and thoughts on building better Laravel apps. No spam, unsubscribe anytime.
            </p>
            @if(session('newsletter_success'))
            <div class="mb-4 p-3 rounded-lg border border-green-500/30 bg-green-500/10 text-green-400 text-sm max-w-md mx-auto">
                {{ session('newsletter_success') }}
            </div>
            @endif
            @error('email')
            <div class="mb-4 p-3 rounded-lg border border-red-500/30 bg-red-500/10 text-red-400 text-sm max-w-md mx-auto">
                {{ $message }}
            </div>
            @enderror
            <form action="{{ route('newsletter.subscribe') }}" method="POST" class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
                @csrf
                <input type="email" name="email" placeholder="you@example.com" required
                    class="newsletter-input flex-1 px-4 py-3 bg-brand-800 border border-brand-700/50 rounded-lg text-white placeholder-gray-500 text-sm transition-all">
                <button type="submit" class="magnetic-btn glow-btn px-6 py-3 bg-brand-600 hover:bg-brand-500 text-white font-semibold rounded-lg text-sm transition-all">
                    Subscribe
                </button>
            </form>
        </div>
    </div>
</section>

{{-- ===== TESTIMONIALS ===== --}}
<section class="py-20 noise-overlay dot-grid-bg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14 fade-up">
            <h2 class="text-4xl font-extrabold text-white mb-4">Kind Words</h2>
            <p class="text-gray-400 text-lg">From colleagues and collaborators over the years.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="testimonial-card fade-up">
                <div class="flex-1">
                    <p class="text-gray-300 text-sm leading-relaxed">Jeffrey has an incredible eye for clean architecture. He took our messy legacy codebase and transformed it into something our team actually enjoys working with. The test coverage alone was worth it.</p>
                </div>
                <div class="flex items-center gap-3 mt-auto pt-5 border-t border-white/5">
                    <div class="w-10 h-10 rounded-full bg-brand-600/20 flex items-center justify-center text-brand-400 font-bold text-sm flex-shrink-0">MR</div>
                    <div>
                        <p class="text-white text-sm font-semibold">Mike Rodriguez</p>
                        <p class="text-gray-500 text-xs">CTO, SaaS Startup</p>
                    </div>
                </div>
            </div>
            <div class="testimonial-card fade-up">
                <div class="flex-1">
                    <p class="text-gray-300 text-sm leading-relaxed">Working with Jeffrey felt like having a senior architect on the team. He doesn't just write code — he thinks about the system as a whole. Our Laravel migration finished ahead of schedule.</p>
                </div>
                <div class="flex items-center gap-3 mt-auto pt-5 border-t border-white/5">
                    <div class="w-10 h-10 rounded-full bg-accent-600/20 flex items-center justify-center text-accent-400 font-bold text-sm flex-shrink-0">SL</div>
                    <div>
                        <p class="text-white text-sm font-semibold">Sarah Lin</p>
                        <p class="text-gray-500 text-xs">Engineering Manager</p>
                    </div>
                </div>
            </div>
            <div class="testimonial-card fade-up">
                <div class="flex-1">
                    <p class="text-gray-300 text-sm leading-relaxed">Jeffrey's blog posts and teaching style are what got me into Laravel in the first place. Clear, practical, no fluff. When I needed a consultant, he was the obvious choice.</p>
                </div>
                <div class="flex items-center gap-3 mt-auto pt-5 border-t border-white/5">
                    <div class="w-10 h-10 rounded-full bg-green-600/20 flex items-center justify-center text-green-400 font-bold text-sm flex-shrink-0">AK</div>
                    <div>
                        <p class="text-white text-sm font-semibold">Alex Kim</p>
                        <p class="text-gray-500 text-xs">Full-Stack Developer</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Section Divider --}}
<div class="section-divider section-divider-dark"></div>

{{-- ===== FINAL CTA ===== --}}
<section class="relative overflow-hidden border-t border-[#1e2a3a]">
    {{-- Animated gradient orbs --}}
    <div class="absolute top-1/2 left-1/4 -translate-y-1/2 w-[500px] h-[500px] rounded-full opacity-[0.07] blur-[100px] animate-pulse" style="background: #4A7FBF; animation-duration: 4s;"></div>
    <div class="absolute top-1/2 right-1/4 -translate-y-1/2 w-[400px] h-[400px] rounded-full opacity-[0.05] blur-[100px] animate-pulse" style="background: #9D5175; animation-duration: 6s;"></div>

    {{-- Grid pattern --}}
    <div class="absolute inset-0 opacity-[0.02]" style="background-image: linear-gradient(#ffffff 1px, transparent 1px), linear-gradient(90deg, #ffffff 1px, transparent 1px); background-size: 60px 60px;"></div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-28 md:py-36 text-center relative z-10">
        {{-- Available badge --}}
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-green-500/20 bg-green-500/5 text-green-400 text-xs font-semibold uppercase tracking-widest mb-8">
            <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-green-400"></span>
            </span>
            Available for Hire
        </div>

        {{-- Headline with gradient text --}}
        <h2 class="text-4xl sm:text-6xl font-extrabold mb-6 leading-tight">
            Let's Build Something
            <span class="bg-gradient-to-r from-[#4A7FBF] via-[#E47A9D] to-[#4A7FBF] bg-clip-text text-transparent bg-[length:200%_auto] animate-[shimmer_3s_linear_infinite]">Together</span>
        </h2>

        <p class="text-gray-400 mb-12 max-w-lg mx-auto text-lg leading-relaxed">
            Freelance Laravel development, legacy modernization, consulting, and collaborations. Let's talk.
        </p>

        {{-- Buttons --}}
        <div class="flex flex-wrap gap-4 justify-center">
            <a href="{{ route('contact') }}" class="magnetic-btn group inline-flex items-center gap-2 px-8 py-4 bg-[#4A7FBF] hover:bg-[#5A8FD0] text-white font-semibold rounded-xl transition-all text-lg hover:shadow-[0_0_30px_rgba(74,127,191,0.3)]">
                Get in Touch
                <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
            <a href="{{ route('projects.index') }}" class="inline-flex items-center gap-2 px-8 py-4 border border-[#1e2a3a] hover:border-[#4A7FBF]/50 text-gray-300 hover:text-white font-semibold rounded-xl transition-all text-lg">
                View My Work
            </a>
        </div>
    </div>
</section>

<script>
// Code editor tab switching
function switchTab(tab) {
    const panels = { routes: 'code-routes', architect: 'code-architect', test: 'code-test' };
    const tabs = { routes: 'tab-routes', architect: 'tab-architect', test: 'tab-test' };

    Object.keys(panels).forEach(key => {
        const panel = document.getElementById(panels[key]);
        const btn = document.getElementById(tabs[key]);
        if (key === tab) {
            panel.classList.remove('hidden');
            btn.className = btn.className.replace('code-editor-tab-inactive', 'code-editor-tab').replace('text-gray-500', 'text-gray-300');
        } else {
            panel.classList.add('hidden');
            btn.className = btn.className.replace(/\bcode-editor-tab\b(?!-)/, 'code-editor-tab-inactive').replace('text-gray-300', 'text-gray-500');
        }
    });
}

// Text Scramble/Decode Effect
(function() {
    const phrases = ['elegant APIs', 'scalable apps', 'clean code', 'Filament dashboards'];
    const chars = '!<>-_\\/[]{}—=+*^?#_abcdefghijklmnopqrstuvwxyz';
    const el = document.getElementById('scramble-text');
    let phraseIdx = 0;

    function scramble(text, onComplete) {
        let frame = 0;
        const length = text.length;
        const totalFrames = length * 3; // frames to fully resolve
        let resolved = 0;
        let output = '';

        function update() {
            output = '';
            resolved = Math.floor(frame / 3);
            for (let i = 0; i < length; i++) {
                if (i < resolved) {
                    output += '<span class="scramble-char-resolved">' + text[i] + '</span>';
                } else if (i < resolved + 3) {
                    output += '<span class="scramble-char-random">' + chars[Math.floor(Math.random() * chars.length)] + '</span>';
                } else {
                    output += '<span class="scramble-char-random">' + chars[Math.floor(Math.random() * chars.length)] + '</span>';
                }
            }
            el.innerHTML = output;
            frame++;

            if (frame <= totalFrames) {
                requestAnimationFrame(update);
            } else {
                el.innerHTML = '<span class="scramble-char-resolved">' + text + '</span>';
                if (onComplete) onComplete();
            }
        }
        requestAnimationFrame(update);
    }

    function cycle() {
        scramble(phrases[phraseIdx], function() {
            setTimeout(function() {
                phraseIdx = (phraseIdx + 1) % phrases.length;
                cycle();
            }, 2500);
        });
    }
    cycle();
})();

// IntersectionObserver for fade-up + count-up
(function() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                e.target.classList.add('visible');
                observer.unobserve(e.target);
            }
        });
    }, { threshold: 0.1 });
    document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));

    // Count-up
    const countObserver = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                const target = +e.target.dataset.target;
                let current = 0;
                const step = () => {
                    current += Math.ceil(target / 30);
                    if (current >= target) { e.target.textContent = target; return; }
                    e.target.textContent = current;
                    requestAnimationFrame(step);
                };
                step();
                countObserver.unobserve(e.target);
            }
        });
    }, { threshold: 0.5 });
    document.querySelectorAll('.count-up').forEach(el => countObserver.observe(el));
})();

// Cursor-following glow on cards
(function() {
    document.querySelectorAll('[data-glow-card]').forEach(function(card) {
        card.addEventListener('mousemove', function(e) {
            const rect = card.getBoundingClientRect();
            card.style.setProperty('--glow-x', (e.clientX - rect.left) + 'px');
            card.style.setProperty('--glow-y', (e.clientY - rect.top) + 'px');
        }, { passive: true });
    });
})();

// Magnetic effect on CTA buttons
(function() {
    document.querySelectorAll('.magnetic-btn').forEach(function(btn) {
        btn.addEventListener('mousemove', function(e) {
            const rect = btn.getBoundingClientRect();
            const x = e.clientX - rect.left - rect.width / 2;
            const y = e.clientY - rect.top - rect.height / 2;
            btn.style.transform = 'translate(' + (x * 0.15) + 'px, ' + (y * 0.15) + 'px)';
        }, { passive: true });

        btn.addEventListener('mouseleave', function() {
            btn.style.transform = '';
        }, { passive: true });
    });
})();

// Smooth parallax on hero code editor
(function() {
    const wrapper = document.getElementById('code-editor-wrapper');
    if (!wrapper) return;
    let ticking = false;

    window.addEventListener('scroll', function() {
        if (!ticking) {
            requestAnimationFrame(function() {
                const scrollY = window.scrollY;
                if (scrollY < window.innerHeight) {
                    wrapper.style.transform = 'translateY(' + (scrollY * 0.12) + 'px)';
                }
                ticking = false;
            });
            ticking = true;
        }
    }, { passive: true });
})();
</script>
@endsection
