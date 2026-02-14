@extends('layouts.app')

@section('title', 'Uses')

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
    .uses-item {
        transition: all 0.2s ease;
    }
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

    {{-- Desk Photo Placeholder --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="rounded-2xl border border-dashed border-[#1e2a3a] bg-[#0D1117]/50 p-12 text-center">
            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-[#4A7FBF]/10 flex items-center justify-center">
                <svg class="w-8 h-8 text-[#4A7FBF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <p class="text-gray-400 font-semibold mb-1">Desk photo coming soon</p>
            <p class="text-gray-600 text-sm">Currently upgrading my home office. The desk tour drops when it's ready.</p>
        </div>
    </div>

    {{-- Content --}}
    <div class="dot-grid-bg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
            <div class="flex flex-col lg:flex-row gap-12">

                {{-- Main Content --}}
                <div class="flex-1 min-w-0">

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
                                ['icon' => '🧠', 'name' => 'PhpStorm', 'desc' => 'Primary IDE. The refactoring tools and deep Laravel/PHP understanding are unmatched.', 'tag' => 'Editor', 'url' => 'https://www.jetbrains.com/phpstorm/'],
                                ['icon' => '🦙', 'name' => 'Laravel Herd', 'desc' => 'Local development environment. Zero-config PHP, nginx, and dnsmasq.', 'tag' => 'Local Dev', 'url' => 'https://herd.laravel.com'],
                                ['icon' => '🔨', 'name' => 'Laravel Forge', 'desc' => 'Server management and deployment. Push to main and it\'s live.', 'tag' => 'Hosting', 'url' => 'https://forge.laravel.com'],
                                ['icon' => '🐙', 'name' => 'GitHub', 'desc' => 'Version control, CI/CD, and open source home.', 'tag' => 'Git', 'url' => 'https://github.com/JeffreyDavidson'],
                                ['icon' => '🗄️', 'name' => 'TablePlus', 'desc' => 'Database GUI. Clean interface for MySQL, SQLite, and Redis.', 'tag' => 'Database', 'url' => 'https://tableplus.com'],
                                ['icon' => '🦊', 'name' => 'Firefox', 'desc' => 'Primary browser for development and daily use.', 'tag' => 'Browser', 'url' => 'https://www.mozilla.org/firefox/'],
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
                                ['icon' => '💻', 'name' => 'MacBook Pro', 'desc' => 'My daily driver for everything — development, content creation, and life.', 'tag' => 'Laptop'],
                                ['icon' => '📷', 'name' => 'Sony Alpha Camera', 'desc' => 'For photography and video content. Finalizing which model — eyeing the a6700 and a7C II.', 'tag' => 'Camera'],
                                ['icon' => '🎧', 'name' => 'Headphones', 'desc' => 'TBD — researching options for podcasting and daily use.', 'tag' => 'Audio'],
                                ['icon' => '🖥️', 'name' => 'Monitor', 'desc' => 'TBD — upgrading to a proper external display for the home office.', 'tag' => 'Display'],
                                ['icon' => '🪑', 'name' => 'Desk & Chair', 'desc' => 'Currently upgrading the home office. Full desk tour coming when it\'s done.', 'tag' => 'Office'],
                            ] as $item)
                            <div class="uses-item flex items-start gap-4 p-4 rounded-xl border border-[#1e2a3a]">
                                <span class="text-xl flex-shrink-0 mt-0.5">{{ $item['icon'] }}</span>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold text-sm">{{ $item['name'] }}</h3>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $item['desc'] }}</p>
                                </div>
                                <span class="text-[10px] font-mono text-gray-600 flex-shrink-0 mt-1">{{ $item['tag'] }}</span>
                            </div>
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
                                ['icon' => '🎬', 'name' => 'OBS Studio', 'desc' => 'Screen recording and streaming. Free, open source, and endlessly customizable.', 'tag' => 'Recording', 'url' => 'https://obsproject.com'],
                                ['icon' => '✂️', 'name' => 'DaVinci Resolve', 'desc' => 'Video editing. The free version is absurdly powerful for YouTube content.', 'tag' => 'Editing', 'url' => 'https://www.blackmagicdesign.com/products/davinciresolve'],
                                ['icon' => '🎨', 'name' => 'Canva', 'desc' => 'Thumbnails, social graphics, and quick design work. Not a designer, but Canva helps me fake it.', 'tag' => 'Design', 'url' => 'https://www.canva.com'],
                                ['icon' => '🎙️', 'name' => 'Audacity', 'desc' => 'Audio editing for podcast episodes. Simple, effective, and free.', 'tag' => 'Audio', 'url' => 'https://www.audacityteam.org'],
                                ['icon' => '🖼️', 'name' => 'Figma', 'desc' => 'When I need to think visually before building. Wireframes and quick mockups.', 'tag' => 'Design', 'url' => 'https://www.figma.com'],
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
                                ['icon' => '📧', 'name' => 'Spark', 'desc' => 'Email client by Readdle. Smart inbox and send-later keep me sane.', 'tag' => 'Email', 'url' => 'https://sparkmailapp.com'],
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
                                <a href="#development" class="block text-sm text-gray-400 hover:text-[#4A7FBF] transition-colors">💻 Development</a>
                                <a href="#hardware" class="block text-sm text-gray-400 hover:text-[#4A7FBF] transition-colors">🖥️ Hardware</a>
                                <a href="#content-creation" class="block text-sm text-gray-400 hover:text-[#4A7FBF] transition-colors">🎬 Content Creation</a>
                                <a href="#productivity" class="block text-sm text-gray-400 hover:text-[#4A7FBF] transition-colors">📋 Productivity</a>
                                <a href="#this-site" class="block text-sm text-gray-400 hover:text-[#4A7FBF] transition-colors">🧪 This Site</a>
                            </nav>
                        </div>

                        {{-- Note --}}
                        <div class="p-5 rounded-2xl border border-[#1e2a3a] bg-[#0D1117]">
                            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-3">🚧 Work in Progress</h3>
                            <p class="text-xs text-gray-500 leading-relaxed">I'm still building out my home office and content creation setup. Hardware section will get fleshed out as I finalize gear. Check back!</p>
                        </div>

                        {{-- uses.tech --}}
                        <div class="p-5 rounded-2xl border border-[#1e2a3a] bg-[#0D1117]">
                            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-3">Inspired By</h3>
                            <a href="https://uses.tech" target="_blank" class="inline-flex items-center gap-1.5 text-sm text-[#4A7FBF] hover:underline">
                                uses.tech
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                            <p class="text-xs text-gray-500 mt-1.5">A directory of developer /uses pages. Submit yours once you're happy with it!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
