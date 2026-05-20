@extends('layouts.app')

@section('title', 'About')

@section('content')
    @php
        $timelineItems = [
            ['year' => '~2008', 'title' => 'Started writing PHP', 'desc' => 'Self-taught, building things for fun'],
            ['year' => '2012', 'title' => 'Full Sail University', 'desc' => 'B.S. in Web Design & Development'],
            ['year' => '2014', 'title' => 'Discovered Laravel 4.2', 'desc' => 'Everything clicked'],
            ['year' => '2015', 'title' => 'Moved to Florida', 'desc' => 'Packed up Kansas, headed south'],
            ['year' => '2017', 'title' => 'Daughter Viola born', 'desc' => 'Changed everything'],
            ['year' => '2026', 'title' => 'The Laravel Architect', 'desc' => 'Blog, podcast, YouTube. Building in public'],
        ];

        $facts = [
            ['icon' => '☕', 'label' => 'Daily Coffee', 'value' => 'Too Many'],
            ['icon' => '🎢', 'label' => 'Favorite Park', 'value' => 'Disney World'],
            ['icon' => '🃏', 'label' => 'Poker Style', 'value' => 'Aggressive'],
            ['icon' => '🤼', 'label' => 'Side Project', 'value' => 'Wrestling App'],
            ['icon' => '🏫', 'label' => 'Alma Mater', 'value' => 'Full Sail University'],
            ['icon' => '🏀', 'label' => 'Forever Fan', 'value' => 'Rock Chalk'],
        ];

        $techStack = [
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
        ];
    @endphp

    {{-- Hero --}}
    <div class="noise-overlay relative overflow-hidden border-b border-gray-200 dark:border-brand-700 bg-white dark:bg-transparent">
        {{-- Ambient glow --}}
        <div class="absolute left-1/4 top-1/3 h-[500px] w-[500px] rounded-full bg-brand-600 opacity-0 blur-[120px] dark:opacity-[0.06]"></div>
        <div class="absolute bottom-0 right-1/4 h-[400px] w-[400px] rounded-full bg-accent-600 opacity-0 blur-[100px] dark:opacity-[0.04]"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-20">
            <div class="flex flex-col md:flex-row gap-8 md:gap-16 lg:gap-20 items-center">

                {{-- Trading Card (Flip) --}}
                <div class="flex-shrink-0 relative" x-data="{ flipped: false }">
                    {{-- Pulsing ambient glow --}}
                    <div class="about-trading-card-glow absolute inset-0 -m-8 rounded-full bg-gradient-to-br from-brand-600 via-accent-600 to-transparent blur-[60px] opacity-0 dark:opacity-100"></div>

                    <div class="about-card-deck">
                        <div class="about-ghost-card about-ghost-card-2"></div>
                        <div class="about-ghost-card about-ghost-card-1"></div>
                    <div class="about-card-flip-container" @click="flipped = !flipped; $dispatch('about-card-flip', { flipped })">
                        <div class="about-card-flip w-[250px] md:w-[250px] lg:w-[300px]" :class="{ 'flipped': flipped }">

                            {{-- FRONT: Portrait --}}
                            <div class="about-card-front">
                                <div class="about-holo-border relative">
                                    <div class="about-trading-card-inner relative rounded-2xl overflow-hidden shadow-2xl bg-white dark:bg-brand-900">
                                        <div class="flex items-center justify-between px-5 pt-3 pb-2">
                                            <span class="text-[9px] font-mono text-gray-500 dark:text-gray-500 uppercase tracking-wider">Developer Card</span>
                                            <span class="whitespace-nowrap rounded-full border border-accent-400/20 bg-accent-400/5 px-1.5 py-0.5 text-[8px] font-bold uppercase tracking-wider text-accent-400">Legendary</span>
                                        </div>
                                        <div class="mx-4 rounded-xl overflow-hidden border-4 border-gray-300 dark:border-brand-700 relative flex-1">
                                            <img src="/images/avatar.jpg" alt="Jeffrey Davidson" class="w-full h-full object-cover object-top">
                                        </div>
                                        <div class="px-5 pt-3 pb-3 text-center">
                                            <h2 class="text-xl font-empera tracking-wide text-gray-900 dark:text-white">Jeffrey Davidson</h2>
                                            <div class="flex items-center justify-center gap-2 mt-1">
                                                <div class="h-px flex-1 bg-gradient-to-r from-transparent to-brand-600/30"></div>
                                                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-brand-600">Laravel Architect</p>
                                                <div class="h-px flex-1 bg-gradient-to-l from-transparent to-brand-600/30"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- BACK: Stats --}}
                            <div class="about-card-back">
                                <div class="about-holo-border relative">
                                    <div class="about-trading-card-inner relative rounded-2xl overflow-hidden shadow-2xl bg-white dark:bg-brand-900">
                                        <div class="flex items-center justify-between px-5 pt-3 pb-2">
                                            <span class="text-[9px] font-mono text-gray-500 dark:text-gray-400 uppercase tracking-wider">Stat Sheet</span>
                                            <span class="text-[9px] font-mono text-gray-500 dark:text-gray-400 uppercase tracking-wider">#001</span>
                                        </div>

                                        <div class="about-stats-content">
                                        {{-- Name plate on back too --}}
                                        <div class="px-5 pb-3 pt-2 text-center">
                                            <h2 class="text-lg font-empera tracking-wide text-gray-900 dark:text-white">Jeffrey Davidson</h2>
                                            <div class="flex items-center justify-center gap-2 mt-0.5">
                                                <div class="h-px flex-1 bg-gradient-to-r from-transparent to-accent-600/30"></div>
                                                <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-accent-600">Stats &amp; Specs</p>
                                                <div class="h-px flex-1 bg-gradient-to-l from-transparent to-accent-600/30"></div>
                                            </div>
                                        </div>

                                        {{-- Stats grid --}}
                                        <div class="px-4 pb-3">
                                            <div class="grid grid-cols-2 gap-2">
                                                <div class="about-stat-cell px-3 py-2.5 rounded-lg bg-gray-50 dark:bg-brand-950/80 border border-gray-200 dark:border-brand-700/50">
                                                    <span class="text-[9px] uppercase tracking-wider text-gray-500 dark:text-gray-400 block">PHP</span>
                                                    <span class="text-sm font-mono font-bold text-gray-700 dark:text-gray-200">8.4</span>
                                                </div>
                                                <div class="about-stat-cell px-3 py-2.5 rounded-lg bg-gray-50 dark:bg-brand-950/80 border border-gray-200 dark:border-brand-700/50">
                                                    <span class="text-[9px] uppercase tracking-wider text-gray-500 dark:text-gray-400 block">Laravel</span>
                                                    <span class="text-sm font-mono font-bold text-gray-700 dark:text-gray-200">12</span>
                                                </div>
                                                <div class="about-stat-cell px-3 py-2.5 rounded-lg bg-gray-50 dark:bg-brand-950/80 border border-gray-200 dark:border-brand-700/50">
                                                    <span class="text-[9px] uppercase tracking-wider text-gray-500 dark:text-gray-400 block">Stack</span>
                                                    <span class="text-sm font-mono font-bold text-gray-700 dark:text-gray-200">TALL</span>
                                                </div>
                                                <div class="about-stat-cell px-3 py-2.5 rounded-lg bg-gray-50 dark:bg-brand-950/80 border border-gray-200 dark:border-brand-700/50">
                                                    <span class="text-[9px] uppercase tracking-wider text-gray-500 dark:text-gray-400 block">Role</span>
                                                    <span class="text-sm font-mono font-bold text-gray-700 dark:text-gray-200">Sr. Software Eng</span>
                                                </div>
                                                <div class="about-stat-cell px-3 py-2.5 rounded-lg bg-gray-50 dark:bg-brand-950/80 border border-gray-200 dark:border-brand-700/50">
                                                    <span class="text-[9px] uppercase tracking-wider text-gray-500 dark:text-gray-400 block">Works</span>
                                                    <span class="text-sm font-mono font-bold text-gray-700 dark:text-gray-200">Remote</span>
                                                </div>
                                                <div class="about-stat-cell px-3 py-2.5 rounded-lg bg-gray-50 dark:bg-brand-950/80 border border-gray-200 dark:border-brand-700/50">
                                                    <span class="text-[9px] uppercase tracking-wider text-gray-500 dark:text-gray-400 block">Call Me When</span>
                                                    <span class="text-sm font-mono font-bold text-gray-700 dark:text-gray-200">It's Broken</span>
                                                </div>
                                            </div>
                                            {{-- Flavor text --}}
                                            <div class="mt-3 px-1 pt-3 border-t border-gray-200 dark:border-brand-700/50">
                                                <p class="text-[11px] italic text-gray-400 dark:text-gray-500 leading-relaxed text-center">"The one you call when the codebase is on fire and nobody else can untangle it."</p>
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>

                    {{-- Flip hint --}}
                    <div class="about-flip-hint flex items-center justify-center gap-2 mt-4 md:absolute md:bottom-[-36px] md:left-1/2 md:-translate-x-1/2 md:whitespace-nowrap">
                        <svg class="w-4 h-4 text-gray-600 dark:text-gray-300 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.042 21.672L13.684 16.6m0 0l-2.51 2.225.569-9.47 5.227 7.917-3.286-.672zM12 2.25V4.5m5.834.166l-1.591 1.591M20.25 10.5H18M7.757 14.743l-1.59 1.59M6 10.5H3.75m4.007-4.243l-1.59-1.59" />
                        </svg>
                        <span class="text-[12px] text-gray-600 dark:text-gray-300 font-medium tracking-wide">Click card to flip</span>
                        <svg class="w-3.5 h-3.5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12c0-1.232-.046-2.453-.138-3.662a4.006 4.006 0 00-3.7-3.7 48.678 48.678 0 00-7.324 0 4.006 4.006 0 00-3.7 3.7c-.017.22-.032.441-.046.662M19.5 12l3-3m-3 3l-3-3m-12 3c0 1.232.046 2.453.138 3.662a4.006 4.006 0 003.7 3.7 48.656 48.656 0 007.324 0 4.006 4.006 0 003.7-3.7c.017-.22.032-.441.046-.662M4.5 12l3 3m-3-3l-3 3" />
                        </svg>
                    </div>
                </div>

                {{-- Intro Text --}}
                <div class="flex-1 text-center md:text-left">
                    <div class="flex items-center gap-3 mb-4 justify-center md:justify-start">
                        <div class="font-mono text-sm text-gray-500 flex items-center gap-2">
                            <span class="text-brand-600">$</span>
                            <span>php artisan about:me</span>
                            <span class="animate-pulse text-gray-400 dark:text-brand-600 relative -top-px">▊</span>
                        </div>
                    </div>

                    <h1 class="text-3xl md:text-4xl font-extrabold mb-4 leading-tight text-gray-900 dark:text-white">
                        I've spent 15 years learning how to write code that my future self <span class="text-brand-600">won't hate.</span>
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400 text-base leading-relaxed max-w-xl mx-auto md:mx-0">
                        Web developer based in Florida. I build clean, maintainable applications with Laravel, talk about it on a podcast, and I'm putting together a YouTube channel. When I'm not coding, I'm being a dad, exploring theme parks, and pretending I'm going to get better at poker.
                    </p>

                    <div class="flex flex-wrap gap-4 justify-center md:justify-start mt-6">
                        <a href="{{ route('contact') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-600 hover:bg-brand-500 text-white text-sm font-semibold rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            Get in Touch
                        </a>
                        <a href="https://github.com/JeffreyDavidson" target="_blank" class="inline-flex items-center gap-2 px-5 py-2.5 border border-gray-200 dark:border-brand-700 hover:border-gray-400 dark:hover:border-gray-600 text-gray-900 dark:text-white text-sm font-semibold rounded-lg transition-colors">
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
                        <span class="w-8 h-8 rounded-lg bg-brand-600/10 flex items-center justify-center">
                            <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
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
                            Now I'm building content alongside code. <strong class="text-gray-900 dark:text-gray-200">Coffee with The Laravel Architect</strong> is me talking about the framework I love, and <strong class="text-gray-900 dark:text-gray-200">The Laravel Architect</strong> YouTube channel is bringing tutorials and live coding to the mix.
                        </p>
                    </div>
                </div>

                {{-- Timeline sidebar (vertical on mobile & large) --}}
                <div class="lg:w-80 flex-shrink-0 hidden lg:block">
                    <h2 class="text-2xl font-extrabold mb-8 flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg bg-brand-600/10 flex items-center justify-center">
                            <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                        Timeline
                    </h2>
                    <div class="space-y-6">
                        @foreach($timelineItems as $item)
                            <x-about.timeline-item :item="$item" />
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Horizontal timeline (md only) --}}
            <div class="hidden md:block lg:hidden mt-16">
                <h2 class="text-2xl font-extrabold mb-10 text-center text-gray-900 dark:text-white">Timeline</h2>
                <div class="relative">
                    {{-- Horizontal line --}}
                    <div class="absolute left-0 right-0 top-1/2 h-0.5 bg-gradient-to-r from-transparent via-brand-600/40 to-transparent"></div>

                    <div class="grid grid-cols-6 gap-2">
                        @foreach($timelineItems as $i => $item)
                            <div class="relative flex flex-col items-center {{ $i % 2 === 0 ? 'pt-0 pb-20' : 'pt-20 pb-0' }}">
                                {{-- Content above or below --}}
                                @if($i % 2 === 0)
                                    <div class="text-center mb-4">
                                        <span class="text-xs font-bold text-brand-600">{{ $item['year'] }}</span>
                                        <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 mt-1">{{ $item['title'] }}</p>
                                        <p class="text-[10px] text-gray-500 mt-0.5">{{ $item['desc'] }}</p>
                                    </div>
                                @endif

                                {{-- Dot --}}
                                <div class="w-3 h-3 rounded-full bg-brand-600 shadow-[0_0_10px_rgba(74,127,191,0.4)] z-10 flex-shrink-0"></div>

                                @if($i % 2 !== 0)
                                    <div class="text-center mt-4">
                                        <span class="text-xs font-bold text-brand-600">{{ $item['year'] }}</span>
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
                    <span class="w-8 h-8 rounded-lg bg-brand-600/10 flex items-center justify-center">
                        <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                    Timeline
                </h2>
                <div class="space-y-6">
                    @foreach($timelineItems as $item)
                        <x-about.timeline-item :item="$item" />
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- What I Believe In --}}
    <div class="border-t border-gray-200 dark:border-brand-700 bg-white dark:bg-transparent">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-20">
            <div class="text-center mb-14">
                <p class="text-xs font-semibold uppercase tracking-widest text-gray-600 mb-3">Core Values</p>
                <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 dark:text-white">What I Believe In</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <x-about.value-card title="Architecture Over Cleverness" description="Clean structure beats clever tricks every time. Code should be readable, predictable, and easy to change. If your future self can't understand it, it's not good code.">
                    <x-slot:icon>
                        <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </x-slot:icon>
                </x-about.value-card>
                <x-about.value-card title="Tests Are Not Optional" description="I run three test suites on every project: Feature, Integration, and Unit. Tests aren't overhead. They're how you ship with confidence and sleep at night.">
                    <x-slot:icon>
                        <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </x-slot:icon>
                </x-about.value-card>
                <x-about.value-card title="Teach What You Learn" description="The best way to solidify knowledge is to share it. Every blog post, podcast episode, and tutorial is me learning out loud, and hopefully making someone else's path easier.">
                    <x-slot:icon>
                        <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </x-slot:icon>
                </x-about.value-card>
                <x-about.value-card title="Family First" description="My daughter Viola is autistic and nonverbal, and being her dad has taught me more about patience, empathy, and what really matters than any codebase ever could." variant="accent">
                    <x-slot:icon>
                        <svg class="w-5 h-5 text-accent-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                    </x-slot:icon>
                </x-about.value-card>
                <x-about.value-card title="Build With Clarity" description="Good software should be understandable, maintainable, and useful. I care about architecture that makes the next change easier instead of showing off how clever the last one was." variant="accent">
                    <x-slot:icon>
                        <svg class="w-5 h-5 text-accent-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/></svg>
                    </x-slot:icon>
                </x-about.value-card>
                <x-about.value-card title="Ship, Don't Perfect" description="Done is better than perfect. I've learned more from shipping imperfect things and iterating than from any project I polished endlessly in private.">
                    <x-slot:icon>
                        <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </x-slot:icon>
                </x-about.value-card>
            </div>
        </div>
    </div>

    {{-- Fun Facts --}}
    <div class="border-t border-gray-200 dark:border-brand-700 bg-gray-50 dark:bg-transparent">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6 text-center">
                @foreach($facts as $fact)
                    <x-about.fact :icon="$fact['icon']" :label="$fact['label']" :value="$fact['value']" />
                @endforeach
            </div>
        </div>
    </div>

    {{-- Tech Stack --}}
    <div class="border-t border-gray-200 dark:border-brand-700 dot-grid-bg bg-white dark:bg-transparent">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-20">
            <div class="text-center mb-14">
                <p class="text-xs font-semibold uppercase tracking-widest text-gray-600 mb-3">Toolbox</p>
                <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 dark:text-white">What I Work With</h2>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($techStack as $tech)
                    <x-about.tech-card :tech="$tech" />
                @endforeach
            </div>
        </div>
    </div>

    {{-- CTA --}}
    <div class="relative border-t border-gray-200 dark:border-brand-700 overflow-hidden bg-gray-50 dark:bg-transparent">
        {{-- Floating orbs --}}
        <div class="absolute top-1/4 left-1/4 w-64 h-64 rounded-full opacity-[0.08] dark:opacity-[0.06] blur-[80px] bg-brand-600"></div>
        <div class="absolute bottom-1/4 right-1/4 w-48 h-48 rounded-full opacity-[0.06] dark:opacity-[0.06] blur-[80px] bg-accent-600"></div>

        <div class="relative max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-28 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-green-500/10 text-green-600 dark:text-green-400 text-xs font-bold uppercase tracking-widest mb-6 border border-green-500/20">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-500 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                </span>
                Available for Projects
            </div>
            <h2 class="text-3xl md:text-4xl font-extrabold mb-4">
                <span class="about-cta-shimmer">Want to work together?</span>
            </h2>
            <p class="text-gray-600 dark:text-gray-400 text-lg mb-8 max-w-xl mx-auto">I'm available for freelance Laravel development, consulting, and legacy modernization projects. Let's talk about what you're building.</p>
            <x-button href="{{ route('contact') }}" class="px-8 py-3.5 text-lg shadow-[0_0_30px_rgba(74,127,191,0.3)]">
                Contact Me
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </x-button>
        </div>
    </div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const container = document.querySelector('.about-card-flip-container');
    const card = document.querySelector('.about-card-flip');
    if (!container || !card) return;

    const maxTilt = 15;
    let flipCount = 0; // always increment (always rotate right)
    let isAnimating = false;

    // Listen for flip events from Alpine
    container.addEventListener('about-card-flip', (e) => {
        flipCount++;
        isAnimating = true;
        const targetY = flipCount * 180;
        card.style.transition = 'transform 0.8s cubic-bezier(0.16, 1, 0.3, 1)';
        card.style.transform = `rotateX(0deg) rotateY(${targetY}deg) scale(1)`;
        setTimeout(() => { isAnimating = false; }, 800);
    });

    container.addEventListener('mousemove', (e) => {
        if (isAnimating) return;

        const rect = container.getBoundingClientRect();
        const x = (e.clientX - rect.left) / rect.width;
        const y = (e.clientY - rect.top) / rect.height;

        const tiltX = (0.5 - y) * maxTilt;
        const tiltY = (x - 0.5) * maxTilt;
        const baseY = flipCount * 180;

        card.style.transition = 'transform 0.1s ease-out';
        card.style.transform = `rotateX(${tiltX}deg) rotateY(${baseY + tiltY}deg) scale(1.02)`;
    });

    container.addEventListener('mouseleave', () => {
        if (isAnimating) return;
        const baseY = flipCount * 180;
        card.style.transition = 'transform 0.6s cubic-bezier(0.16, 1, 0.3, 1)';
        card.style.transform = `rotateX(0deg) rotateY(${baseY}deg) scale(1)`;
    });
});
</script>
@endsection
