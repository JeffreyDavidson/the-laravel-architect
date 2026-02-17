<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth dark">
<script>
    // Sync theme before paint to prevent flash
    if (localStorage.theme === 'light' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: light)').matches)) {
        document.documentElement.classList.remove('dark');
    }
</script>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" sizes="32x32" href="/images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/images/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/images/favicon-180x180.png">
    <link rel="manifest" href="/site.webmanifest">
    <meta name="theme-color" content="#0D1117">
    <link rel="alternate" type="application/rss+xml" title="The Laravel Architect" href="/rss">
    {!! seo() !!}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @if(config('services.fathom.site_id'))
    <script src="https://cdn.usefathom.com/script.js" data-site="{{ config('services.fathom.site_id') }}" defer></script>
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.json-ld')
    @stack('head')
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|jetbrains-mono:400,500" rel="stylesheet" />
</head>
<body class="bg-white dark:bg-brand-950 text-gray-800 dark:text-gray-100 font-sans antialiased">
    {{-- Navigation --}}
    <nav class="sticky top-0 z-50 border-b border-gray-200 dark:border-brand-800/50 bg-white/90 dark:bg-brand-950/90 backdrop-blur-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                    <img src="/images/logo-color.svg" alt="The Laravel Architect" class="w-9 h-9 rounded-full">
                    <span class="flex items-baseline gap-1 text-gray-900 dark:text-white group-hover:text-brand-400 transition-colors">
                        <span class="text-xs font-semibold tracking-widest uppercase">The</span>
                        <span class="text-xl font-empera tracking-wide">Laravel</span>
                        <span class="text-xs font-semibold tracking-widest uppercase">Architect</span>
                    </span>
                </a>

                {{-- Mobile hamburger --}}
                <button id="mobile-menu-btn" class="md:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-brand-800 transition-colors" aria-label="Toggle menu">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path id="hamburger-icon" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <div class="hidden md:flex items-center gap-8">
                    <a href="{{ route('home') }}" class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors @if(request()->routeIs('home')) text-gray-900 dark:text-white @endif">Home</a>
                    <a href="{{ route('blog.index') }}" class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors @if(request()->routeIs('blog.*')) text-gray-900 dark:text-white @endif">Blog</a>
                    <a href="{{ route('podcast.index') }}" class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors @if(request()->routeIs('podcast.*')) text-gray-900 dark:text-white @endif">Podcasts</a>
                    <a href="{{ route('projects.index') }}" class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors @if(request()->routeIs('projects.*')) text-gray-900 dark:text-white @endif">Projects</a>
                    <a href="{{ route('about') }}" class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors @if(request()->routeIs('about')) text-gray-900 dark:text-white @endif">About</a>
                    <a href="{{ route('uses') }}" class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors @if(request()->routeIs('uses')) text-gray-900 dark:text-white @endif">Uses</a>
                    <button id="theme-toggle" class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-brand-800/50 transition-colors" title="Toggle theme">
                        <svg id="theme-icon-dark" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                        <svg id="theme-icon-light" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </button>
                    <a href="{{ route('contact') }}" class="inline-flex items-center px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white text-sm font-medium rounded-lg transition-colors">Contact Me</a>
                </div>
            </div>

            {{-- Mobile menu --}}
            <div id="mobile-menu" class="hidden md:hidden border-t border-gray-200 dark:border-brand-800/50 py-4">
                <div class="flex flex-col gap-3">
                    <a href="{{ route('home') }}" class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors px-2 py-1 @if(request()->routeIs('home')) text-gray-900 dark:text-white @endif">Home</a>
                    <a href="{{ route('blog.index') }}" class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors px-2 py-1 @if(request()->routeIs('blog.*')) text-gray-900 dark:text-white @endif">Blog</a>
                    <a href="{{ route('podcast.index') }}" class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors px-2 py-1 @if(request()->routeIs('podcast.*')) text-gray-900 dark:text-white @endif">Podcasts</a>
                    <a href="{{ route('projects.index') }}" class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors px-2 py-1 @if(request()->routeIs('projects.*')) text-gray-900 dark:text-white @endif">Projects</a>
                    <a href="{{ route('about') }}" class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors px-2 py-1 @if(request()->routeIs('about')) text-gray-900 dark:text-white @endif">About</a>
                    <a href="{{ route('uses') }}" class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors px-2 py-1 @if(request()->routeIs('uses')) text-gray-900 dark:text-white @endif">Uses</a>
                    <button class="theme-toggle-mobile flex items-center gap-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors px-2 py-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                        <span class="theme-toggle-label">Light Mode</span>
                    </button>
                    <a href="{{ route('contact') }}" class="inline-flex items-center px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white text-sm font-medium rounded-lg transition-colors w-fit">Contact Me</a>
                </div>
            </div>
        </div>
    </nav>

    <script>
        // Mobile menu
        document.getElementById('mobile-menu-btn').addEventListener('click', function() {
            document.getElementById('mobile-menu').classList.toggle('hidden');
        });

        // Theme toggle
        function updateThemeIcons() {
            const isDark = document.documentElement.classList.contains('dark');
            document.getElementById('theme-icon-dark').classList.toggle('hidden', isDark);
            document.getElementById('theme-icon-light').classList.toggle('hidden', !isDark);
            document.querySelectorAll('.theme-toggle-label').forEach(el => {
                el.textContent = isDark ? 'Light Mode' : 'Dark Mode';
            });
            // Update theme-color meta
            document.querySelector('meta[name="theme-color"]').content = isDark ? '#0D1117' : '#ffffff';
        }

        function toggleTheme() {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.theme = isDark ? 'dark' : 'light';
            updateThemeIcons();
        }

        document.getElementById('theme-toggle').addEventListener('click', toggleTheme);
        document.querySelectorAll('.theme-toggle-mobile').forEach(el => el.addEventListener('click', toggleTheme));
        updateThemeIcons();
    </script>

    {{-- Content --}}
    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="relative border-t border-gray-200 dark:border-[#1e2a3a] overflow-hidden bg-gradient-to-b from-gray-50 to-white dark:from-brand-950 dark:to-brand-950">
        {{-- Subtle grid background --}}
        <div class="absolute inset-0 opacity-[0.03] dark:opacity-[0.015]" style="background-image: radial-gradient(circle, #4A7FBF 1px, transparent 1px); background-size: 24px 24px;"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 sm:pt-12 pb-8">

            {{-- Top section: Brand + Links --}}
            <div class="flex flex-col lg:flex-row justify-between gap-6 lg:gap-10 mb-8 lg:mb-14">
                <div class="max-w-sm">
                    <a href="{{ route('home') }}" class="flex items-center gap-3 mb-3 group">
                        <img src="/images/logo-color.svg" alt="The Laravel Architect" class="w-10 h-10 rounded-full">
                        <span class="flex items-baseline gap-1 text-gray-900 dark:text-white group-hover:text-[#4A7FBF] transition-colors">
                            <span class="text-[10px] font-semibold tracking-widest uppercase">The</span>
                            <span class="text-xl font-empera tracking-wide">Laravel</span>
                            <span class="text-[10px] font-semibold tracking-widest uppercase">Architect</span>
                        </span>
                    </a>
                    <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed mb-4">Building elegant web applications with Laravel. Writing about code, architecture, and the developer life.</p>

                    {{-- Social icons --}}
                    <x-social-links variant="buttons" />
                </div>

                {{-- Links columns --}}
                <div class="flex gap-10 sm:gap-16">
                    <div>
                        <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-500 uppercase tracking-widest mb-3">Navigate</h4>
                        <ul class="space-y-2 text-sm">
                            <li><a href="{{ route('blog.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">Blog</a></li>
                            <li><a href="{{ route('podcast.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">Podcasts</a></li>
                            <li><a href="{{ route('projects.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">Projects</a></li>
                            <li><a href="{{ route('about') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">About</a></li>
                            <li><a href="{{ route('uses') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">Uses</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-500 uppercase tracking-widest mb-3">Resources</h4>
                        <ul class="space-y-2 text-sm">
                            <li><a href="{{ route('contact') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">Contact</a></li>
                            <li><a href="/rss" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">RSS Feed</a></li>
                            <li><a href="https://uses.tech" target="_blank" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">uses.tech</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Gradient line --}}
            <div class="h-px w-full" style="background: linear-gradient(90deg, transparent, #4A7FBF55, #9D517544, #4A7FBF55, transparent);"></div>

            {{-- Bottom bar --}}
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-6">
                <p class="text-xs text-gray-600 dark:text-gray-400">
                    &copy; {{ date('Y') }} Jeffrey Davidson. Built with
                    <a href="https://laravel.com" target="_blank" class="text-gray-600 dark:text-gray-400 hover:text-[#4A7FBF] transition-colors">Laravel</a> &
                    <a href="https://filamentphp.com" target="_blank" class="text-gray-600 dark:text-gray-400 hover:text-[#4A7FBF] transition-colors">Filament</a>.
                </p>
                <p class="text-xs text-gray-600 dark:text-gray-400">
                    Designed with ☕ in Florida
                </p>
            </div>
        </div>
    </footer>
    @stack('scripts')
</body>
</html>
