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

                    {{-- Development --}}
                    <section class="mb-16">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="w-10 h-10 rounded-xl bg-[#4A7FBF]/10 flex items-center justify-center">
                                <svg class="w-5 h-5 text-[#4A7FBF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                            </div>
                            <h2 class="text-2xl font-extrabold">Development</h2>
                        </div>
                        <div class="space-y-3">
                            <div class="uses-item p-4 rounded-xl border border-[#1e2a3a]">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h3 class="font-semibold text-sm">PhpStorm</h3>
                                        <p class="text-xs text-gray-500 mt-0.5">Primary IDE. The refactoring tools and deep Laravel/PHP understanding are unmatched.</p>
                                    </div>
                                    <span class="text-[10px] font-mono text-gray-600 flex-shrink-0 mt-0.5">Editor</span>
                                </div>
                            </div>
                            <div class="uses-item p-4 rounded-xl border border-[#1e2a3a]">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h3 class="font-semibold text-sm">Laravel Herd</h3>
                                        <p class="text-xs text-gray-500 mt-0.5">Local development environment. Zero-config PHP, nginx, and dnsmasq.</p>
                                    </div>
                                    <span class="text-[10px] font-mono text-gray-600 flex-shrink-0 mt-0.5">Local Dev</span>
                                </div>
                            </div>
                            <div class="uses-item p-4 rounded-xl border border-[#1e2a3a]">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h3 class="font-semibold text-sm">Laravel Forge</h3>
                                        <p class="text-xs text-gray-500 mt-0.5">Server management and deployment. Push to main and it's live.</p>
                                    </div>
                                    <span class="text-[10px] font-mono text-gray-600 flex-shrink-0 mt-0.5">Hosting</span>
                                </div>
                            </div>
                            <div class="uses-item p-4 rounded-xl border border-[#1e2a3a]">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h3 class="font-semibold text-sm">GitHub</h3>
                                        <p class="text-xs text-gray-500 mt-0.5">Version control, CI/CD, and open source home.</p>
                                    </div>
                                    <span class="text-[10px] font-mono text-gray-600 flex-shrink-0 mt-0.5">Git</span>
                                </div>
                            </div>
                            <div class="uses-item p-4 rounded-xl border border-[#1e2a3a]">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h3 class="font-semibold text-sm">TablePlus</h3>
                                        <p class="text-xs text-gray-500 mt-0.5">Database GUI. Clean interface for MySQL, SQLite, and Redis.</p>
                                    </div>
                                    <span class="text-[10px] font-mono text-gray-600 flex-shrink-0 mt-0.5">Database</span>
                                </div>
                            </div>
                            <div class="uses-item p-4 rounded-xl border border-[#1e2a3a]">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h3 class="font-semibold text-sm">Firefox</h3>
                                        <p class="text-xs text-gray-500 mt-0.5">Primary browser for development and daily use.</p>
                                    </div>
                                    <span class="text-[10px] font-mono text-gray-600 flex-shrink-0 mt-0.5">Browser</span>
                                </div>
                            </div>
                            <div class="uses-item p-4 rounded-xl border border-[#1e2a3a]">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h3 class="font-semibold text-sm">Ray</h3>
                                        <p class="text-xs text-gray-500 mt-0.5">By Spatie. A beautiful debugging tool that replaced dd() in my workflow.</p>
                                    </div>
                                    <span class="text-[10px] font-mono text-gray-600 flex-shrink-0 mt-0.5">Debugging</span>
                                </div>
                            </div>
                            <div class="uses-item p-4 rounded-xl border border-[#1e2a3a]">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h3 class="font-semibold text-sm">Pest</h3>
                                        <p class="text-xs text-gray-500 mt-0.5">Testing framework for PHP. Elegant syntax, powerful assertions. Three suites: Feature, Integration, Unit.</p>
                                    </div>
                                    <span class="text-[10px] font-mono text-gray-600 flex-shrink-0 mt-0.5">Testing</span>
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- Hardware --}}
                    <section class="mb-16">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="w-10 h-10 rounded-xl bg-[#4A7FBF]/10 flex items-center justify-center">
                                <svg class="w-5 h-5 text-[#4A7FBF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                            <h2 class="text-2xl font-extrabold">Hardware</h2>
                        </div>
                        <div class="space-y-3">
                            <div class="uses-item p-4 rounded-xl border border-[#1e2a3a]">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h3 class="font-semibold text-sm">MacBook Pro</h3>
                                        <p class="text-xs text-gray-500 mt-0.5">My daily driver for everything — development, content creation, and life.</p>
                                    </div>
                                    <span class="text-[10px] font-mono text-gray-600 flex-shrink-0 mt-0.5">Laptop</span>
                                </div>
                            </div>
                            <div class="uses-item p-4 rounded-xl border border-[#1e2a3a]">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h3 class="font-semibold text-sm">Sony Alpha Camera</h3>
                                        <p class="text-xs text-gray-500 mt-0.5">For photography and video content. Details TBD as I finalize my gear.</p>
                                    </div>
                                    <span class="text-[10px] font-mono text-gray-600 flex-shrink-0 mt-0.5">Camera</span>
                                </div>
                            </div>
                            <div class="uses-item p-4 rounded-xl border border-[#1e2a3a]">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h3 class="font-semibold text-sm">Desk Setup</h3>
                                        <p class="text-xs text-gray-500 mt-0.5">Currently upgrading my home office. Stay tuned for the full desk tour.</p>
                                    </div>
                                    <span class="text-[10px] font-mono text-gray-600 flex-shrink-0 mt-0.5">Office</span>
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- Content Creation --}}
                    <section class="mb-16">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="w-10 h-10 rounded-xl bg-[#9D5175]/10 flex items-center justify-center">
                                <svg class="w-5 h-5 text-[#9D5175]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            </div>
                            <h2 class="text-2xl font-extrabold">Content Creation</h2>
                        </div>
                        <div class="space-y-3">
                            <div class="uses-item p-4 rounded-xl border border-[#1e2a3a]">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h3 class="font-semibold text-sm">OBS Studio</h3>
                                        <p class="text-xs text-gray-500 mt-0.5">Screen recording and streaming. Free, open source, and endlessly customizable.</p>
                                    </div>
                                    <span class="text-[10px] font-mono text-gray-600 flex-shrink-0 mt-0.5">Recording</span>
                                </div>
                            </div>
                            <div class="uses-item p-4 rounded-xl border border-[#1e2a3a]">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h3 class="font-semibold text-sm">DaVinci Resolve</h3>
                                        <p class="text-xs text-gray-500 mt-0.5">Video editing. The free version is absurdly powerful for YouTube content.</p>
                                    </div>
                                    <span class="text-[10px] font-mono text-gray-600 flex-shrink-0 mt-0.5">Editing</span>
                                </div>
                            </div>
                            <div class="uses-item p-4 rounded-xl border border-[#1e2a3a]">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h3 class="font-semibold text-sm">Canva</h3>
                                        <p class="text-xs text-gray-500 mt-0.5">Thumbnails, social graphics, and quick design work. Not a designer, but Canva helps me fake it.</p>
                                    </div>
                                    <span class="text-[10px] font-mono text-gray-600 flex-shrink-0 mt-0.5">Design</span>
                                </div>
                            </div>
                            <div class="uses-item p-4 rounded-xl border border-[#1e2a3a]">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h3 class="font-semibold text-sm">Audacity</h3>
                                        <p class="text-xs text-gray-500 mt-0.5">Audio editing for podcast episodes. Simple, effective, and free.</p>
                                    </div>
                                    <span class="text-[10px] font-mono text-gray-600 flex-shrink-0 mt-0.5">Audio</span>
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- Productivity --}}
                    <section class="mb-16">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="w-10 h-10 rounded-xl bg-[#4A7FBF]/10 flex items-center justify-center">
                                <svg class="w-5 h-5 text-[#4A7FBF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                            </div>
                            <h2 class="text-2xl font-extrabold">Productivity</h2>
                        </div>
                        <div class="space-y-3">
                            <div class="uses-item p-4 rounded-xl border border-[#1e2a3a]">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h3 class="font-semibold text-sm">Notion</h3>
                                        <p class="text-xs text-gray-500 mt-0.5">Everything lives here. Family organization, project planning, content calendars, and notes.</p>
                                    </div>
                                    <span class="text-[10px] font-mono text-gray-600 flex-shrink-0 mt-0.5">Notes</span>
                                </div>
                            </div>
                            <div class="uses-item p-4 rounded-xl border border-[#1e2a3a]">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h3 class="font-semibold text-sm">Figma</h3>
                                        <p class="text-xs text-gray-500 mt-0.5">When I need to think visually before building. Wireframes and quick mockups.</p>
                                    </div>
                                    <span class="text-[10px] font-mono text-gray-600 flex-shrink-0 mt-0.5">Design</span>
                                </div>
                            </div>
                            <div class="uses-item p-4 rounded-xl border border-[#1e2a3a]">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h3 class="font-semibold text-sm">Slack / Discord</h3>
                                        <p class="text-xs text-gray-500 mt-0.5">Community and communication. Active in several Laravel and dev communities.</p>
                                    </div>
                                    <span class="text-[10px] font-mono text-gray-600 flex-shrink-0 mt-0.5">Communication</span>
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- The Stack --}}
                    <section>
                        <div class="flex items-center gap-3 mb-8">
                            <div class="w-10 h-10 rounded-xl bg-[#4A7FBF]/10 flex items-center justify-center">
                                <svg class="w-5 h-5 text-[#4A7FBF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                            </div>
                            <h2 class="text-2xl font-extrabold">This Site Is Built With</h2>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            @foreach([
                                ['name' => 'Laravel 12', 'desc' => 'Framework'],
                                ['name' => 'Filament 5', 'desc' => 'Admin panel'],
                                ['name' => 'Tailwind CSS', 'desc' => 'Styling'],
                                ['name' => 'Blade', 'desc' => 'Templates'],
                                ['name' => 'SQLite', 'desc' => 'Database'],
                                ['name' => 'Pest', 'desc' => 'Testing'],
                                ['name' => 'Laravel Forge', 'desc' => 'Deployment'],
                                ['name' => 'Intervention Image', 'desc' => 'OG images'],
                                ['name' => 'Prism.js', 'desc' => 'Syntax highlighting'],
                            ] as $tech)
                            <div class="p-3 rounded-lg border border-[#1e2a3a] bg-[#0D1117]/50">
                                <p class="font-semibold text-xs">{{ $tech['name'] }}</p>
                                <p class="text-[10px] text-gray-500 mt-0.5">{{ $tech['desc'] }}</p>
                            </div>
                            @endforeach
                        </div>
                    </section>
                </div>

                {{-- Sidebar --}}
                <div class="lg:w-72 flex-shrink-0">
                    <div class="lg:sticky lg:top-8 space-y-6">
                        {{-- Quick nav --}}
                        <div class="p-5 rounded-2xl border border-[#1e2a3a] bg-[#0D1117]">
                            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-4">Jump To</h3>
                            <nav class="space-y-2">
                                <a href="#" class="block text-sm text-gray-400 hover:text-[#4A7FBF] transition-colors">Development</a>
                                <a href="#" class="block text-sm text-gray-400 hover:text-[#4A7FBF] transition-colors">Hardware</a>
                                <a href="#" class="block text-sm text-gray-400 hover:text-[#4A7FBF] transition-colors">Content Creation</a>
                                <a href="#" class="block text-sm text-gray-400 hover:text-[#4A7FBF] transition-colors">Productivity</a>
                                <a href="#" class="block text-sm text-gray-400 hover:text-[#4A7FBF] transition-colors">This Site</a>
                            </nav>
                        </div>

                        {{-- Note --}}
                        <div class="p-5 rounded-2xl border border-[#1e2a3a] bg-[#0D1117]">
                            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-3">Note</h3>
                            <p class="text-xs text-gray-500 leading-relaxed">This page is a work in progress. I'm still building out my home office and content creation setup. I'll update this as things evolve.</p>
                        </div>

                        {{-- uses.tech --}}
                        <div class="p-5 rounded-2xl border border-[#1e2a3a] bg-[#0D1117]">
                            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-3">Inspired By</h3>
                            <a href="https://uses.tech" target="_blank" class="inline-flex items-center gap-1.5 text-sm text-[#4A7FBF] hover:underline">
                                uses.tech
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                            <p class="text-xs text-gray-500 mt-1">A list of developer /uses pages.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
