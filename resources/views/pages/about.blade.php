@extends('layouts.app')

@section('title', 'About')

@section('content')
<style>
    .timeline-item {
        position: relative;
        padding-left: 2rem;
        transition: all 0.3s ease;
    }
    .timeline-item::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0.5rem;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #4A7FBF;
        box-shadow: 0 0 10px rgba(74, 127, 191, 0.3);
        transition: all 0.3s ease;
    }
    .timeline-item:hover::before {
        box-shadow: 0 0 20px rgba(74, 127, 191, 0.5);
        transform: scale(1.3);
    }
    .timeline-item::after {
        content: '';
        position: absolute;
        left: 4px;
        top: 1.5rem;
        width: 2px;
        height: calc(100% - 0.5rem);
        background: linear-gradient(to bottom, #1e2a3a, transparent);
    }
    .timeline-item:last-child::after {
        display: none;
    }
    .value-card {
        position: relative;
        transition: all 0.3s ease;
        overflow: hidden;
    }
    .value-card::before {
        content: '';
        position: absolute;
        inset: -1px;
        border-radius: 1rem;
        background: conic-gradient(from var(--card-angle, 0deg), transparent 60%, #4A7FBF 80%, transparent 100%);
        opacity: 0;
        transition: opacity 0.4s ease;
        z-index: -1;
        animation: cardSpin 4s linear infinite;
    }
    .value-card:hover::before {
        opacity: 1;
    }
    .value-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.4);
        border-color: transparent;
    }
    .value-card-pink::before {
        background: conic-gradient(from var(--card-angle, 0deg), transparent 60%, #9D5175 80%, transparent 100%);
    }
    @keyframes cardSpin {
        to { --card-angle: 360deg; }
    }
    @property --card-angle {
        syntax: '<angle>';
        initial-value: 0deg;
        inherits: false;
    }

    /* Trading Card Flip */
    .card-flip-container {
        perspective: 1200px;
        cursor: pointer;
    }
    .card-flip {
        position: relative;
        transform-style: preserve-3d;
        transition: transform 0.8s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .card-flip.flipped {
        transform: rotateY(180deg);
    }
    .card-front, .card-back {
        backface-visibility: hidden;
        -webkit-backface-visibility: hidden;
        width: 100%;
    }
    .card-back {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        transform: rotateY(180deg);
    }
    .card-back .trading-card-inner {
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .card-back .stats-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    /* Subtle hover lift */
    .card-flip-container:hover .card-flip:not(.flipped) {
        transform: rotateY(-5deg) scale(1.02);
    }
    .card-flip-container:hover .card-flip.flipped {
        transform: rotateY(175deg) scale(1.02);
    }

    .trading-card-inner {
        transition: box-shadow 0.4s ease;
        transform-style: preserve-3d;
    }

    /* Flip hint */
    .flip-hint {
        transition: opacity 0.4s ease;
    }
    .flip-hint.hidden { opacity: 0; pointer-events: none; }

    /* Holographic shimmer */
    .holo-border {
        position: relative;
    }
    .holo-border::before {
        content: '';
        position: absolute;
        inset: -2px;
        border-radius: 1.25rem;
        background: conic-gradient(
            from var(--holo-angle, 0deg),
            #4A7FBF 0%,
            #9D5175 25%,
            #4A7FBF 50%,
            #E47A9D 75%,
            #4A7FBF 100%
        );
        opacity: 0.4;
        z-index: -1;
        transition: opacity 0.4s ease;
        animation: holoSpin 6s linear infinite;
    }
    .trading-card-tilt:hover .holo-border::before {
        opacity: 0.7;
    }
    @keyframes holoSpin {
        to { --holo-angle: 360deg; }
    }
    @property --holo-angle {
        syntax: '<angle>';
        initial-value: 0deg;
        inherits: false;
    }

    /* Stat bar fill animation */
    .stat-bar {
        transition: width 1s ease-out;
    }

    /* Pulsing glow behind trading card */
    @keyframes glowPulse {
        0%, 100% { opacity: 0.08; transform: scale(1); }
        50% { opacity: 0.15; transform: scale(1.05); }
    }
    .trading-card-glow {
        animation: glowPulse 4s ease-in-out infinite;
    }

    /* Shimmer for CTA */
    @keyframes shimmer {
        to { background-position: 200% center; }
    }
    
    /* Light mode overrides */
    :root:not(.dark) .timeline-item::after {
        background: linear-gradient(to bottom, rgba(0,0,0,0.1), transparent);
    }
    :root:not(.dark) .timeline-item::before {
        box-shadow: 0 0 10px rgba(74, 127, 191, 0.2);
    }
    :root:not(.dark) .value-card {
        background: white !important;
        border-color: rgba(0,0,0,0.08) !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }
    :root:not(.dark) .value-card:hover {
        border-color: rgba(74,127,191,0.2) !important;
        box-shadow: 0 12px 30px rgba(0,0,0,0.08);
    }
    :root:not(.dark) .trading-card-inner {
        background: white !important;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }
    :root:not(.dark) .trading-card-inner .stat-cell {
        background: #f9fafb !important;
        border-color: #e5e7eb !important;
    }
    :root:not(.dark) .holo-border::before {
        opacity: 0.25 !important;
    }
    :root:not(.dark) .trading-card-glow {
        display: none;
    }
    /* removed */
    :root:not(.dark) .cta-shimmer {
        background: linear-gradient(90deg, #1f2937 0%, #4A7FBF 50%, #1f2937 100%) !important;
        background-size: 200% auto !important;
        -webkit-background-clip: text !important;
        background-clip: text !important;
        -webkit-text-fill-color: transparent !important;
        animation: shimmer 3s linear infinite !important;
    }
</style>

    {{-- Hero --}}
    <div class="noise-overlay relative overflow-hidden border-b border-gray-200 dark:border-[#1e2a3a] bg-white dark:bg-transparent">
        {{-- Ambient glow --}}
        <div class="absolute top-1/3 left-1/4 w-[500px] h-[500px] rounded-full opacity-0 dark:opacity-[0.06] blur-[120px]" style="background: radial-gradient(circle, #4A7FBF, transparent 70%);"></div>
        <div class="absolute bottom-0 right-1/4 w-[400px] h-[400px] rounded-full opacity-0 dark:opacity-[0.04] blur-[100px]" style="background: radial-gradient(circle, #9D5175, transparent 70%);"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-20">
            <div class="flex flex-col md:flex-row gap-8 md:gap-16 lg:gap-20 items-center">

                {{-- Trading Card (Flip) --}}
                <div class="flex-shrink-0 relative" x-data="{ flipped: false, hinted: true }">
                    {{-- Pulsing ambient glow --}}
                    <div class="trading-card-glow absolute inset-0 -m-8 rounded-full blur-[60px] opacity-0 dark:opacity-100" style="background: radial-gradient(circle, #4A7FBF 0%, #9D5175 50%, transparent 70%);"></div>

                    <div class="card-flip-container" @click="flipped = !flipped; hinted = false">
                        <div class="card-flip w-[250px] md:w-[250px] lg:w-[300px]" :class="{ 'flipped': flipped }">

                            {{-- FRONT: Portrait --}}
                            <div class="card-front">
                                <div class="holo-border relative">
                                    <div class="trading-card-inner relative rounded-2xl overflow-hidden shadow-2xl bg-white dark:bg-[#111820]">
                                        <div class="h-1.5 w-full" style="background: linear-gradient(90deg, #4A7FBF, #E47A9D, #4A7FBF, #9D5175, #4A7FBF);"></div>
                                        <div class="flex items-center justify-between px-5 pt-3 pb-2">
                                            <span class="text-[9px] font-mono text-gray-400 dark:text-gray-600 uppercase tracking-wider">Developer Card</span>
                                            <span class="px-1.5 py-0.5 text-[8px] font-bold uppercase tracking-wider rounded-full whitespace-nowrap" style="color: #E47A9D; border: 1px solid #E47A9D33; background: #E47A9D08;">✦ Legendary</span>
                                        </div>
                                        <div class="mx-4 rounded-xl overflow-hidden border-2 border-gray-200 dark:border-[#1e2a3a] relative">
                                            <div class="aspect-[3/4] bg-white">
                                                <img src="/images/avatar.jpg" alt="Jeffrey Davidson" class="w-full h-full object-cover object-top">
                                            </div>
                                        </div>
                                        <div class="px-5 pt-3 pb-1 text-center">
                                            <h2 class="text-xl font-empera tracking-wide text-gray-900 dark:text-white">Jeffrey Davidson</h2>
                                            <div class="flex items-center justify-center gap-2 mt-1">
                                                <div class="h-px flex-1 bg-gradient-to-r from-transparent to-[#4A7FBF]/30"></div>
                                                <p class="text-[11px] font-semibold uppercase tracking-[0.2em]" style="color: #4A7FBF;">Laravel Architect</p>
                                                <div class="h-px flex-1 bg-gradient-to-l from-transparent to-[#4A7FBF]/30"></div>
                                            </div>
                                        </div>
                                        <div class="h-1 mt-3" style="background: linear-gradient(90deg, #4A7FBF, #E47A9D, #4A7FBF, #9D5175, #4A7FBF);"></div>
                                    </div>
                                </div>
                            </div>

                            {{-- BACK: Stats --}}
                            <div class="card-back">
                                <div class="holo-border relative">
                                    <div class="trading-card-inner relative rounded-2xl overflow-hidden shadow-2xl bg-white dark:bg-[#111820]">
                                        <div class="h-1.5 w-full" style="background: linear-gradient(90deg, #9D5175, #4A7FBF, #E47A9D, #4A7FBF, #9D5175);"></div>
                                        <div class="flex items-center justify-between px-5 pt-3 pb-2">
                                            <span class="text-[9px] font-mono text-gray-400 dark:text-gray-600 uppercase tracking-wider">Stat Sheet</span>
                                            <span class="text-[9px] font-mono text-gray-400 dark:text-gray-600 uppercase tracking-wider">#001</span>
                                        </div>

                                        <div class="stats-content">
                                        {{-- Name plate on back too --}}
                                        <div class="px-5 pb-3 pt-2 text-center">
                                            <h2 class="text-lg font-empera tracking-wide text-gray-900 dark:text-white">Jeffrey Davidson</h2>
                                            <div class="flex items-center justify-center gap-2 mt-0.5">
                                                <div class="h-px flex-1 bg-gradient-to-r from-transparent to-[#9D5175]/30"></div>
                                                <p class="text-[10px] font-semibold uppercase tracking-[0.2em]" style="color: #9D5175;">Stats &amp; Specs</p>
                                                <div class="h-px flex-1 bg-gradient-to-l from-transparent to-[#9D5175]/30"></div>
                                            </div>
                                        </div>

                                        {{-- Stats grid --}}
                                        <div class="px-4 pb-3">
                                            <div class="grid grid-cols-2 gap-2">
                                                <div class="stat-cell px-3 py-2.5 rounded-lg bg-gray-50 dark:bg-[#0D1117]/80 border border-gray-200 dark:border-[#1e2a3a]/50">
                                                    <span class="text-[9px] uppercase tracking-wider text-gray-500 dark:text-gray-400 block">PHP</span>
                                                    <span class="text-sm font-mono font-bold text-gray-700 dark:text-gray-200">2008</span>
                                                </div>
                                                <div class="stat-cell px-3 py-2.5 rounded-lg bg-gray-50 dark:bg-[#0D1117]/80 border border-gray-200 dark:border-[#1e2a3a]/50">
                                                    <span class="text-[9px] uppercase tracking-wider text-gray-500 dark:text-gray-400 block">Laravel</span>
                                                    <span class="text-sm font-mono font-bold text-gray-700 dark:text-gray-200">v4.2+</span>
                                                </div>
                                                <div class="stat-cell px-3 py-2.5 rounded-lg bg-gray-50 dark:bg-[#0D1117]/80 border border-gray-200 dark:border-[#1e2a3a]/50">
                                                    <span class="text-[9px] uppercase tracking-wider text-gray-500 dark:text-gray-400 block">Test Suites</span>
                                                    <span class="text-sm font-mono font-bold text-gray-700 dark:text-gray-200">3</span>
                                                </div>
                                                <div class="stat-cell px-3 py-2.5 rounded-lg bg-gray-50 dark:bg-[#0D1117]/80 border border-gray-200 dark:border-[#1e2a3a]/50">
                                                    <span class="text-[9px] uppercase tracking-wider text-gray-500 dark:text-gray-400 block">Experience</span>
                                                    <span class="text-sm font-mono font-bold text-gray-700 dark:text-gray-200">15+ yrs</span>
                                                </div>
                                                <div class="stat-cell px-3 py-2.5 rounded-lg bg-gray-50 dark:bg-[#0D1117]/80 border border-gray-200 dark:border-[#1e2a3a]/50">
                                                    <span class="text-[9px] uppercase tracking-wider text-gray-500 dark:text-gray-400 block">Location</span>
                                                    <span class="text-sm font-mono font-bold text-gray-700 dark:text-gray-200">Florida</span>
                                                </div>
                                                <div class="stat-cell px-3 py-2.5 rounded-lg bg-gray-50 dark:bg-[#0D1117]/80 border border-gray-200 dark:border-[#1e2a3a]/50">
                                                    <span class="text-[9px] uppercase tracking-wider text-gray-500 dark:text-gray-400 block">Podcasts</span>
                                                    <span class="text-sm font-mono font-bold text-gray-700 dark:text-gray-200">2</span>
                                                </div>
                                            </div>
                                            {{-- Flavor text --}}
                                            <div class="mt-3 px-1 pt-3 border-t border-gray-200 dark:border-[#1e2a3a]/50">
                                                <p class="text-[11px] italic text-gray-400 dark:text-gray-500 leading-relaxed text-center">"Writes tests before coffee. Believes every application deserves clean architecture."</p>
                                            </div>
                                        </div>
                                        </div>
                                        <div class="h-1 mt-auto" style="background: linear-gradient(90deg, #9D5175, #4A7FBF, #E47A9D, #4A7FBF, #9D5175);"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Flip hint --}}
                    <div class="flip-hint flex items-center justify-center gap-2 mt-4" :class="{ 'hidden': !hinted }">
                        <svg class="w-4 h-4 text-gray-400 dark:text-gray-600 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.042 21.672L13.684 16.6m0 0l-2.51 2.225.569-9.47 5.227 7.917-3.286-.672zM12 2.25V4.5m5.834.166l-1.591 1.591M20.25 10.5H18M7.757 14.743l-1.59 1.59M6 10.5H3.75m4.007-4.243l-1.59-1.59" />
                        </svg>
                        <span class="text-[11px] text-gray-400 dark:text-gray-600 font-medium tracking-wide">Click card to flip</span>
                        <svg class="w-3.5 h-3.5 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12c0-1.232-.046-2.453-.138-3.662a4.006 4.006 0 00-3.7-3.7 48.678 48.678 0 00-7.324 0 4.006 4.006 0 00-3.7 3.7c-.017.22-.032.441-.046.662M19.5 12l3-3m-3 3l-3-3m-12 3c0 1.232.046 2.453.138 3.662a4.006 4.006 0 003.7 3.7 48.656 48.656 0 007.324 0 4.006 4.006 0 003.7-3.7c.017-.22.032-.441.046-.662M4.5 12l3 3m-3-3l-3 3" />
                        </svg>
                    </div>
                </div>

                {{-- Intro Text --}}
                <div class="flex-1 text-center md:text-left">
                    <div class="flex items-center gap-3 mb-4 justify-center md:justify-start">
                        <div class="font-mono text-sm text-gray-500 flex items-center gap-2">
                            <span class="text-[#4A7FBF]">$</span>
                            <span>php artisan about:me</span>
                            <span class="animate-pulse text-gray-400 dark:text-[#4A7FBF] relative -top-px">▊</span>
                        </div>
                    </div>

                    <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold mb-4 leading-tight text-gray-900 dark:text-white">
                        I've spent 15 years learning how to write code that my future self <span class="text-[#4A7FBF]">won't hate.</span>
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400 text-lg leading-relaxed max-w-2xl mx-auto md:mx-0">
                        Web developer based in Florida. I build clean, maintainable applications with Laravel, talk about it on two podcasts, and I'm putting together a YouTube channel. When I'm not coding, I'm being a dad, exploring theme parks, and pretending I'm going to get better at poker.
                    </p>

                    <div class="flex flex-wrap gap-4 justify-center md:justify-start mt-6">
                        <a href="{{ route('contact') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#4A7FBF] hover:bg-[#5A8FD0] text-white text-sm font-semibold rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            Get in Touch
                        </a>
                        <a href="https://github.com/JeffreyDavidson" target="_blank" class="inline-flex items-center gap-2 px-5 py-2.5 border border-gray-200 dark:border-[#1e2a3a] hover:border-gray-400 dark:hover:border-gray-600 text-gray-900 dark:text-white text-sm font-semibold rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
                            GitHub
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- The Story --}}
    <div class="dot-grid-bg bg-gray-50 dark:bg-transparent">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-20">
            <div class="flex flex-col lg:flex-row gap-16">
                {{-- Main story --}}
                <div class="flex-1">
                    <h2 class="text-2xl font-extrabold mb-8 flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg bg-[#4A7FBF]/10 flex items-center justify-center">
                            <svg class="w-4 h-4 text-[#4A7FBF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        </span>
                        My Story
                    </h2>

                    <div class="space-y-6 text-gray-600 dark:text-gray-400 leading-relaxed">
                        <p>
                            I grew up in the suburbs of Kansas, messing around with HTML on a desktop in my bedroom and spending way too much time customizing MySpace layouts. That turned into PHP, which turned into a career I never planned on having.
                        </p>
                        <p>
                            After a couple semesters at community college learning table-based layouts (seriously), I enrolled at <strong class="text-gray-900 dark:text-gray-200">Full Sail University</strong> and earned my Bachelor of Science in Web Design and Development. That gave me the structure I'd been missing as a self-taught developer.
                        </p>
                        <p>
                            I found <strong class="text-gray-900 dark:text-gray-200">Laravel</strong> in 2014, specifically version 4.2, and everything clicked. Here was a framework that was opinionated in all the right ways, that made PHP feel modern, that actually cared about developer experience. I've been building with it ever since.
                        </p>
                        <p>
                            A big part of my career has been <strong class="text-gray-900 dark:text-gray-200">modernization work</strong>: taking legacy codebases written in CodeIgniter, Yii2, CakePHP, and ExpressionEngine and rewriting them in Laravel. Every migration taught me something about untangling technical debt and building something clean from the wreckage.
                        </p>
                        <p>
                            In 2015, my wife Cassie and I packed up our Kansas lives and moved to <strong class="text-gray-900 dark:text-gray-200">Florida</strong>. In 2017, our daughter <strong class="text-gray-900 dark:text-gray-200">Viola</strong> came along, and being her dad has reshaped my priorities, my patience, and my entire perspective on what matters.
                        </p>
                        <p>
                            Now I'm building content alongside code. <strong class="text-gray-900 dark:text-gray-200">Coffee with The Laravel Architect</strong> is me talking about the framework I love. <strong class="text-gray-900 dark:text-gray-200">Embracing Cloudy Days</strong> is the personal side, covering mental health, parenting, and the messier parts of life. And <strong class="text-gray-900 dark:text-gray-200">The Laravel Architect</strong> YouTube channel is bringing tutorials and live coding to the mix.
                        </p>
                    </div>
                </div>

                {{-- Timeline sidebar (vertical on mobile & large) --}}
                <div class="lg:w-80 flex-shrink-0 hidden lg:block">
                    <h2 class="text-2xl font-extrabold mb-8 flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg bg-[#4A7FBF]/10 flex items-center justify-center">
                            <svg class="w-4 h-4 text-[#4A7FBF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                        Timeline
                    </h2>
                    <div class="space-y-6">
                        <div class="timeline-item">
                            <span class="text-xs font-bold text-[#4A7FBF]">~2008</span>
                            <p class="text-sm text-gray-700 dark:text-gray-300 font-semibold mt-1">Started writing PHP</p>
                            <p class="text-xs text-gray-500 mt-0.5">Self-taught, building things for fun</p>
                        </div>
                        <div class="timeline-item">
                            <span class="text-xs font-bold text-[#4A7FBF]">2012</span>
                            <p class="text-sm text-gray-700 dark:text-gray-300 font-semibold mt-1">Full Sail University</p>
                            <p class="text-xs text-gray-500 mt-0.5">B.S. in Web Design & Development</p>
                        </div>
                        <div class="timeline-item">
                            <span class="text-xs font-bold text-[#4A7FBF]">2014</span>
                            <p class="text-sm text-gray-700 dark:text-gray-300 font-semibold mt-1">Discovered Laravel 4.2</p>
                            <p class="text-xs text-gray-500 mt-0.5">Everything clicked</p>
                        </div>
                        <div class="timeline-item">
                            <span class="text-xs font-bold text-[#4A7FBF]">2015</span>
                            <p class="text-sm text-gray-700 dark:text-gray-300 font-semibold mt-1">Moved to Florida</p>
                            <p class="text-xs text-gray-500 mt-0.5">Packed up Kansas, headed south</p>
                        </div>
                        <div class="timeline-item">
                            <span class="text-xs font-bold text-[#4A7FBF]">2017</span>
                            <p class="text-sm text-gray-700 dark:text-gray-300 font-semibold mt-1">Daughter Viola born</p>
                            <p class="text-xs text-gray-500 mt-0.5">Changed everything</p>
                        </div>
                        <div class="timeline-item">
                            <span class="text-xs font-bold text-[#4A7FBF]">2026</span>
                            <p class="text-sm text-gray-700 dark:text-gray-300 font-semibold mt-1">The Laravel Architect</p>
                            <p class="text-xs text-gray-500 mt-0.5">Blog, podcasts, YouTube. Building in public</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Horizontal timeline (md only) --}}
            <div class="hidden md:block lg:hidden mt-16">
                <h2 class="text-2xl font-extrabold mb-10 text-center text-gray-900 dark:text-white">Timeline</h2>
                <div class="relative">
                    {{-- Horizontal line --}}
                    <div class="absolute left-0 right-0 top-1/2 h-0.5 bg-gradient-to-r from-transparent via-[#4A7FBF]/40 to-transparent"></div>

                    <div class="grid grid-cols-6 gap-2">
                        @php
                            $timelineItems = [
                                ['year' => '~2008', 'title' => 'Started writing PHP', 'desc' => 'Self-taught, building for fun'],
                                ['year' => '2012', 'title' => 'Full Sail University', 'desc' => 'B.S. Web Design & Dev'],
                                ['year' => '2014', 'title' => 'Discovered Laravel 4.2', 'desc' => 'Everything clicked'],
                                ['year' => '2015', 'title' => 'Moved to Florida', 'desc' => 'Packed up Kansas'],
                                ['year' => '2017', 'title' => 'Daughter Viola born', 'desc' => 'Changed everything'],
                                ['year' => '2026', 'title' => 'The Laravel Architect', 'desc' => 'Building in public'],
                            ];
                        @endphp

                        @foreach($timelineItems as $i => $item)
                            <div class="relative flex flex-col items-center {{ $i % 2 === 0 ? 'pt-0 pb-20' : 'pt-20 pb-0' }}">
                                {{-- Content above or below --}}
                                @if($i % 2 === 0)
                                    <div class="text-center mb-4">
                                        <span class="text-xs font-bold text-[#4A7FBF]">{{ $item['year'] }}</span>
                                        <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 mt-1">{{ $item['title'] }}</p>
                                        <p class="text-[10px] text-gray-500 mt-0.5">{{ $item['desc'] }}</p>
                                    </div>
                                @endif

                                {{-- Dot --}}
                                <div class="w-3 h-3 rounded-full bg-[#4A7FBF] shadow-[0_0_10px_rgba(74,127,191,0.4)] z-10 flex-shrink-0"></div>

                                @if($i % 2 !== 0)
                                    <div class="text-center mt-4">
                                        <span class="text-xs font-bold text-[#4A7FBF]">{{ $item['year'] }}</span>
                                        <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 mt-1">{{ $item['title'] }}</p>
                                        <p class="text-[10px] text-gray-500 mt-0.5">{{ $item['desc'] }}</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Vertical timeline (mobile only) --}}
            <div class="md:hidden mt-12">
                <h2 class="text-2xl font-extrabold mb-8 flex items-center gap-3">
                    <span class="w-8 h-8 rounded-lg bg-[#4A7FBF]/10 flex items-center justify-center">
                        <svg class="w-4 h-4 text-[#4A7FBF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                    Timeline
                </h2>
                <div class="space-y-6">
                    <div class="timeline-item">
                        <span class="text-xs font-bold text-[#4A7FBF]">~2008</span>
                        <p class="text-sm text-gray-700 dark:text-gray-300 font-semibold mt-1">Started writing PHP</p>
                        <p class="text-xs text-gray-500 mt-0.5">Self-taught, building things for fun</p>
                    </div>
                    <div class="timeline-item">
                        <span class="text-xs font-bold text-[#4A7FBF]">2012</span>
                        <p class="text-sm text-gray-700 dark:text-gray-300 font-semibold mt-1">Full Sail University</p>
                        <p class="text-xs text-gray-500 mt-0.5">B.S. in Web Design & Development</p>
                    </div>
                    <div class="timeline-item">
                        <span class="text-xs font-bold text-[#4A7FBF]">2014</span>
                        <p class="text-sm text-gray-700 dark:text-gray-300 font-semibold mt-1">Discovered Laravel 4.2</p>
                        <p class="text-xs text-gray-500 mt-0.5">Everything clicked</p>
                    </div>
                    <div class="timeline-item">
                        <span class="text-xs font-bold text-[#4A7FBF]">2015</span>
                        <p class="text-sm text-gray-700 dark:text-gray-300 font-semibold mt-1">Moved to Florida</p>
                        <p class="text-xs text-gray-500 mt-0.5">Packed up Kansas, headed south</p>
                    </div>
                    <div class="timeline-item">
                        <span class="text-xs font-bold text-[#4A7FBF]">2017</span>
                        <p class="text-sm text-gray-700 dark:text-gray-300 font-semibold mt-1">Daughter Viola born</p>
                        <p class="text-xs text-gray-500 mt-0.5">Changed everything</p>
                    </div>
                    <div class="timeline-item">
                        <span class="text-xs font-bold text-[#4A7FBF]">2026</span>
                        <p class="text-sm text-gray-700 dark:text-gray-300 font-semibold mt-1">The Laravel Architect</p>
                        <p class="text-xs text-gray-500 mt-0.5">Blog, podcasts, YouTube. Building in public</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- What I Believe In --}}
    <div class="border-t border-gray-200 dark:border-[#1e2a3a] bg-white dark:bg-transparent">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-20">
            <div class="text-center mb-14">
                <p class="text-xs font-semibold uppercase tracking-widest text-gray-600 mb-3">Core Values</p>
                <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 dark:text-white">What I Believe In</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="value-card p-6 rounded-2xl border border-gray-200 dark:border-[#1e2a3a] bg-white dark:bg-[#0D1117]">
                    <div class="w-10 h-10 rounded-xl bg-[#4A7FBF]/10 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-[#4A7FBF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <h3 class="font-bold mb-2 text-gray-900 dark:text-white">Architecture Over Cleverness</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">Clean structure beats clever tricks every time. Code should be readable, predictable, and easy to change. If your future self can't understand it, it's not good code.</p>
                </div>
                <div class="value-card p-6 rounded-2xl border border-gray-200 dark:border-[#1e2a3a] bg-white dark:bg-[#0D1117]">
                    <div class="w-10 h-10 rounded-xl bg-[#4A7FBF]/10 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-[#4A7FBF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="font-bold mb-2 text-gray-900 dark:text-white">Tests Are Not Optional</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">I run three test suites on every project: Feature, Integration, and Unit. Tests aren't overhead. They're how you ship with confidence and sleep at night.</p>
                </div>
                <div class="value-card p-6 rounded-2xl border border-gray-200 dark:border-[#1e2a3a] bg-white dark:bg-[#0D1117]">
                    <div class="w-10 h-10 rounded-xl bg-[#4A7FBF]/10 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-[#4A7FBF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <h3 class="font-bold mb-2 text-gray-900 dark:text-white">Teach What You Learn</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">The best way to solidify knowledge is to share it. Every blog post, podcast episode, and tutorial is me learning out loud, and hopefully making someone else's path easier.</p>
                </div>
                <div class="value-card value-card-pink p-6 rounded-2xl border border-gray-200 dark:border-[#1e2a3a] bg-white dark:bg-[#0D1117]">
                    <div class="w-10 h-10 rounded-xl bg-[#9D5175]/10 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-[#9D5175]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                    </div>
                    <h3 class="font-bold mb-2 text-gray-900 dark:text-white">Family First</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">My daughter Viola is autistic and nonverbal, and being her dad has taught me more about patience, empathy, and what really matters than any codebase ever could.</p>
                </div>
                <div class="value-card value-card-pink p-6 rounded-2xl border border-gray-200 dark:border-[#1e2a3a] bg-white dark:bg-[#0D1117]">
                    <div class="w-10 h-10 rounded-xl bg-[#9D5175]/10 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-[#9D5175]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/></svg>
                    </div>
                    <h3 class="font-bold mb-2 text-gray-900 dark:text-white">Embrace the Cloudy Days</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">Not every day is sunshine. I talk openly about mental health, burnout, and the hard parts of being a developer and a parent. Vulnerability isn't weakness. It's honesty.</p>
                </div>
                <div class="value-card p-6 rounded-2xl border border-gray-200 dark:border-[#1e2a3a] bg-white dark:bg-[#0D1117]">
                    <div class="w-10 h-10 rounded-xl bg-[#4A7FBF]/10 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-[#4A7FBF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <h3 class="font-bold mb-2 text-gray-900 dark:text-white">Ship, Don't Perfect</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">Done is better than perfect. I've learned more from shipping imperfect things and iterating than from any project I polished endlessly in private.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Fun Facts --}}
    <div class="border-t border-gray-200 dark:border-[#1e2a3a] bg-gray-50 dark:bg-transparent">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6 text-center">
                <div>
                    <span class="text-2xl mb-1 block">☕</span>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Daily Coffee</p>
                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mt-1">Too Many</p>
                </div>
                <div>
                    <span class="text-2xl mb-1 block">🎢</span>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Favorite Park</p>
                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mt-1">Disney World</p>
                </div>
                <div>
                    <span class="text-2xl mb-1 block">🃏</span>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Poker Style</p>
                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mt-1">Aggressive</p>
                </div>
                <div>
                    <span class="text-2xl mb-1 block">🤼</span>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Side Project</p>
                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mt-1">Wrestling App</p>
                </div>
                <div>
                    <span class="text-2xl mb-1 block">🏫</span>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Alma Mater</p>
                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mt-1">Full Sail University</p>
                </div>
                <div>
                    <span class="text-2xl mb-1 block">🏀</span>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Forever Fan</p>
                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mt-1">Rock Chalk</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Tech Stack --}}
    <div class="border-t border-gray-200 dark:border-[#1e2a3a] dot-grid-bg bg-white dark:bg-transparent">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-20">
            <div class="text-center mb-14">
                <p class="text-xs font-semibold uppercase tracking-widest text-gray-600 mb-3">Toolbox</p>
                <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 dark:text-white">What I Work With</h2>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach([
                    ['name' => 'Laravel', 'icon' => '🔺', 'desc' => 'My framework of choice since 2014'],
                    ['name' => 'PHP', 'icon' => '🐘', 'desc' => 'The language that started it all'],
                    ['name' => 'Filament', 'icon' => '🛡️', 'desc' => 'Admin panels done right'],
                    ['name' => 'Livewire', 'icon' => '⚡', 'desc' => 'Reactive interfaces without the SPA'],
                    ['name' => 'Tailwind CSS', 'icon' => '🎨', 'desc' => 'Utility-first, no going back'],
                    ['name' => 'Alpine.js', 'icon' => '🏔️', 'desc' => 'Just enough JavaScript'],
                    ['name' => 'Pest', 'icon' => '🧪', 'desc' => 'Testing with elegance'],
                    ['name' => 'MySQL', 'icon' => '🗄️', 'desc' => 'Relational data, done well'],
                    ['name' => 'Redis', 'icon' => '⚡', 'desc' => 'Caching, queues, sessions'],
                    ['name' => 'Laravel Forge', 'icon' => '🔨', 'desc' => 'Deployment without the pain'],
                    ['name' => 'Git', 'icon' => '📦', 'desc' => 'Version everything, always'],
                    ['name' => 'SQLite', 'icon' => '💾', 'desc' => 'Perfect for the right project'],
                ] as $tech)
                <div class="value-card p-4 rounded-xl border border-gray-200 dark:border-[#1e2a3a] bg-white dark:bg-[#0D1117]/50 hover:border-[#4A7FBF]/20 dark:hover:border-[#4A7FBF]/20">
                    <span class="text-lg mb-1 block">{{ $tech['icon'] }}</span>
                    <p class="font-semibold text-sm mb-0.5 text-gray-900 dark:text-white">{{ $tech['name'] }}</p>
                    <p class="text-xs text-gray-500">{{ $tech['desc'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- CTA --}}
    <div class="relative border-t border-gray-200 dark:border-[#1e2a3a] overflow-hidden bg-gray-50 dark:bg-transparent">
        {{-- Floating orbs --}}
        <div class="absolute top-1/4 left-1/4 w-64 h-64 rounded-full opacity-[0.08] dark:opacity-[0.06] blur-[80px] bg-[#4A7FBF]"></div>
        <div class="absolute bottom-1/4 right-1/4 w-48 h-48 rounded-full opacity-[0.06] dark:opacity-[0.06] blur-[80px] bg-[#9D5175]"></div>

        <div class="relative max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-28 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-green-500/10 text-green-600 dark:text-green-400 text-xs font-bold uppercase tracking-widest mb-6 border border-green-500/20">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-500 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                </span>
                Available for Projects
            </div>
            <h2 class="text-3xl md:text-4xl font-extrabold mb-4">
                <span class="cta-shimmer" style="background: linear-gradient(90deg, #fff 0%, #4A7FBF 50%, #fff 100%); background-size: 200% auto; -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; animation: shimmer 3s linear infinite;">Want to work together?</span>
            </h2>
            <p class="text-gray-600 dark:text-gray-400 text-lg mb-8 max-w-xl mx-auto">I'm available for freelance Laravel development, consulting, and legacy modernization projects. Let's talk about what you're building.</p>
            <x-button href="{{ route('contact') }}" class="px-8 py-3.5 text-lg" style="box-shadow: 0 0 30px rgba(74,127,191,0.3);">
                Contact Me
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </x-button>
        </div>
    </div>
@endsection
