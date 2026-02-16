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
                    <button class="theme-toggle-mobile flex items-center gap-2 text-sm font-medium text-gray-400 hover:text-white transition-colors px-2 py-1">
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
    <footer class="relative border-t border-gray-200 dark:border-[#1e2a3a] overflow-hidden bg-white dark:bg-brand-950">
        {{-- Subtle grid background --}}
        <div class="absolute inset-0 opacity-[0.015]" style="background-image: radial-gradient(circle, #ffffff 1px, transparent 1px); background-size: 24px 24px;"></div>

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
                    <div class="flex items-center gap-3">
                        <a href="https://github.com/JeffreyDavidson" target="_blank" class="w-9 h-9 rounded-lg border border-gray-200 dark:border-[#1e2a3a] flex items-center justify-center text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:border-[#4A7FBF]/50 hover:bg-[#4A7FBF]/5 transition-all" title="GitHub">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
                        </a>
                        <a href="https://x.com/thelaravelarch" target="_blank" class="w-9 h-9 rounded-lg border border-gray-200 dark:border-[#1e2a3a] flex items-center justify-center text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:border-[#4A7FBF]/50 hover:bg-[#4A7FBF]/5 transition-all" title="X / Twitter">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                        </a>
                        <a href="https://youtube.com/@thelaravelarchitect" target="_blank" class="w-9 h-9 rounded-lg border border-gray-200 dark:border-[#1e2a3a] flex items-center justify-center text-gray-500 dark:text-gray-400 hover:text-red-500 hover:border-red-500/50 hover:bg-red-500/5 transition-all" title="YouTube">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814z"/><path fill="currentColor" d="M9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                        </a>
                        <a href="https://bsky.app/profile/thelaravelarch" target="_blank" class="w-9 h-9 rounded-lg border border-gray-200 dark:border-[#1e2a3a] flex items-center justify-center text-gray-500 dark:text-gray-400 hover:text-blue-400 hover:border-blue-400/50 hover:bg-blue-400/5 transition-all" title="Bluesky">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 10.8c-1.087-2.114-4.046-6.053-6.798-7.995C2.566.944 1.561 1.266.902 1.565.139 1.908 0 3.08 0 3.768c0 .69.378 5.65.624 6.479.785 2.627 3.584 3.493 6.173 3.243-4.058.666-7.652 2.174-4.461 7.744 3.648 5.123 5.353-1.31 5.664-2.978.311 1.669 1.104 8.101 5.664 2.978 3.191-5.57-.403-7.078-4.461-7.744 2.589.25 5.388-.616 6.173-3.243C15.622 9.418 16 4.458 16 3.768c0-.69-.139-1.861-.902-2.203-.659-.299-1.664-.62-4.3 1.24C8.046 4.747 5.087 8.686 4 10.8"/></svg>
                        </a>
                        <a href="https://instagram.com/thelaravelarch" target="_blank" class="w-9 h-9 rounded-lg border border-gray-200 dark:border-[#1e2a3a] flex items-center justify-center text-gray-500 dark:text-gray-400 hover:text-pink-400 hover:border-pink-400/50 hover:bg-pink-400/5 transition-all" title="Instagram">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                        <a href="https://facebook.com/thelaravelarch" target="_blank" class="w-9 h-9 rounded-lg border border-gray-200 dark:border-[#1e2a3a] flex items-center justify-center text-gray-500 dark:text-gray-400 hover:text-blue-500 hover:border-blue-500/50 hover:bg-blue-500/5 transition-all" title="Facebook">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                    </div>
                </div>

                {{-- Links columns --}}
                <div class="flex gap-10 sm:gap-16">
                    <div>
                        <h4 class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-3">Navigate</h4>
                        <ul class="space-y-2 text-sm">
                            <li><a href="{{ route('blog.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">Blog</a></li>
                            <li><a href="{{ route('podcast.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">Podcasts</a></li>
                            <li><a href="{{ route('projects.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">Projects</a></li>
                            <li><a href="{{ route('about') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">About</a></li>
                            <li><a href="{{ route('uses') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">Uses</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-3">Resources</h4>
                        <ul class="space-y-2 text-sm">
                            <li><a href="{{ route('contact') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">Contact</a></li>
                            <li><a href="/rss" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">RSS Feed</a></li>
                            <li><a href="https://uses.tech" target="_blank" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">uses.tech</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Gradient line --}}
            <div class="h-px w-full" style="background: linear-gradient(90deg, transparent, #4A7FBF33, #9D517533, #4A7FBF33, transparent);"></div>

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
