@extends('layouts.app')

@section('title', 'About')

@section('content')
<style>
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
    .timeline-item {
        position: relative;
        padding-left: 2rem;
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
        transition: all 0.3s ease;
    }
    .value-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.4);
    }

    /* Trading Card */
    .trading-card-tilt {
        perspective: 1000px;
    }
    .trading-card-inner {
        transition: transform 0.4s ease, box-shadow 0.4s ease;
        transform-style: preserve-3d;
    }
    .trading-card-tilt:hover .trading-card-inner {
        transform: rotateY(-3deg) rotateX(2deg) scale(1.02);
        box-shadow:
            0 25px 50px -12px rgba(0, 0, 0, 0.6),
            0 0 40px rgba(74, 127, 191, 0.1),
            0 0 80px rgba(157, 81, 117, 0.05);
    }

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
</style>

    {{-- Hero --}}
    <div class="noise-overlay relative overflow-hidden border-b border-[#1e2a3a]">
        {{-- Ambient glow --}}
        <div class="absolute top-1/3 left-1/4 w-[600px] h-[600px] rounded-full opacity-[0.06] blur-[120px]" style="background: radial-gradient(circle, #4A7FBF, transparent 70%);"></div>
        <div class="absolute bottom-0 right-1/4 w-[500px] h-[500px] rounded-full opacity-[0.05] blur-[100px]" style="background: radial-gradient(circle, #9D5175, transparent 70%);"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24">
            <div class="flex flex-col lg:flex-row gap-12 lg:gap-20 items-center">

                {{-- Trading Card --}}
                <div class="flex-shrink-0 trading-card-tilt">
                    <div class="holo-border">
                        <div class="trading-card-inner relative w-[300px] rounded-2xl overflow-hidden shadow-2xl" style="background: linear-gradient(165deg, #111820 0%, #0a0e14 50%, #0f1520 100%);">

                            {{-- Top holographic stripe --}}
                            <div class="h-1.5 w-full" style="background: linear-gradient(90deg, #4A7FBF, #E47A9D, #4A7FBF, #9D5175, #4A7FBF);"></div>

                            {{-- Card header --}}
                            <div class="flex items-center justify-between px-5 pt-3 pb-2">
                                <span class="text-[10px] font-mono text-gray-600 uppercase tracking-widest">Developer Card</span>
                                <span class="px-2 py-0.5 text-[9px] font-bold uppercase tracking-widest rounded-full" style="color: #E47A9D; border: 1px solid #E47A9D33; background: #E47A9D08;">✦ Legendary</span>
                            </div>

                            {{-- Avatar frame --}}
                            <div class="mx-4 rounded-xl overflow-hidden border-2 border-[#1e2a3a] relative">
                                <div class="aspect-[4/3] bg-white">
                                    <img src="/images/logo-alternate.jpg" alt="Jeffrey Davidson" class="w-full h-full object-cover object-top">
                                </div>
                                {{-- Gradient overlay at bottom of image --}}
                                <div class="absolute bottom-0 inset-x-0 h-12 bg-gradient-to-t from-[#111820] to-transparent"></div>
                            </div>

                            {{-- Name plate --}}
                            <div class="px-5 pt-3 pb-1 text-center">
                                <h2 class="text-xl font-empera tracking-wide">Jeffrey Davidson</h2>
                                <div class="flex items-center justify-center gap-2 mt-1">
                                    <div class="h-px flex-1 bg-gradient-to-r from-transparent to-[#4A7FBF]/30"></div>
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em]" style="color: #4A7FBF;">Laravel Architect</p>
                                    <div class="h-px flex-1 bg-gradient-to-l from-transparent to-[#4A7FBF]/30"></div>
                                </div>
                            </div>

                            {{-- Stats grid --}}
                            <div class="px-4 pt-3 pb-4">
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="px-3 py-2 rounded-lg bg-[#0D1117]/80 border border-[#1e2a3a]/50">
                                        <span class="text-[10px] uppercase tracking-wider text-gray-600 block">PHP</span>
                                        <span class="text-sm font-mono font-bold text-gray-200">2008</span>
                                    </div>
                                    <div class="px-3 py-2 rounded-lg bg-[#0D1117]/80 border border-[#1e2a3a]/50">
                                        <span class="text-[10px] uppercase tracking-wider text-gray-600 block">Laravel</span>
                                        <span class="text-sm font-mono font-bold text-gray-200">v4.2+</span>
                                    </div>
                                    <div class="px-3 py-2 rounded-lg bg-[#0D1117]/80 border border-[#1e2a3a]/50">
                                        <span class="text-[10px] uppercase tracking-wider text-gray-600 block">Test Suites</span>
                                        <span class="text-sm font-mono font-bold text-gray-200">3</span>
                                    </div>
                                    <div class="px-3 py-2 rounded-lg bg-[#0D1117]/80 border border-[#1e2a3a]/50">
                                        <span class="text-[10px] uppercase tracking-wider text-gray-600 block">Experience</span>
                                        <span class="text-sm font-mono font-bold text-gray-200">15+ yrs</span>
                                    </div>
                                    <div class="px-3 py-2 rounded-lg bg-[#0D1117]/80 border border-[#1e2a3a]/50">
                                        <span class="text-[10px] uppercase tracking-wider text-gray-600 block">Location</span>
                                        <span class="text-sm font-mono font-bold text-gray-200">Florida</span>
                                    </div>
                                    <div class="px-3 py-2 rounded-lg bg-[#0D1117]/80 border border-[#1e2a3a]/50">
                                        <span class="text-[10px] uppercase tracking-wider text-gray-600 block">Podcasts</span>
                                        <span class="text-sm font-mono font-bold text-gray-200">2</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Bottom holographic stripe --}}
                            <div class="h-1" style="background: linear-gradient(90deg, #4A7FBF, #E47A9D, #4A7FBF, #9D5175, #4A7FBF);"></div>
                        </div>
                    </div>
                </div>

                {{-- Intro Text --}}
                <div class="flex-1 text-center lg:text-left">
                    <p class="text-[#4A7FBF] text-sm font-semibold uppercase tracking-widest mb-4">About Me</p>
                    <h1 class="text-4xl md:text-5xl font-extrabold mb-6 leading-tight">
                        I've spent 15 years learning how to write code that my future self <span class="text-[#4A7FBF]">won't hate.</span>
                    </h1>
                    <p class="text-gray-400 text-lg leading-relaxed max-w-2xl mb-8">
                        Web developer based in Florida. I build clean, maintainable applications with Laravel, talk about it on two podcasts, and I'm putting together a YouTube channel. When I'm not coding, I'm being a dad, exploring theme parks, and pretending I'm going to get better at poker.
                    </p>

                    <div class="flex flex-wrap gap-4 justify-center lg:justify-start">
                        <a href="{{ route('contact') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#4A7FBF] hover:bg-[#5A8FD0] text-white text-sm font-semibold rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            Get in Touch
                        </a>
                        <a href="https://github.com/JeffreyDavidson" target="_blank" class="inline-flex items-center gap-2 px-5 py-2.5 border border-[#1e2a3a] hover:border-gray-600 text-sm font-semibold rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
                            GitHub
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- The Story --}}
    <div class="dot-grid-bg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24">
            <div class="flex flex-col lg:flex-row gap-16">
                {{-- Main story --}}
                <div class="flex-1">
                    <h2 class="text-2xl font-extrabold mb-8 flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg bg-[#4A7FBF]/10 flex items-center justify-center">
                            <svg class="w-4 h-4 text-[#4A7FBF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        </span>
                        My Story
                    </h2>

                    <div class="space-y-6 text-gray-400 leading-relaxed">
                        <p>
                            I grew up in the suburbs of Kansas, messing around with HTML on a desktop in my bedroom and spending way too much time customizing MySpace layouts. That turned into PHP, which turned into a career I never planned on having.
                        </p>
                        <p>
                            After a couple semesters at community college learning table-based layouts (seriously), I enrolled at <strong class="text-gray-200">Full Sail University</strong> and earned my Bachelor of Science in Web Design and Development. That gave me the structure I'd been missing as a self-taught developer.
                        </p>
                        <p>
                            I found <strong class="text-gray-200">Laravel</strong> in 2014 — version 4.2 — and everything clicked. Here was a framework that was opinionated in all the right ways, that made PHP feel modern, that actually cared about developer experience. I've been building with it ever since.
                        </p>
                        <p>
                            A big part of my career has been <strong class="text-gray-200">modernization work</strong> — taking legacy codebases written in CodeIgniter, Yii2, CakePHP, and ExpressionEngine and rewriting them in Laravel. Every migration taught me something about untangling technical debt and building something clean from the wreckage.
                        </p>
                        <p>
                            In 2015, my wife Cassie and I packed up our Kansas lives and moved to <strong class="text-gray-200">Florida</strong>. In 2017, our daughter <strong class="text-gray-200">Viola</strong> came along, and being her dad has reshaped my priorities, my patience, and my entire perspective on what matters.
                        </p>
                        <p>
                            Now I'm building content alongside code. <strong class="text-gray-200">Coffee with The Laravel Architect</strong> is me talking about the framework I love. <strong class="text-gray-200">Embracing Cloudy Days</strong> is the personal side — mental health, parenting, and the messier parts of life. And <strong class="text-gray-200">The Laravel Architect</strong> YouTube channel is bringing tutorials and live coding to the mix.
                        </p>
                    </div>
                </div>

                {{-- Timeline sidebar --}}
                <div class="lg:w-80 flex-shrink-0">
                    <h2 class="text-2xl font-extrabold mb-8 flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg bg-[#4A7FBF]/10 flex items-center justify-center">
                            <svg class="w-4 h-4 text-[#4A7FBF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                        Timeline
                    </h2>
                    <div class="space-y-6">
                        <div class="timeline-item">
                            <span class="text-xs font-bold text-[#4A7FBF]">~2008</span>
                            <p class="text-sm text-gray-300 font-semibold mt-1">Started writing PHP</p>
                            <p class="text-xs text-gray-500 mt-0.5">Self-taught, building things for fun</p>
                        </div>
                        <div class="timeline-item">
                            <span class="text-xs font-bold text-[#4A7FBF]">2012</span>
                            <p class="text-sm text-gray-300 font-semibold mt-1">Full Sail University</p>
                            <p class="text-xs text-gray-500 mt-0.5">B.S. in Web Design & Development</p>
                        </div>
                        <div class="timeline-item">
                            <span class="text-xs font-bold text-[#4A7FBF]">2014</span>
                            <p class="text-sm text-gray-300 font-semibold mt-1">Discovered Laravel 4.2</p>
                            <p class="text-xs text-gray-500 mt-0.5">Everything clicked</p>
                        </div>
                        <div class="timeline-item">
                            <span class="text-xs font-bold text-[#4A7FBF]">2015</span>
                            <p class="text-sm text-gray-300 font-semibold mt-1">Moved to Florida</p>
                            <p class="text-xs text-gray-500 mt-0.5">Packed up Kansas, headed south</p>
                        </div>
                        <div class="timeline-item">
                            <span class="text-xs font-bold text-[#4A7FBF]">2017</span>
                            <p class="text-sm text-gray-300 font-semibold mt-1">Daughter Viola born</p>
                            <p class="text-xs text-gray-500 mt-0.5">Changed everything</p>
                        </div>
                        <div class="timeline-item">
                            <span class="text-xs font-bold text-[#4A7FBF]">2026</span>
                            <p class="text-sm text-gray-300 font-semibold mt-1">The Laravel Architect</p>
                            <p class="text-xs text-gray-500 mt-0.5">Blog, podcasts, YouTube — building in public</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- What I Believe In --}}
    <div class="border-t border-[#1e2a3a]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24">
            <div class="text-center mb-14">
                <p class="text-xs font-semibold uppercase tracking-widest text-gray-600 mb-3">Core Values</p>
                <h2 class="text-3xl md:text-4xl font-extrabold text-white">What I Believe In</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="value-card p-6 rounded-2xl border border-[#1e2a3a] bg-[#0D1117]">
                    <div class="w-10 h-10 rounded-xl bg-[#4A7FBF]/10 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-[#4A7FBF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <h3 class="font-bold mb-2">Architecture Over Cleverness</h3>
                    <p class="text-sm text-gray-400 leading-relaxed">Clean structure beats clever tricks every time. Code should be readable, predictable, and easy to change. If your future self can't understand it, it's not good code.</p>
                </div>
                <div class="value-card p-6 rounded-2xl border border-[#1e2a3a] bg-[#0D1117]">
                    <div class="w-10 h-10 rounded-xl bg-[#4A7FBF]/10 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-[#4A7FBF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="font-bold mb-2">Tests Are Not Optional</h3>
                    <p class="text-sm text-gray-400 leading-relaxed">I run three test suites on every project: Feature, Integration, and Unit. Tests aren't overhead — they're how you ship with confidence and sleep at night.</p>
                </div>
                <div class="value-card p-6 rounded-2xl border border-[#1e2a3a] bg-[#0D1117]">
                    <div class="w-10 h-10 rounded-xl bg-[#4A7FBF]/10 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-[#4A7FBF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <h3 class="font-bold mb-2">Teach What You Learn</h3>
                    <p class="text-sm text-gray-400 leading-relaxed">The best way to solidify knowledge is to share it. Every blog post, podcast episode, and tutorial is me learning out loud — and hopefully making someone else's path easier.</p>
                </div>
                <div class="value-card p-6 rounded-2xl border border-[#1e2a3a] bg-[#0D1117]">
                    <div class="w-10 h-10 rounded-xl bg-[#9D5175]/10 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-[#9D5175]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                    </div>
                    <h3 class="font-bold mb-2">Family First</h3>
                    <p class="text-sm text-gray-400 leading-relaxed">My daughter Viola is autistic and nonverbal, and being her dad has taught me more about patience, empathy, and what really matters than any codebase ever could.</p>
                </div>
                <div class="value-card p-6 rounded-2xl border border-[#1e2a3a] bg-[#0D1117]">
                    <div class="w-10 h-10 rounded-xl bg-[#9D5175]/10 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-[#9D5175]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/></svg>
                    </div>
                    <h3 class="font-bold mb-2">Embrace the Cloudy Days</h3>
                    <p class="text-sm text-gray-400 leading-relaxed">Not every day is sunshine. I talk openly about mental health, burnout, and the hard parts of being a developer and a parent. Vulnerability isn't weakness — it's honesty.</p>
                </div>
                <div class="value-card p-6 rounded-2xl border border-[#1e2a3a] bg-[#0D1117]">
                    <div class="w-10 h-10 rounded-xl bg-[#4A7FBF]/10 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-[#4A7FBF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <h3 class="font-bold mb-2">Ship, Don't Perfect</h3>
                    <p class="text-sm text-gray-400 leading-relaxed">Done is better than perfect. I've learned more from shipping imperfect things and iterating than from any project I polished endlessly in private.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Tech Stack --}}
    <div class="border-t border-[#1e2a3a] dot-grid-bg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24">
            <div class="text-center mb-14">
                <p class="text-xs font-semibold uppercase tracking-widest text-gray-600 mb-3">Toolbox</p>
                <h2 class="text-3xl md:text-4xl font-extrabold text-white">What I Work With</h2>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach([
                    ['name' => 'Laravel', 'desc' => 'My framework of choice since 2014'],
                    ['name' => 'PHP', 'desc' => 'The language that started it all'],
                    ['name' => 'Filament', 'desc' => 'Admin panels done right'],
                    ['name' => 'Livewire', 'desc' => 'Reactive interfaces without the SPA'],
                    ['name' => 'Tailwind CSS', 'desc' => 'Utility-first, no going back'],
                    ['name' => 'Alpine.js', 'desc' => 'Just enough JavaScript'],
                    ['name' => 'Pest', 'desc' => 'Testing with elegance'],
                    ['name' => 'MySQL', 'desc' => 'Relational data, done well'],
                    ['name' => 'Redis', 'desc' => 'Caching, queues, sessions'],
                    ['name' => 'Laravel Forge', 'desc' => 'Deployment without the pain'],
                    ['name' => 'Git', 'desc' => 'Version everything, always'],
                    ['name' => 'SQLite', 'desc' => 'Perfect for the right project'],
                ] as $tech)
                <div class="value-card p-4 rounded-xl border border-[#1e2a3a] bg-[#0D1117]/50 hover:border-[#4A7FBF]/20">
                    <p class="font-semibold text-sm mb-0.5">{{ $tech['name'] }}</p>
                    <p class="text-xs text-gray-500">{{ $tech['desc'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- CTA --}}
    <div class="relative border-t border-[#1e2a3a] overflow-hidden">
        {{-- Floating orbs --}}
        <div class="absolute top-1/4 left-1/4 w-64 h-64 rounded-full opacity-[0.06] blur-[80px] bg-[#4A7FBF]"></div>
        <div class="absolute bottom-1/4 right-1/4 w-48 h-48 rounded-full opacity-[0.06] blur-[80px] bg-[#9D5175]"></div>

        <div class="relative max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-28 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-green-500/10 text-green-400 text-xs font-bold uppercase tracking-widest mb-6 border border-green-500/20">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-500 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                </span>
                Available for Projects
            </div>
            <h2 class="text-3xl md:text-4xl font-extrabold mb-4">Want to work together?</h2>
            <p class="text-gray-400 text-lg mb-8 max-w-xl mx-auto">I'm available for freelance Laravel development, consulting, and legacy modernization projects. Let's talk about what you're building.</p>
            <a href="{{ route('contact') }}" class="inline-flex items-center gap-2 px-8 py-3.5 bg-[#4A7FBF] hover:bg-[#5A8FD0] text-white font-bold rounded-xl transition-colors text-lg" style="box-shadow: 0 0 30px rgba(74,127,191,0.3);">
                Hire Me
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>
    </div>
@endsection
