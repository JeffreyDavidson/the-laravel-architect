@extends('layouts.app')

@section('title', 'Uses')

@section('content')
    {{-- Hero --}}
    <x-hero-section>
        <div class="grid gap-6 md:grid-cols-[8rem_1fr] md:gap-10">
            <p class="font-mono text-xs uppercase tracking-[0.18em] text-brand-600">Toolkit / 05</p>
            <div>
                <h1 class="mb-4 text-4xl font-bold tracking-tight text-gray-900 dark:text-white md:text-6xl">The tools behind the work.</h1>
                <p class="text-lg leading-relaxed text-gray-600 dark:text-gray-400 md:text-xl">The hardware, software, and tools I use daily for development, content creation, and life. Inspired by <a href="https://uses.tech" target="_blank" rel="noopener noreferrer" class="text-brand-600 underline decoration-brand-600/50 underline-offset-4 hover:decoration-current focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-400">uses.tech</a>.</p>
                <p class="mt-5 font-mono text-xs uppercase tracking-wide text-gray-500">Last updated February 2026</p>
            </div>
        </div>
    </x-hero-section>

    <nav aria-label="Jump to uses section" class="border-b border-gray-200 bg-white dark:border-[#1e2a3a] dark:bg-[#0b1016] lg:hidden">
        <div class="mx-auto flex max-w-7xl gap-6 overflow-x-auto px-4 py-4 font-mono text-xs uppercase tracking-wide text-gray-600 sm:px-6 dark:text-gray-400">
            <a href="#hardware" class="whitespace-nowrap hover:text-brand-600">Hardware</a>
            <a href="#development" class="whitespace-nowrap hover:text-brand-600">Development</a>
            <a href="#content-creation" class="whitespace-nowrap hover:text-brand-600">Content</a>
            <a href="#productivity" class="whitespace-nowrap hover:text-brand-600">Productivity</a>
            <a href="#this-site" class="whitespace-nowrap hover:text-brand-600">This site</a>
        </div>
    </nav>

    {{-- Content --}}
    <div class="bg-gray-50 dark:bg-[#0b1016]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
            <div class="flex flex-col lg:flex-row gap-12">

                {{-- Main Content --}}
                <div class="flex-1 min-w-0">

                    {{-- Hardware --}}
                    <section id="hardware" class="mb-16 scroll-mt-24">
                        <div class="flex items-center gap-3 mb-8">
                            <x-public.section-icon><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg></x-public.section-icon>
                            <h2 class="text-2xl font-extrabold text-gray-900 dark:text-white">Hardware</h2>
                        </div>
                        <div class="space-y-3">
                            @foreach([
                                ['icon' => '💻', 'name' => 'MacBook Pro 16" (Nov 2024)', 'desc' => 'Apple M4 Max, 48GB RAM. The daily driver for everything: development, content creation, and life.', 'tag' => 'Laptop'],
                                ['icon' => '🖥️', 'name' => 'LG 39GS95QE', 'desc' => '39" ultrawide OLED gaming monitor. Gorgeous colors, plenty of real estate for code + browser side by side.', 'tag' => 'Monitor', 'url' => 'https://www.lg.com/us/monitors/lg-39gs95qe-b-gaming-monitor'],
                                ['icon' => '⌨️', 'name' => 'Apple Magic Keyboard', 'desc' => 'Simple, reliable, and matches the ecosystem. No mechanical keyboard phase. Yet.', 'tag' => 'Keyboard'],
                                ['icon' => '🖱️', 'name' => 'Apple Magic Trackpad', 'desc' => 'Gestures are too good to give up. The trackpad stays.', 'tag' => 'Trackpad'],
                                ['icon' => '🔌', 'name' => 'CalDigit TS3 Plus', 'desc' => 'Thunderbolt dock. One cable to rule them all. Monitor, peripherals, power, everything.', 'tag' => 'Dock', 'url' => 'https://www.caldigit.com/ts3-plus/'],
                                ['icon' => '🔊', 'name' => 'Kanto YU2', 'desc' => 'Compact powered desktop speakers. Big sound from a small footprint. Perfect for the desk setup.', 'tag' => 'Speakers', 'url' => 'https://www.kantoaudio.com/powered-speakers/yu2/'],
                                ['icon' => '🪑', 'name' => 'Secretlab Chair', 'desc' => 'Comfortable for long coding sessions. Worth the investment.', 'tag' => 'Chair'],
                                ['icon' => '🪵', 'name' => 'Fully Jarvis 72×30', 'desc' => 'Black bamboo standing desk. Sit-stand with plenty of room for the ultrawide and all the gear.', 'tag' => 'Desk', 'url' => 'https://www.fully.com/standing-desks/jarvis.html'],
                            ] as $item)
                            <x-uses.item :item="$item" />
                            @endforeach
                        </div>
                    </section>

                    {{-- Development --}}
                    <section id="development" class="mb-16 scroll-mt-24">
                        <div class="flex items-center gap-3 mb-8">
                            <x-public.section-icon><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg></x-public.section-icon>
                            <h2 class="text-2xl font-extrabold text-gray-900 dark:text-white">Development</h2>
                        </div>
                        <div class="space-y-3">
                            @foreach([
                                ['icon' => '📝', 'name' => 'Visual Studio Code', 'desc' => 'My editor of choice. Fast, extensible, and the ecosystem of extensions is unbeatable.', 'tag' => 'Editor', 'url' => 'https://code.visualstudio.com'],
                                ['icon' => '🐚', 'name' => 'Warp', 'desc' => 'Modern terminal with AI built in. Getting a little bloated though. Eyeing Ghostty as a leaner alternative.', 'tag' => 'Terminal', 'url' => 'https://www.warp.dev'],
                                ['icon' => '🦙', 'name' => 'Laravel Herd', 'desc' => 'Local development environment. Zero-config PHP, nginx, and dnsmasq on macOS.', 'tag' => 'Local Dev', 'url' => 'https://herd.laravel.com'],
                                ['icon' => '🔨', 'name' => 'Laravel Forge', 'desc' => 'Server management and deployment. Push to main and it\'s live.', 'tag' => 'Hosting', 'url' => 'https://forge.laravel.com'],
                                ['icon' => '🐙', 'name' => 'GitHub', 'desc' => 'Version control, CI/CD, and open source home.', 'tag' => 'Git', 'url' => 'https://github.com/JeffreyDavidson'],
                                ['icon' => '🗄️', 'name' => 'TablePlus', 'desc' => 'Database GUI. Clean, fast, and works beautifully with MySQL, SQLite, and Redis.', 'tag' => 'Database', 'url' => 'https://tableplus.com'],
                                ['icon' => '🦊', 'name' => 'Firefox', 'desc' => 'Primary browser for development and daily use. Looking at Arc for something fresh.', 'tag' => 'Browser', 'url' => 'https://www.mozilla.org/firefox/'],
                                ['icon' => '🔦', 'name' => 'Ray', 'desc' => 'By Spatie. A beautiful debugging tool that replaced dd() in my workflow.', 'tag' => 'Debugging', 'url' => 'https://myray.app'],
                                ['icon' => '🧪', 'name' => 'Pest', 'desc' => 'Testing framework for PHP. Elegant syntax, powerful assertions. Three suites: Feature, Integration, Unit.', 'tag' => 'Testing', 'url' => 'https://pestphp.com'],
                            ] as $item)
                            <x-uses.item :item="$item" />
                            @endforeach
                        </div>
                    </section>

                    {{-- Content Creation --}}
                    <section id="content-creation" class="mb-16 scroll-mt-24">
                        <div class="flex items-center gap-3 mb-8">
                            <x-public.section-icon variant="accent"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg></x-public.section-icon>
                            <h2 class="text-2xl font-extrabold text-gray-900 dark:text-white">Content Creation</h2>
                        </div>
                        <div class="space-y-3">
                            @foreach([
                                ['icon' => '🎙️', 'name' => 'Shure SM7B', 'desc' => 'The industry standard broadcast mic. Warm, rich sound that makes everything sound professional.', 'tag' => 'Microphone', 'url' => 'https://www.shure.com/en-US/products/microphones/sm/sm7b'],
                                ['icon' => '🎛️', 'name' => 'RØDECaster Pro', 'desc' => 'All-in-one podcast production studio. Handles audio processing, mixing, and recording in one box.', 'tag' => 'Audio', 'url' => 'https://rode.com/en/interfaces-mixers/rodecaster-series/rodecaster-pro'],
                                ['icon' => '📷', 'name' => 'Sony ZV-E10', 'desc' => 'Mirrorless camera made for content creators. Great video quality, compact body, interchangeable lenses.', 'tag' => 'Camera', 'url' => 'https://electronics.sony.com/imaging/interchangeable-lens-cameras/aps-c/p/ilczve10-b'],
                                ['icon' => '💡', 'name' => 'Elgato Key Light', 'desc' => 'Edge-lit LED panel. App-controlled brightness and color temperature. Clean, even lighting for video.', 'tag' => 'Lighting', 'url' => 'https://www.elgato.com/us/en/p/key-light'],
                                ['icon' => '🎮', 'name' => 'Elgato Stream Deck XL', 'desc' => '32 programmable LCD keys. Scene switching, shortcuts, and macros for streaming and productivity.', 'tag' => 'Control', 'url' => 'https://www.elgato.com/us/en/p/stream-deck-xl'],
                                ['icon' => '🕹️', 'name' => 'Elgato Stream Deck MK.2', 'desc' => '15-key companion to the XL. Extra controls for when one deck isn\'t enough.', 'tag' => 'Control', 'url' => 'https://www.elgato.com/us/en/p/stream-deck-mk2-black'],
                            ] as $item)
                            <x-uses.item :item="$item" />
                            @endforeach
                        </div>
                    </section>

                    {{-- Productivity --}}
                    <section id="productivity" class="mb-16 scroll-mt-24">
                        <div class="flex items-center gap-3 mb-8">
                            <x-public.section-icon><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg></x-public.section-icon>
                            <h2 class="text-2xl font-extrabold text-gray-900 dark:text-white">Productivity</h2>
                        </div>
                        <div class="space-y-3">
                            @foreach([
                                ['icon' => '📓', 'name' => 'Notion', 'desc' => 'Everything lives here. Family organization, project planning, content calendars, and notes.', 'tag' => 'Notes', 'url' => 'https://www.notion.so'],
                                ['icon' => '💬', 'name' => 'Slack', 'desc' => 'Work communication and Laravel community channels.', 'tag' => 'Chat', 'url' => 'https://slack.com'],
                                ['icon' => '🎮', 'name' => 'Discord', 'desc' => 'Dev communities, podcast listeners, and gaming.', 'tag' => 'Community', 'url' => 'https://discord.com'],
                            ] as $item)
                            <x-uses.item :item="$item" />
                            @endforeach
                        </div>
                    </section>

                    {{-- This Site --}}
                    <section id="this-site" class="scroll-mt-24">
                        <div class="flex items-center gap-3 mb-8">
                            <x-public.section-icon><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg></x-public.section-icon>
                            <h2 class="text-2xl font-extrabold text-gray-900 dark:text-white">This Site Is Built With</h2>
                        </div>
                        <div class="grid grid-cols-2 border-y border-gray-200 dark:border-[#1e2a3a] sm:grid-cols-3">
                            @foreach([
                                ['icon' => '🐘', 'name' => 'Laravel '.config('public-site.technology.laravel'), 'desc' => 'Framework'],
                                ['icon' => '🛡️', 'name' => 'Filament '.config('public-site.technology.filament'), 'desc' => 'Admin panel'],
                                ['icon' => '🎨', 'name' => 'Tailwind CSS', 'desc' => 'Styling'],
                                ['icon' => '📄', 'name' => 'Blade', 'desc' => 'Templates'],
                                ['icon' => '💾', 'name' => 'SQLite', 'desc' => 'Database'],
                                ['icon' => '🧪', 'name' => 'Pest', 'desc' => 'Testing'],
                                ['icon' => '🔨', 'name' => 'Laravel Forge', 'desc' => 'Deployment'],
                                ['icon' => '🖼️', 'name' => 'Intervention Image', 'desc' => 'OG images'],
                                ['icon' => '✨', 'name' => 'Prism.js', 'desc' => 'Syntax highlighting'],
                                ['icon' => '📧', 'name' => 'Resend', 'desc' => 'Email'],
                                ['icon' => '⛰️', 'name' => 'Alpine.js', 'desc' => 'Interactivity'],
                            ] as $tech)
                            <x-uses.site-tech :tech="$tech" />
                            @endforeach
                        </div>
                    </section>
                </div>

                {{-- Sidebar --}}
                <div class="hidden flex-shrink-0 lg:block lg:w-72">
                    <div class="lg:sticky lg:top-24 space-y-6">
                        {{-- Quick nav --}}
                        <section class="border-t border-gray-200 pt-5 dark:border-[#1e2a3a]">
                            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-4">Jump To</h3>
                            <nav aria-label="Jump to uses section" class="space-y-2">
                                <a href="#hardware" class="block text-sm text-gray-600 transition-colors hover:text-brand-600 dark:text-gray-400">Hardware</a>
                                <a href="#development" class="block text-sm text-gray-600 transition-colors hover:text-brand-600 dark:text-gray-400">Development</a>
                                <a href="#content-creation" class="block text-sm text-gray-600 transition-colors hover:text-brand-600 dark:text-gray-400">Content Creation</a>
                                <a href="#productivity" class="block text-sm text-gray-600 transition-colors hover:text-brand-600 dark:text-gray-400">Productivity</a>
                                <a href="#this-site" class="block text-sm text-gray-600 transition-colors hover:text-brand-600 dark:text-gray-400">This Site</a>
                            </nav>
                        </section>

                        {{-- uses.tech --}}
                        <section class="border-t border-gray-200 pt-5 dark:border-[#1e2a3a]">
                            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-3">Inspired By</h3>
                            <a href="https://uses.tech" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 text-sm text-brand-600 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-400">
                                uses.tech
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                            <p class="text-xs text-gray-500 mt-1.5">A directory of developer /uses pages.</p>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
