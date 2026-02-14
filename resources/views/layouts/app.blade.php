<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'The Laravel Architect') — Jeffrey Davidson</title>
    <meta name="description" content="@yield('meta_description', 'Blog, portfolio, and insights from Jeffrey Davidson — Laravel developer, content creator, and software architect.')">
    <link rel="icon" type="image/svg+xml" href="/images/logo-color.svg">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|jetbrains-mono:400,500" rel="stylesheet" />
</head>
<body class="bg-brand-950 text-gray-100 font-sans antialiased">
    {{-- Navigation --}}
    <nav class="sticky top-0 z-50 border-b border-brand-800/50 bg-brand-950/90 backdrop-blur-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                    <img src="/images/logo-color.svg" alt="The Laravel Architect" class="w-9 h-9 rounded-full">
                    <span class="flex items-baseline gap-1 text-white group-hover:text-brand-400 transition-colors">
                        <span class="text-xs font-semibold tracking-widest uppercase">The</span>
                        <span class="text-xl font-empera tracking-wide">Laravel</span>
                        <span class="text-xs font-semibold tracking-widest uppercase">Architect</span>
                    </span>
                </a>

                {{-- Mobile hamburger --}}
                <button id="mobile-menu-btn" class="md:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-white hover:bg-brand-800 transition-colors" aria-label="Toggle menu">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path id="hamburger-icon" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <div class="hidden md:flex items-center gap-8">
                    <a href="{{ route('home') }}" class="text-sm font-medium text-gray-400 hover:text-white transition-colors @if(request()->routeIs('home')) text-white @endif">Home</a>
                    <a href="{{ route('blog.index') }}" class="text-sm font-medium text-gray-400 hover:text-white transition-colors @if(request()->routeIs('blog.*')) text-white @endif">Blog</a>
                    <a href="{{ route('podcast.index') }}" class="text-sm font-medium text-gray-400 hover:text-white transition-colors @if(request()->routeIs('podcast.*')) text-white @endif">Podcasts</a>
                    <a href="{{ route('projects.index') }}" class="text-sm font-medium text-gray-400 hover:text-white transition-colors @if(request()->routeIs('projects.*')) text-white @endif">Projects</a>
                    <a href="{{ route('about') }}" class="text-sm font-medium text-gray-400 hover:text-white transition-colors @if(request()->routeIs('about')) text-white @endif">About</a>
                    <a href="{{ route('contact') }}" class="inline-flex items-center px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white text-sm font-medium rounded-lg transition-colors">Hire Me</a>
                </div>
            </div>

            {{-- Mobile menu --}}
            <div id="mobile-menu" class="hidden md:hidden border-t border-brand-800/50 py-4">
                <div class="flex flex-col gap-3">
                    <a href="{{ route('home') }}" class="text-sm font-medium text-gray-400 hover:text-white transition-colors px-2 py-1 @if(request()->routeIs('home')) text-white @endif">Home</a>
                    <a href="{{ route('blog.index') }}" class="text-sm font-medium text-gray-400 hover:text-white transition-colors px-2 py-1 @if(request()->routeIs('blog.*')) text-white @endif">Blog</a>
                    <a href="{{ route('podcast.index') }}" class="text-sm font-medium text-gray-400 hover:text-white transition-colors px-2 py-1 @if(request()->routeIs('podcast.*')) text-white @endif">Podcasts</a>
                    <a href="{{ route('projects.index') }}" class="text-sm font-medium text-gray-400 hover:text-white transition-colors px-2 py-1 @if(request()->routeIs('projects.*')) text-white @endif">Projects</a>
                    <a href="{{ route('about') }}" class="text-sm font-medium text-gray-400 hover:text-white transition-colors px-2 py-1 @if(request()->routeIs('about')) text-white @endif">About</a>
                    <a href="{{ route('contact') }}" class="inline-flex items-center px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white text-sm font-medium rounded-lg transition-colors w-fit">Hire Me</a>
                </div>
            </div>
        </div>
    </nav>

    <script>
        document.getElementById('mobile-menu-btn').addEventListener('click', function() {
            document.getElementById('mobile-menu').classList.toggle('hidden');
        });
    </script>

    {{-- Content --}}
    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="border-t border-brand-800/50 mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <div class="flex items-center gap-3 mb-3">
                        <img src="/images/logo-color.svg" alt="The Laravel Architect" class="w-8 h-8">
                        <span class="flex items-baseline gap-1 text-white">
                            <span class="text-[10px] font-semibold tracking-widest uppercase">The</span>
                            <span class="text-lg font-empera tracking-wide">Laravel</span>
                            <span class="text-[10px] font-semibold tracking-widest uppercase">Architect</span>
                        </span>
                    </div>
                    <p class="text-sm text-gray-500">Building elegant web applications with Laravel. Writing about code, architecture, and the developer life.</p>
                </div>
                <div>
                    <h4 class="font-semibold mb-3 text-white">Links</h4>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li><a href="{{ route('blog.index') }}" class="hover:text-brand-400 transition-colors">Blog</a></li>
                        <li><a href="{{ route('podcast.index') }}" class="hover:text-brand-400 transition-colors">Podcasts</a></li>
                        <li><a href="{{ route('projects.index') }}" class="hover:text-brand-400 transition-colors">Projects</a></li>
                        <li><a href="{{ route('about') }}" class="hover:text-brand-400 transition-colors">About</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-brand-400 transition-colors">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-3 text-white">Connect</h4>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li><a href="https://youtube.com/channel/UC42H30o7l5QvvCzC86dSu_A" target="_blank" class="hover:text-brand-400 transition-colors">YouTube</a></li>
                        <li><a href="https://instagram.com/thelaravelarch" target="_blank" class="hover:text-brand-400 transition-colors">Instagram</a></li>
                        <li><a href="https://twitter.com/thelaravelarch" target="_blank" class="hover:text-brand-400 transition-colors">Twitter / X</a></li>
                        <li><a href="https://bsky.app/profile/thelaravelarch" target="_blank" class="hover:text-brand-400 transition-colors">Bluesky</a></li>
                        <li><a href="https://facebook.com/thelaravelarch" target="_blank" class="hover:text-brand-400 transition-colors">Facebook</a></li>
                        <li><a href="#" class="hover:text-brand-400 transition-colors">GitHub</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-8 pt-8 border-t border-brand-800/50 text-center text-sm text-gray-600">
                &copy; {{ date('Y') }} Jeffrey Davidson. Built with Laravel & Filament.
            </div>
        </div>
    </footer>
    @stack('scripts')
</body>
</html>
