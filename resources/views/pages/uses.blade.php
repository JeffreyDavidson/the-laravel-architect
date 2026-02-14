@extends('layouts.app')

@section('title', 'Uses')

@section('content')
<style>
    .noise-overlay { position: relative; }
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
    .dot-grid-bg { position: relative; }
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
    .dot-grid-bg > * { position: relative; z-index: 1; }
    .uses-item { transition: all 0.2s ease; }
    .uses-item:hover {
        background: rgba(74, 127, 191, 0.03);
        border-color: #2a3a4a;
        transform: translateX(4px);
    }
</style>

    {{-- Hero --}}
    <div class="noise-overlay border-b border-[#1e2a3a]">
        <div class="absolute inset-0 bg-gradient-to-br from-[#4A7FBF]/5 via-transparent to-[#9D5175]/5"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-28">
            <div class="max-w-3xl">
                <h1 class="text-4xl md:text-6xl font-extrabold mb-4 tracking-tight">/uses</h1>
                <p class="text-gray-400 text-lg md:text-xl leading-relaxed">The hardware, software, and tools I use daily for development, content creation, and life. Inspired by <a href="https://uses.tech" target="_blank" class="text-[#4A7FBF] hover:underline">uses.tech</a>.</p>
                <p class="text-gray-500 text-sm mt-4">Last updated: February 2026</p>
            </div>
        </div>
    </div>

    {{-- Content --}}
    <div class="dot-grid-bg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
            <div class="flex flex-col lg:flex-row gap-12">

                {{-- Main Content --}}
                <div class="flex-1 min-w-0">

                    {{-- Hardware --}}
                    <section id="hardware" class="mb-16 scroll-mt-24">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="w-10 h-10 rounded-xl bg-[#4A7FBF]/10 flex items-center justify-center">
                                <svg class="w-5 h-5 text-[#4A7FBF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                            <h2 class="text-2xl font-extrabold">Hardware</h2>
                        </div>
                        <div class="space-y-3">
                            @foreach([
                                ['icon' => '💻', 'name' => 'MacBook Pro 16" (Nov 2024)', 'desc' => 'Apple M4 Max, 48GB RAM. The daily driver for everything — development, content creation, and life.', 'tag' => 'Laptop'],
                                ['icon' => '🖥️', 'name' => 'LG 39GS95QE', 'desc' => '39" ultrawide OLED gaming monitor. Gorgeous colors, plenty of real estate for code + browser side by side.', 'tag' => 'Monitor', 'url' => 'https://www.lg.com/us/monitors/lg-39gs95qe-b-gaming-monitor'],
                                ['icon' => '⌨️', 'name' => 'Apple Magic Keyboard', 'desc' => 'Simple, reliable, and matches the ecosystem. No mechanical keyboard phase — yet.', 'tag' => 'Keyboard'],
                                ['icon' => '🖱️', 'name' => 'Apple Magic Trackpad', 'desc' => 'Gestures are too good to give up. The trackpad stays.', 'tag' => 'Trackpad'],
                                ['icon' => '🔌', 'name' => 'CalDigit TS3 Plus', 'desc' => 'Thunderbolt dock. One cable to rule them all — monitor, peripherals, power, everything.', 'tag' => 'Dock', 'url' => 'https://www.caldigit.com/ts3-plus/'],
                                ['icon' => '🔊', 'name' => 'Kanto YU2', 'desc' => 'Compact powered desktop speakers. Big sound from a small footprint — perfect for the desk setup.', 'tag' => 'Speakers', 'url' => 'https://www.kantoaudio.com/powered-speakers/yu2/'],
                                ['icon' => '🪑', 'name' => 'Secretlab Chair', 'desc' => 'Comfortable for long coding sessions. Worth the investment.', 'tag' => 'Chair'],
                                ['icon' => '🪵', 'name' => 'Fully Jarvis 72×30', 'desc' => 'Black bamboo standing desk. Sit-stand with plenty of room for the ultrawide and all the gear.', 'tag' => 'Desk', 'url' => 'https://www.fully.com/standing-desks/jarvis.html'],
                            ] as $item)
                            @if(isset($item['url']))
                            <a href="{{ $item['url'] }}" target="_blank" class="uses-item group flex items-start gap-4 p-4 rounded-xl border border-[#1e2a3a]">
                                <span class="text-xl flex-shrink-0 mt-0.5">{{ $item['icon'] }}</span>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-semibold text-sm group-hover:text-[#4A7FBF] transition-colors">{{ $item['name'] }}</h3>
                                        <svg class="w-3 h-3 text-gray-600 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $item['desc'] }}</p>
                                </div>
                                <span class="text-[10px] font-mono text-gray-600 flex-shrink-0 mt-1">{{ $item['tag'] }}</span>
                            </a>
                            @else
                            <div class="uses-item flex items-start gap-4 p-4 rounded-xl border border-[#1e2a3a]">
                                <span class="text-xl flex-shrink-0 mt-0.5">{{ $item['icon'] }}</span>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold text-sm">{{ $item['name'] }}</h3>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $item['desc'] }}</p>
                                </div>
                                <span class="text-[10px] font-mono text-gray-600 flex-shrink-0 mt-1">{{ $item['tag'] }}</span>
                            </div>
                            @endif
                            @endforeach
                        </div>
                    </section>

                    {{-- Development --}}
                    <section id="development" class="mb-16 scroll-mt-24">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="w-10 h-10 rounded-xl bg-[#4A7FBF]/10 flex items-center justify-center">
                                <svg class="w-5 h-5 text-[#4A7FBF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                            </div>
                            <h2 class="text-2xl font-extrabold">Development</h2>
                        </div>
                        <div class="space-y-3">
                            @foreach([
                                ['icon' => '📝', 'name' => 'Visual Studio Code', 'desc' => 'My editor of choice. Fast, extensible, and the ecosystem of extensions is unbeatable.', 'tag' => 'Editor', 'url' => 'https://code.visualstudio.com'],
                                ['icon' => '🐚', 'name' => 'Warp', 'desc' => 'Modern terminal with AI built in. Getting a little bloated though — eyeing Ghostty as a leaner alternative.', 'tag' => 'Terminal', 'url' => 'https://www.warp.dev'],
                                ['icon' => '🦙', 'name' => 'Laravel Herd', 'desc' => 'Local development environment. Zero-config PHP, nginx, and dnsmasq on macOS.', 'tag' => 'Local Dev', 'url' => 'https://herd.laravel.com'],
                                ['icon' => '🔨', 'name' => 'Laravel Forge', 'desc' => 'Server management and deployment. Push to main and it\'s live.', 'tag' => 'Hosting', 'url' => 'https://forge.laravel.com'],
                                ['icon' => '🐙', 'name' => 'GitHub', 'desc' => 'Version control, CI/CD, and open source home.', 'tag' => 'Git', 'url' => 'https://github.com/JeffreyDavidson'],
                                ['icon' => '🗄️', 'name' => 'TablePlus', 'desc' => 'Database GUI. Clean, fast, and works beautifully with MySQL, SQLite, and Redis.', 'tag' => 'Database', 'url' => 'https://tableplus.com'],
                                ['icon' => '🦊', 'name' => 'Firefox', 'desc' => 'Primary browser for development and daily use. Looking at Arc for something fresh.', 'tag' => 'Browser', 'url' => 'https://www.mozilla.org/firefox/'],
                                ['icon' => '🔦', 'name' => 'Ray', 'desc' => 'By Spatie. A beautiful debugging tool that replaced dd() in my workflow.', 'tag' => 'Debugging', 'url' => 'https://myray.app'],
                                ['icon' => '🧪', 'name' => 'Pest', 'desc' => 'Testing framework for PHP. Elegant syntax, powerful assertions. Three suites: Feature, Integration, Unit.', 'tag' => 'Testing', 'url' => 'https://pestphp.com'],
                            ] as $item)
                            <a href="{{ $item['url'] }}" target="_blank" class="uses-item group flex items-start gap-4 p-4 rounded-xl border border-[#1e2a3a]">
                                <span class="text-xl flex-shrink-0 mt-0.5">{{ $item['icon'] }}</span>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-semibold text-sm group-hover:text-[#4A7FBF] transition-colors">{{ $item['name'] }}</h3>
                                        <svg class="w-3 h-3 text-gray-600 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $item['desc'] }}</p>
                                </div>
                                <span class="text-[10px] font-mono text-gray-600 flex-shrink-0 mt-1">{{ $item['tag'] }}</span>
                            </a>
                            @endforeach
                        </div>
                    </section>

                    {{-- Content Creation --}}
                    <section id="content-creation" class="mb-16 scroll-mt-24">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="w-10 h-10 rounded-xl bg-[#9D5175]/10 flex items-center justify-center">
                                <svg class="w-5 h-5 text-[#9D5175]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            </div>
                            <h2 class="text-2xl font-extrabold">Content Creation</h2>
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
                            <a href="{{ $item['url'] }}" target="_blank" class="uses-item group flex items-start gap-4 p-4 rounded-xl border border-[#1e2a3a]">
                                <span class="text-xl flex-shrink-0 mt-0.5">{{ $item['icon'] }}</span>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-semibold text-sm group-hover:text-[#4A7FBF] transition-colors">{{ $item['name'] }}</h3>
                                        <svg class="w-3 h-3 text-gray-600 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $item['desc'] }}</p>
                                </div>
                                <span class="text-[10px] font-mono text-gray-600 flex-shrink-0 mt-1">{{ $item['tag'] }}</span>
                            </a>
                            @endforeach
                        </div>
                    </section>

                    {{-- Productivity --}}
                    <section id="productivity" class="mb-16 scroll-mt-24">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="w-10 h-10 rounded-xl bg-[#4A7FBF]/10 flex items-center justify-center">
                                <svg class="w-5 h-5 text-[#4A7FBF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                            </div>
                            <h2 class="text-2xl font-extrabold">Productivity</h2>
                        </div>
                        <div class="space-y-3">
                            @foreach([
                                ['icon' => '📓', 'name' => 'Notion', 'desc' => 'Everything lives here. Family organization, project planning, content calendars, and notes.', 'tag' => 'Notes', 'url' => 'https://www.notion.so'],
                                ['icon' => '💬', 'name' => 'Slack', 'desc' => 'Work communication and Laravel community channels.', 'tag' => 'Chat', 'url' => 'https://slack.com'],
                                ['icon' => '🎮', 'name' => 'Discord', 'desc' => 'Dev communities, podcast listeners, and gaming.', 'tag' => 'Community', 'url' => 'https://discord.com'],
                            ] as $item)
                            <a href="{{ $item['url'] }}" target="_blank" class="uses-item group flex items-start gap-4 p-4 rounded-xl border border-[#1e2a3a]">
                                <span class="text-xl flex-shrink-0 mt-0.5">{{ $item['icon'] }}</span>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-semibold text-sm group-hover:text-[#4A7FBF] transition-colors">{{ $item['name'] }}</h3>
                                        <svg class="w-3 h-3 text-gray-600 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $item['desc'] }}</p>
                                </div>
                                <span class="text-[10px] font-mono text-gray-600 flex-shrink-0 mt-1">{{ $item['tag'] }}</span>
                            </a>
                            @endforeach
                        </div>
                    </section>

                    {{-- This Site --}}
                    <section id="this-site" class="scroll-mt-24">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="w-10 h-10 rounded-xl bg-[#4A7FBF]/10 flex items-center justify-center">
                                <svg class="w-5 h-5 text-[#4A7FBF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                            </div>
                            <h2 class="text-2xl font-extrabold">This Site Is Built With</h2>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            @foreach([
                                ['icon' => '🐘', 'name' => 'Laravel 12', 'desc' => 'Framework'],
                                ['icon' => '🛡️', 'name' => 'Filament 5', 'desc' => 'Admin panel'],
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
                            <div class="p-4 rounded-xl border border-[#1e2a3a] bg-[#0D1117]/50">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-base">{{ $tech['icon'] }}</span>
                                    <p class="font-semibold text-sm">{{ $tech['name'] }}</p>
                                </div>
                                <p class="text-[11px] text-gray-500">{{ $tech['desc'] }}</p>
                            </div>
                            @endforeach
                        </div>
                    </section>
                </div>

                {{-- Sidebar --}}
                <div class="lg:w-72 flex-shrink-0">
                    <div class="lg:sticky lg:top-24 space-y-6">
                        {{-- Quick nav --}}
                        <div class="p-5 rounded-2xl border border-[#1e2a3a] bg-[#0D1117]">
                            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-4">Jump To</h3>
                            <nav class="space-y-2">
                                <a href="#hardware" class="block text-sm text-gray-400 hover:text-[#4A7FBF] transition-colors">🖥️ Hardware</a>
                                <a href="#development" class="block text-sm text-gray-400 hover:text-[#4A7FBF] transition-colors">💻 Development</a>
                                <a href="#content-creation" class="block text-sm text-gray-400 hover:text-[#4A7FBF] transition-colors">🎬 Content Creation</a>
                                <a href="#productivity" class="block text-sm text-gray-400 hover:text-[#4A7FBF] transition-colors">📋 Productivity</a>
                                <a href="#this-site" class="block text-sm text-gray-400 hover:text-[#4A7FBF] transition-colors">🧪 This Site</a>
                            </nav>
                        </div>

                        {{-- uses.tech --}}
                        <div class="p-5 rounded-2xl border border-[#1e2a3a] bg-[#0D1117]">
                            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-3">Inspired By</h3>
                            <a href="https://uses.tech" target="_blank" class="inline-flex items-center gap-1.5 text-sm text-[#4A7FBF] hover:underline">
                                uses.tech
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                            <p class="text-xs text-gray-500 mt-1.5">A directory of developer /uses pages.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
