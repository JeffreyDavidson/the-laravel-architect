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

    /* ===== Laravel Gradient Glow Text ===== */
    .laravel-glow {
        font-family: 'Empera', serif;
        background: linear-gradient(135deg, #4A7FBF, #6fa3d6, #4A7FBF);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        filter: drop-shadow(0 0 20px rgba(74, 127, 191, 0.5)) drop-shadow(0 0 40px rgba(74, 127, 191, 0.25));
    }

    /* ===== Typing Effect ===== */
    .typing-wrapper {
        display: inline-flex;
        align-items: center;
        min-height: 1.5em;
    }
    .typing-text {
        border-right: 2px solid #4A7FBF;
        animation: blink 0.7s step-end infinite;
        white-space: nowrap;
        overflow: hidden;
    }
    @keyframes blink {
        50% { border-color: transparent; }
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

    /* ===== Glassmorphism Cards ===== */
    .glass-card {
        background: rgba(26, 29, 33, 0.6);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(74, 127, 191, 0.15);
        transition: all 0.4s ease;
        transform-style: preserve-3d;
        perspective: 800px;
    }
    .glass-card:hover {
        border-color: rgba(74, 127, 191, 0.5);
        box-shadow: 0 0 30px rgba(74, 127, 191, 0.15), inset 0 0 30px rgba(74, 127, 191, 0.03);
        transform: rotateY(-2deg) rotateX(2deg) scale(1.02);
    }

    /* ===== Gradient Top Border Cards ===== */
    .gradient-border-card {
        position: relative;
        overflow: hidden;
    }
    .gradient-border-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, #4A7FBF, #E47A9D);
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
</style>

{{-- ===== HERO ===== --}}
<section class="hero-mesh relative overflow-hidden min-h-[90vh] flex items-center">
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-28 z-10">
        <div class="flex flex-col lg:flex-row items-center lg:items-stretch gap-12 lg:gap-12">
            {{-- Left: Text Content --}}
            <div class="flex-1 text-center lg:text-left">
                <p class="text-brand-400 font-semibold mb-6 text-sm tracking-wide uppercase">Hey, I'm Jeffrey Davidson 👋</p>

                <h1 class="text-4xl sm:text-5xl lg:text-7xl font-bold tracking-tight leading-tight mb-6 text-white">
                    I architect things for the web with
                    <br>
                    <span class="laravel-glow text-5xl sm:text-6xl lg:text-8xl">Laravel</span>
                </h1>

                <div class="text-lg sm:text-xl text-gray-400 mb-4">
                    Crafting <span class="typing-wrapper"><span class="typing-text text-brand-300" id="typed-text"></span></span>
                </div>

                <p class="text-gray-500 mb-10 max-w-2xl">
                    Software developer, content creator, and architect of clean, maintainable applications.
                    I write about Laravel, PHP, web development, and the lessons learned along the way.
                </p>

                <div class="flex flex-wrap justify-center lg:justify-start gap-4 mb-10">
                    <a href="{{ route('blog.index') }}" class="glow-btn inline-flex items-center px-8 py-3.5 bg-brand-600 hover:bg-brand-500 text-white font-semibold rounded-lg">
                        Read the Blog
                    </a>
                    <a href="{{ route('projects.index') }}" class="glow-btn glow-btn-outline inline-flex items-center px-8 py-3.5 border border-brand-700 text-gray-300 font-semibold rounded-lg transition-all">
                        View Projects
                    </a>
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
            <div class="hidden lg:flex lg:flex-col flex-1 min-w-0">
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
                        <div class="flex gap-4"><span class="code-line-number">11</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-class">Route</span>::<span class="syn-method">get</span>(<span class="syn-string">'/projects'</span>, <span class="syn-bracket">[</span></span></div>
                        <div class="flex gap-4"><span class="code-line-number">12</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-class">ArchitectController</span>::<span class="syn-keyword">class</span>,</span></div>
                        <div class="flex gap-4"><span class="code-line-number">13</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-string">'build'</span></span></div>
                        <div class="flex gap-4"><span class="code-line-number">14</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-bracket">]</span>);</span></div>
                        <div class="flex gap-4"><span class="code-line-number">15</span><span>&nbsp;</span></div>
                        <div class="flex gap-4"><span class="code-line-number">16</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-class">Route</span>::<span class="syn-method">get</span>(<span class="syn-string">'/hire-me'</span>, <span class="syn-bracket">[</span></span></div>
                        <div class="flex gap-4"><span class="code-line-number">17</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-class">ArchitectController</span>::<span class="syn-keyword">class</span>,</span></div>
                        <div class="flex gap-4"><span class="code-line-number">18</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-string">'collaborate'</span></span></div>
                        <div class="flex gap-4"><span class="code-line-number">19</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-bracket">]</span>);</span></div>
                        <div class="flex gap-4"><span class="code-line-number">20</span><span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-bracket">}</span>);</span></div>
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

{{-- ===== STATS BAR ===== --}}
<section class="border-y border-brand-800/50 bg-brand-900/80 backdrop-blur">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
            <div>
                <div class="flex items-center justify-center gap-1">
                    <span class="count-up text-white text-2xl font-bold" data-target="15">0</span>
                    <span class="text-brand-400 text-2xl font-bold">+</span>
                </div>
                <p class="text-gray-500 text-sm mt-1">Years Experience</p>
            </div>
            <div>
                <div class="flex items-center justify-center gap-1">
                    <span class="count-up text-white text-2xl font-bold" data-target="2">0</span>
                </div>
                <p class="text-gray-500 text-sm mt-1">Podcasts</p>
            </div>
            <div>
                <div class="flex items-center justify-center gap-1">
                    <span class="text-white text-2xl font-bold">Laravel</span>
                </div>
                <p class="text-gray-500 text-sm mt-1">& PHP Specialist</p>
            </div>
            <div>
                <div class="flex items-center justify-center gap-1">
                    <span class="text-brand-400 text-2xl font-bold">✓</span>
                </div>
                <p class="text-gray-500 text-sm mt-1">Available for Hire</p>
            </div>
        </div>
    </div>
</section>

{{-- ===== ABOUT BLURB ===== --}}
<section class="py-16 fade-up">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row items-center gap-10">
            <div class="flex-shrink-0">
                <img src="/images/logo-alternate.jpg" alt="The Laravel Architect" class="w-40 h-40 rounded-2xl object-cover border border-brand-700/30 shadow-lg shadow-brand-600/10">
            </div>
            <div class="text-center md:text-left">
                <p class="text-lg text-gray-300 leading-relaxed">
                    I'm a husband, father, and software developer who moved from Kansas to Florida to chase better weather
                    and build better software. By day, I architect Laravel applications. By night, I create content to help
                    other developers level up — through <span class="text-brand-400">blog posts</span>,
                    <span class="text-brand-400">YouTube tutorials</span>, and
                    <span class="text-brand-400">two podcasts</span>. I believe clean code and genuine community
                    make the tech world a better place.
                </p>
                <a href="{{ route('about') }}" class="inline-flex items-center mt-6 text-sm text-brand-400 hover:text-brand-300 font-medium transition-colors">
                    More about me →
                </a>
            </div>
        </div>
    </div>
</section>

{{-- ===== TECH STACK ===== --}}
<section class="py-12 border-y border-brand-800/30">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <p class="text-center text-sm text-gray-500 uppercase tracking-widest mb-8">Tech I work with</p>
        <div class="flex flex-wrap justify-center gap-8 items-center">
            {{-- Laravel --}}
            <div class="tech-icon flex flex-col items-center gap-2 group">
                <div class="w-12 h-12 rounded-lg bg-brand-900/50 border border-brand-800/50 flex items-center justify-center">
                    <svg class="w-7 h-7 text-red-500" viewBox="0 0 24 24" fill="currentColor"><path d="M23.642 5.43a.364.364 0 0 1 .014.1v5.149c0 .135-.073.26-.189.326l-4.323 2.49v4.934c0 .135-.073.26-.189.326l-9.037 5.206a.35.35 0 0 1-.128.049c-.01.004-.02.005-.03.01a.35.35 0 0 1-.2 0c-.013-.005-.025-.004-.038-.01a.376.376 0 0 1-.126-.049L.378 18.755a.378.378 0 0 1-.189-.326V3.334c0-.034.005-.07.014-.1.003-.012.01-.02.014-.032a.369.369 0 0 1 .023-.058c.004-.013.015-.022.023-.033.012-.015.021-.032.036-.045.01-.01.025-.018.037-.027.014-.012.027-.024.041-.034h.001L4.896.384a.378.378 0 0 1 .378 0L9.79 3.01h.002c.015.01.027.021.04.033.013.01.027.018.038.028.014.013.023.03.035.045.009.011.02.021.024.033.01.019.015.038.024.058.005.012.011.02.013.033a.363.363 0 0 1 .015.1v9.652l3.76-2.164V5.527c0-.034.005-.07.013-.1.004-.013.01-.021.015-.033a.376.376 0 0 1 .023-.058c.01-.013.016-.022.024-.033.011-.015.02-.032.035-.045.012-.01.025-.018.038-.027.013-.012.027-.024.04-.034h.002l4.518-2.624a.378.378 0 0 1 .377 0l4.518 2.624c.015.01.027.021.042.033.012.01.025.018.036.028.016.013.025.03.037.045.008.011.019.021.023.033.01.019.017.038.024.058.005.012.011.02.014.033zm-.74 5.032V5.86l-1.58.908-2.18 1.257v4.6zm-4.518 7.76v-4.604l-2.146 1.225-6.608 3.772v4.652zM1.133 3.665v14.757l8.282 4.773v-4.65l-4.32-2.44-.003-.003-.004-.001c-.013-.01-.025-.022-.039-.032-.012-.01-.026-.018-.036-.029l-.002-.003c-.011-.012-.02-.028-.03-.042-.01-.012-.021-.023-.028-.037l-.002-.003c-.008-.014-.012-.031-.018-.047-.006-.013-.014-.025-.018-.039-.004-.018-.004-.037-.006-.056-.002-.014-.006-.027-.006-.041V5.862l-2.18-1.257zM5.085.861L1.322 3.033l3.762 2.171 3.762-2.171zm1.892 13.292l2.18-1.256V3.665l-1.58.909-2.18 1.256v9.232zm8.094-9.181l-3.762 2.172 3.762 2.172 3.762-2.172zm-.378 4.673l-2.18-1.256-1.58-.909v4.6l2.18 1.257 1.58.908zm-8.47 9.172l5.53-3.159 2.925-1.67-3.756-2.167-4.324 2.49-3.63 2.091z"/></svg>
                </div>
                <span class="text-xs text-gray-500 group-hover:text-gray-300 transition-colors">Laravel</span>
            </div>
            {{-- PHP --}}
            <div class="tech-icon flex flex-col items-center gap-2 group">
                <div class="w-12 h-12 rounded-lg bg-brand-900/50 border border-brand-800/50 flex items-center justify-center">
                    <svg class="w-7 h-7 text-indigo-400" viewBox="0 0 24 24" fill="currentColor"><path d="M7.01 10.207h-.944l-.515 2.648h.838c.556 0 .97-.105 1.242-.314.272-.21.455-.559.55-1.049.092-.47.05-.802-.124-.995-.175-.193-.523-.29-1.047-.29zM12 5.688C5.373 5.688 0 8.514 0 12s5.373 6.313 12 6.313S24 15.486 24 12c0-3.486-5.373-6.312-12-6.312zm-3.26 7.451c-.261.25-.575.438-.917.551-.336.108-.765.164-1.285.164H5.357l-.327 1.681H3.652l1.23-6.326h2.65c.797 0 1.378.209 1.744.628.366.418.476 1.002.33 1.752a2.836 2.836 0 0 1-.866 1.55zm5.943-.209c.043-.186.06-.345.05-.475-.01-.131-.053-.233-.129-.306-.076-.074-.19-.12-.342-.14a3.3 3.3 0 0 0-.594-.025l-.737 0-.283 1.454h-.658l.857-4.404h.658l-.283 1.455.72-.001c.39-.003.67.037.838.12.168.082.271.244.308.484.021.13.012.323-.027.578l-.193.992h-.668l.203-1.046zm3.838-.562c-.086.42-.243.79-.47 1.108-.228.319-.511.564-.847.735-.337.17-.738.256-1.203.256h-.964l-.327 1.681h-1.378l1.23-6.326h2.648c.798 0 1.38.209 1.745.628.367.418.477 1.002.33 1.752a2.84 2.84 0 0 1-.764 1.166zm-.437-.985c.092-.47.05-.802-.124-.995-.174-.193-.523-.29-1.047-.29h-.944l-.515 2.648h.838c.557 0 .97-.105 1.242-.314.272-.21.456-.559.55-1.049z"/></svg>
                </div>
                <span class="text-xs text-gray-500 group-hover:text-gray-300 transition-colors">PHP</span>
            </div>
            {{-- Filament --}}
            <div class="tech-icon flex flex-col items-center gap-2 group">
                <div class="w-12 h-12 rounded-lg bg-brand-900/50 border border-brand-800/50 flex items-center justify-center">
                    <span class="text-amber-400 text-xl font-bold">F</span>
                </div>
                <span class="text-xs text-gray-500 group-hover:text-gray-300 transition-colors">Filament</span>
            </div>
            {{-- Livewire --}}
            <div class="tech-icon flex flex-col items-center gap-2 group">
                <div class="w-12 h-12 rounded-lg bg-brand-900/50 border border-brand-800/50 flex items-center justify-center">
                    <svg class="w-7 h-7 text-pink-400" viewBox="0 0 24 24" fill="currentColor"><path d="M12.001 0C6.174 0 1.448 4.957 1.448 11.07c0 3.806 1.83 7.184 4.665 9.227.233.168.482-.111.379-.367a16.06 16.06 0 0 1-.592-1.874c-.063-.252.085-.442.276-.583 2.794-2.07 4.6-5.395 4.6-9.142 0-.357-.017-.71-.05-1.06-.026-.276.226-.5.493-.41 3.147 1.074 5.554 3.752 6.278 7.066a.274.274 0 0 0 .491.1c.654-.883 1.088-1.894 1.301-2.983.052-.266.367-.375.565-.182a9.384 9.384 0 0 1 2.7 6.617c0 .575-.053 1.14-.155 1.69-.048.262.218.459.438.31A11.123 11.123 0 0 0 22.554 11.07C22.554 4.957 17.828 0 12.001 0z"/></svg>
                </div>
                <span class="text-xs text-gray-500 group-hover:text-gray-300 transition-colors">Livewire</span>
            </div>
            {{-- Tailwind --}}
            <div class="tech-icon flex flex-col items-center gap-2 group">
                <div class="w-12 h-12 rounded-lg bg-brand-900/50 border border-brand-800/50 flex items-center justify-center">
                    <svg class="w-7 h-7 text-cyan-400" viewBox="0 0 24 24" fill="currentColor"><path d="M12.001 4.8c-3.2 0-5.2 1.6-6 4.8 1.2-1.6 2.6-2.2 4.2-1.8.913.228 1.565.89 2.288 1.624C13.666 10.618 15.027 12 18.001 12c3.2 0 5.2-1.6 6-4.8-1.2 1.6-2.6 2.2-4.2 1.8-.913-.228-1.565-.89-2.288-1.624C16.337 6.182 14.976 4.8 12.001 4.8zm-6 7.2c-3.2 0-5.2 1.6-6 4.8 1.2-1.6 2.6-2.2 4.2-1.8.913.228 1.565.89 2.288 1.624 1.177 1.194 2.538 2.576 5.512 2.576 3.2 0 5.2-1.6 6-4.8-1.2 1.6-2.6 2.2-4.2 1.8-.913-.228-1.565-.89-2.288-1.624C10.337 13.382 8.976 12 6.001 12z"/></svg>
                </div>
                <span class="text-xs text-gray-500 group-hover:text-gray-300 transition-colors">Tailwind</span>
            </div>
            {{-- MySQL --}}
            <div class="tech-icon flex flex-col items-center gap-2 group">
                <div class="w-12 h-12 rounded-lg bg-brand-900/50 border border-brand-800/50 flex items-center justify-center">
                    <svg class="w-7 h-7 text-blue-400" viewBox="0 0 24 24" fill="currentColor"><path d="M16.405 5.501c-.115 0-.193.014-.274.033v.013h.014c.054.104.146.18.214.273.054.107.1.214.154.32l.014-.015c.094-.066.14-.172.14-.333-.04-.047-.046-.094-.08-.14-.04-.067-.126-.1-.18-.153zM5.77 18.695h-.927a50.854 50.854 0 0 0-.27-4.41h-.008l-1.41 4.41H2.45l-1.4-4.41h-.01a72.892 72.892 0 0 0-.195 4.41H0c.055-1.966.192-3.81.41-5.53h1.15l1.335 4.064h.008l1.347-4.064h1.095c.242 2.015.384 3.86.428 5.53zm4.326-3.78c-.2.59-.37 1.06-.507 1.41-.14.35-.26.607-.363.77-.1.164-.213.278-.328.34-.116.06-.26.092-.437.092-.128 0-.233-.018-.313-.056l-.065-.372c.087.028.16.042.22.042.17 0 .3-.09.4-.27.094-.18.097-.39.013-.62l-1.2-3.466h.87l.727 2.414c.033.1.04.19.02.27l.006.007c.09-.27.196-.564.317-.88l.677-1.81h.82zm5.2 3.64c-.39.12-.63.18-.72.18-.36 0-.65-.12-.84-.36-.21-.24-.35-.64-.42-1.2-.08-.56-.08-1.35 0-2.35.07-.96.24-1.65.5-2.07.26-.43.67-.64 1.22-.64.15 0 .35.04.59.12.24.07.42.17.55.29l-.36.66c-.15-.16-.37-.24-.65-.24-.28 0-.5.13-.66.38-.15.26-.26.67-.33 1.23-.06.54-.07 1.17-.03 1.9.06.7.18 1.17.37 1.42.18.24.44.36.78.36.22 0 .44-.05.68-.16l.11.54z"/></svg>
                </div>
                <span class="text-xs text-gray-500 group-hover:text-gray-300 transition-colors">MySQL</span>
            </div>
            {{-- Alpine.js --}}
            <div class="tech-icon flex flex-col items-center gap-2 group">
                <div class="w-12 h-12 rounded-lg bg-brand-900/50 border border-brand-800/50 flex items-center justify-center">
                    <span class="text-teal-400 text-lg font-bold">A</span>
                </div>
                <span class="text-xs text-gray-500 group-hover:text-gray-300 transition-colors">Alpine.js</span>
            </div>
            {{-- Redis --}}
            <div class="tech-icon flex flex-col items-center gap-2 group">
                <div class="w-12 h-12 rounded-lg bg-brand-900/50 border border-brand-800/50 flex items-center justify-center">
                    <svg class="w-7 h-7 text-red-400" viewBox="0 0 24 24" fill="currentColor"><path d="M10.5 2.661l.54.997-1.797.644 2.409.218.748 1.246.467-1.48 2.077-.322-1.584-.72.514-1.467-1.596.885zM3.055 8.6l5.036 2.093 7.074-3.06L9.89 5.57zm17.834 1.735c.017.18-.07.396-.283.551l-7.697 4.39v7.702c0 .253-.166.384-.378.312l-9.237-3.234c-.2-.07-.345-.282-.345-.527V12.15c0-.166.117-.327.291-.396l8.04-3.109 8.913 3.592c.2.072.384.22.396.398z"/></svg>
                </div>
                <span class="text-xs text-gray-500 group-hover:text-gray-300 transition-colors">Redis</span>
            </div>
        </div>
    </div>
</section>

{{-- ===== FEATURED PROJECTS ===== --}}
@if($featuredProjects->count())
<section class="py-20">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-12">
            <h2 class="text-3xl font-bold text-white">Featured Projects</h2>
            <a href="{{ route('projects.index') }}" class="text-sm text-brand-400 hover:text-brand-300 transition-colors">View all →</a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            @foreach($featuredProjects as $project)
            <a href="{{ route('projects.show', $project) }}" class="glass-card group block rounded-xl overflow-hidden fade-up">
                @if($project->featured_image)
                <div class="aspect-video bg-brand-800 overflow-hidden">
                    <img src="{{ Storage::url($project->featured_image) }}" alt="{{ $project->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                </div>
                @endif
                <div class="p-6">
                    <h3 class="font-semibold text-lg text-white mb-2 group-hover:text-brand-400 transition-colors">{{ $project->title }}</h3>
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

{{-- ===== LATEST POSTS ===== --}}
@if($latestPosts->count())
<section class="py-20">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-12">
            <h2 class="text-3xl font-bold text-white">Latest Posts</h2>
            <a href="{{ route('blog.index') }}" class="text-sm text-brand-400 hover:text-brand-300 transition-colors">View all →</a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($latestPosts as $index => $post)
            <article class="group fade-up gradient-border-card bg-brand-900/60 rounded-xl overflow-hidden border border-brand-800/50 hover:border-brand-600/40 transition-all duration-300">
                <a href="{{ route('blog.show', $post) }}" class="block">
                    <div class="aspect-video overflow-hidden">
                        @if($post->featured_image)
                        <img src="{{ Storage::url($post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        @else
                        {{-- Gradient placeholder with category icon --}}
                        <div class="w-full h-full post-placeholder-{{ $index % 3 }} flex items-center justify-center">
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
                        <h3 class="font-semibold text-lg text-white mt-1 mb-2 group-hover:text-brand-400 transition-colors">{{ $post->title }}</h3>
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

{{-- ===== PODCASTS ===== --}}
<section class="py-20">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-12">
            <h2 class="text-3xl font-bold text-white">Podcasts</h2>
            <a href="{{ route('podcast.index') }}" class="text-sm text-brand-400 hover:text-brand-300 transition-colors">View all →</a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            {{-- Coffee podcast --}}
            <a href="{{ route('podcast.index') }}" class="fade-up group relative rounded-xl p-8 overflow-hidden border border-brand-600/30 transition-all duration-300 hover:border-brand-600/50" style="background: linear-gradient(135deg, rgba(74,127,191,0.15), rgba(13,17,23,0.9));">
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
                <p class="text-gray-400 text-sm mb-5">Conversations about Laravel, web development, and the developer life — one cup at a time.</p>
                <span class="text-sm text-brand-400 group-hover:text-brand-300 font-medium transition-colors">Listen now →</span>
            </a>
            {{-- Cloudy Days podcast --}}
            <a href="{{ route('podcast.index') }}" class="fade-up group relative rounded-xl p-8 overflow-hidden border border-accent-600/30 transition-all duration-300 hover:border-accent-600/50" style="background: linear-gradient(135deg, rgba(196,112,136,0.12), rgba(13,17,23,0.9));">
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
                <p class="text-gray-400 text-sm mb-5">Real talk about mental health, burnout, work-life balance, and finding your way through the fog.</p>
                <span class="text-sm text-accent-500 group-hover:text-accent-400 font-medium transition-colors">Listen now →</span>
            </a>
        </div>
    </div>
</section>

{{-- ===== YOUTUBE ===== --}}
<section class="py-20">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold text-white mb-4">Watch on YouTube</h2>
        <p class="text-gray-400 mb-10 max-w-xl mx-auto">
            Tutorials, live coding, and conversations about Laravel and web development.
        </p>
        <div class="browser-frame mx-auto">
            {{-- Browser chrome --}}
            <div class="bg-brand-900 px-4 py-3 flex items-center gap-3 border-b border-white/10">
                <div class="browser-dots flex gap-2">
                    <span class="bg-red-500/80"></span>
                    <span class="bg-yellow-500/80"></span>
                    <span class="bg-green-500/80"></span>
                </div>
                <div class="flex-1 bg-brand-800 rounded-md px-3 py-1 text-xs text-gray-500 text-left truncate">
                    youtube.com/@thelaravelarchitect
                </div>
            </div>
            <div class="aspect-video bg-brand-800">
                <img src="/images/social-banner.jpg" alt="The Laravel Architect YouTube" class="w-full h-full object-cover">
            </div>
        </div>
        <div class="mt-8">
            <a href="https://youtube.com/channel/UC42H30o7l5QvvCzC86dSu_A" target="_blank" class="glow-btn inline-flex items-center px-8 py-3.5 bg-red-600 hover:bg-red-500 text-white font-semibold rounded-lg transition-all" style="--tw-shadow-color: rgba(239,68,68,0.3);">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                Subscribe
            </a>
        </div>
    </div>
</section>

{{-- ===== NEWSLETTER ===== --}}
<section class="py-20 fade-up">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="bg-brand-900/50 border border-brand-800/50 rounded-2xl p-10">
            <svg class="w-10 h-10 text-brand-400 mx-auto mb-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
            </svg>
            <h3 class="text-2xl font-bold text-white mb-3">Get Laravel tips in your inbox</h3>
            <p class="text-gray-400 mb-6">
                A weekly-ish newsletter with practical tips, tutorials, and thoughts on building better Laravel apps. No spam, unsubscribe anytime.
            </p>
            <form action="#" method="POST" class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
                @csrf
                <input type="email" name="email" placeholder="you@example.com" required
                    class="newsletter-input flex-1 px-4 py-3 bg-brand-800 border border-brand-700/50 rounded-lg text-white placeholder-gray-500 text-sm transition-all">
                <button type="submit" class="glow-btn px-6 py-3 bg-brand-600 hover:bg-brand-500 text-white font-semibold rounded-lg text-sm transition-all">
                    Subscribe
                </button>
            </form>
        </div>
    </div>
</section>

{{-- ===== FINAL CTA ===== --}}
<section class="relative overflow-hidden" style="background: linear-gradient(135deg, #4A7FBF, #E47A9D);">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center relative z-10">
        <h2 class="text-3xl sm:text-4xl font-bold text-white mb-4">Need a Laravel Developer?</h2>
        <p class="text-white/80 mb-8 max-w-xl mx-auto text-lg">
            I'm available for freelance projects, consulting, and collaborations. Let's build something great together.
        </p>
        <a href="{{ route('contact') }}" class="inline-flex items-center px-10 py-4 bg-white text-brand-600 font-bold rounded-lg hover:bg-gray-100 transition-colors text-lg">
            Get in Touch
        </a>
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

// Typing effect
(function() {
    const phrases = ['elegant APIs', 'scalable apps', 'clean code', 'Filament dashboards'];
    const el = document.getElementById('typed-text');
    let phraseIdx = 0, charIdx = 0, deleting = false;

    function tick() {
        const current = phrases[phraseIdx];
        if (!deleting) {
            el.textContent = current.substring(0, ++charIdx);
            if (charIdx === current.length) { setTimeout(() => { deleting = true; tick(); }, 2000); return; }
        } else {
            el.textContent = current.substring(0, --charIdx);
            if (charIdx === 0) { deleting = false; phraseIdx = (phraseIdx + 1) % phrases.length; }
        }
        setTimeout(tick, deleting ? 40 : 80);
    }
    tick();
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
</script>
@endsection
