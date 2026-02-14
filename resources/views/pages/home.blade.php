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

    /* ===== Blog Placeholder Gradients ===== */
    .post-placeholder-0 { background: linear-gradient(135deg, #1a2a4a, #2b3a5e, #1a2a4a); }
    .post-placeholder-1 { background: linear-gradient(135deg, #2a1a3a, #3b2a4e, #2a1a3a); }
    .post-placeholder-2 { background: linear-gradient(135deg, #1a3a3a, #2b4a4e, #1a3a3a); }

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
<section class="hero-mesh noise-overlay relative overflow-hidden min-h-[90vh] flex items-center">
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

{{-- Section Divider: Hero → Content --}}
<div class="section-divider section-divider-hero"></div>

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
                        {{-- Gradient placeholder with category icon --}}
                        <div class="w-full h-full post-placeholder-{{ $index % 3 }} flex items-center justify-center {{ $index === 0 ? 'min-h-[200px]' : '' }}">
                            <div class="text-center">
                                <svg class="w-10 h-10 text-white/20 mx-auto mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                                </svg>
                                @if($post->category)
                                <span class="text-white/20 text-xs font-medium uppercase tracking-wider">{{ $post->category->name }}</span>
                                @endif
                            </div>
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
<section class="py-20 dot-grid-bg">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row gap-12 items-center">

            {{-- Video preview card --}}
            <div class="flex-1 w-full">
                <a href="https://youtube.com/@thelaravelarchitect" target="_blank" class="group block relative rounded-2xl overflow-hidden border border-[#1e2a3a] aspect-video bg-gradient-to-br from-[#0D1117] via-[#111820] to-[#0D1117]">
                    {{-- Grid lines --}}
                    <div class="absolute inset-0 opacity-[0.03]" style="background-image: linear-gradient(#ffffff 1px, transparent 1px), linear-gradient(90deg, #ffffff 1px, transparent 1px); background-size: 40px 40px;"></div>

                    {{-- Center play button --}}
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="w-20 h-20 rounded-full bg-red-600 flex items-center justify-center shadow-lg group-hover:scale-110 group-hover:bg-red-500 transition-all duration-300" style="box-shadow: 0 0 40px rgba(239,68,68,0.3);">
                            <svg class="w-8 h-8 text-white ml-1" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        </div>
                    </div>

                    {{-- Coming Soon badge --}}
                    <div class="absolute top-4 right-4">
                        <span class="px-3 py-1 text-[10px] font-bold uppercase tracking-widest rounded-full bg-red-600/10 text-red-400 border border-red-500/20">Coming Soon</span>
                    </div>

                    {{-- Bottom bar --}}
                    <div class="absolute bottom-0 inset-x-0 p-5 bg-gradient-to-t from-[#0D1117] via-[#0D1117]/80 to-transparent">
                        <div class="flex items-center gap-3">
                            <img src="/images/logo-color.svg" alt="" class="w-10 h-10 rounded-full">
                            <div>
                                <p class="text-white font-semibold text-sm">The Laravel Architect</p>
                                <p class="text-gray-500 text-xs">youtube.com/@thelaravelarchitect</p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            {{-- Text content --}}
            <div class="flex-1 lg:max-w-sm">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-600/10 text-red-400 text-xs font-semibold uppercase tracking-widest mb-4 border border-red-500/20">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814z"/><path fill="#0D1117" d="M9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                    YouTube
                </div>
                <h2 class="text-3xl font-extrabold text-white mb-4">Tutorials & Live Coding</h2>
                <p class="text-gray-400 mb-6 leading-relaxed">
                    Laravel tutorials, package deep dives, live coding sessions, and the occasional rant about testing. The channel is launching soon.
                </p>

                <ul class="space-y-3 mb-8">
                    <li class="flex items-center gap-2 text-sm text-gray-400">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-500 flex-shrink-0"></span>
                        Step-by-step Laravel tutorials
                    </li>
                    <li class="flex items-center gap-2 text-sm text-gray-400">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-500 flex-shrink-0"></span>
                        Live coding & building in public
                    </li>
                    <li class="flex items-center gap-2 text-sm text-gray-400">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-500 flex-shrink-0"></span>
                        Package reviews & architecture talks
                    </li>
                </ul>

                <a href="https://youtube.com/@thelaravelarchitect" target="_blank" class="magnetic-btn inline-flex items-center gap-2 px-6 py-3 bg-red-600 hover:bg-red-500 text-white font-semibold rounded-lg transition-all hover:shadow-[0_0_30px_rgba(239,68,68,0.2)]">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                    Subscribe
                </a>
            </div>
        </div>
    </div>
</section>

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
