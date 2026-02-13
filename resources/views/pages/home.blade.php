@extends('layouts.app')

@section('content')
    {{-- Hero --}}
    <section class="relative overflow-hidden">
        {{-- Geometric background shapes --}}
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -top-20 -right-20 w-96 h-96 bg-brand-600/5 rotate-45 rounded-3xl"></div>
            <div class="absolute top-40 -left-10 w-64 h-64 bg-accent-600/5 rotate-12 rounded-3xl"></div>
            <div class="absolute bottom-0 right-1/4 w-48 h-48 bg-brand-400/5 -rotate-12 rounded-3xl"></div>
        </div>

        <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-24 lg:py-32">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div>
                    <p class="text-brand-400 font-semibold mb-4">Hey, I'm Jeffrey Davidson 👋</p>
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold tracking-tight leading-tight mb-6 text-white">
                        I build things for the web with <span class="text-brand-400">Laravel</span>
                    </h1>
                    <p class="text-lg text-gray-400 mb-8 max-w-2xl">
                        Software developer, content creator, and architect of clean, maintainable applications.
                        I write about Laravel, PHP, web development, and the lessons learned along the way.
                    </p>
                    <div class="flex flex-wrap gap-4 mb-8">
                        <a href="{{ route('blog.index') }}" class="inline-flex items-center px-6 py-3 bg-brand-600 hover:bg-brand-500 text-white font-medium rounded-lg transition-colors">
                            Read the Blog
                        </a>
                        <a href="{{ route('projects.index') }}" class="inline-flex items-center px-6 py-3 border border-brand-700 hover:border-brand-600 text-gray-300 hover:text-white font-medium rounded-lg transition-colors">
                            View Projects
                        </a>
                    </div>

                    {{-- Social links --}}
                    <div class="flex items-center gap-5">
                        <a href="https://youtube.com/channel/UC42H30o7l5QvvCzC86dSu_A" target="_blank" class="text-gray-500 hover:text-red-500 transition-colors" title="YouTube">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                        </a>
                        <a href="https://instagram.com/thelaravelarch" target="_blank" class="text-gray-500 hover:text-pink-500 transition-colors" title="Instagram">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z"/></svg>
                        </a>
                        <a href="https://twitter.com/thelaravelarch" target="_blank" class="text-gray-500 hover:text-white transition-colors" title="X / Twitter">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                        </a>
                        <a href="https://bsky.app/profile/thelaravelarch" target="_blank" class="text-gray-500 hover:text-blue-400 transition-colors" title="Bluesky">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 10.8c-1.087-2.114-4.046-6.053-6.798-7.995C2.566.944 1.561 1.266.902 1.565.139 1.908 0 3.08 0 3.768c0 .69.378 5.65.624 6.479.785 2.627 3.6 3.476 6.153 3.228-4.488.744-8.126 3.46-4.542 7.522 3.84 3.793 5.903-.686 6.765-3.127.181-.511.264-.749.3-.549.036-.2.119.038.3.549.863 2.44 2.925 6.92 6.765 3.127 3.584-4.063-.054-6.778-4.542-7.522 2.554.248 5.368-.601 6.153-3.228C18.622 9.418 19 4.458 19 3.768c0-.688-.139-1.86-.902-2.203-.659-.299-1.664-.621-4.3 1.24C11.046 4.747 8.087 8.686 7 10.8z"/></svg>
                        </a>
                    </div>
                </div>

                {{-- Brand character image --}}
                <div class="hidden lg:flex justify-center">
                    <img src="/images/logo-alternate.jpg" alt="The Laravel Architect character" class="w-80 h-80 object-cover rounded-2xl shadow-2xl shadow-brand-600/10 border border-brand-800/50">
                </div>
            </div>
        </div>
    </section>

    {{-- Featured Projects --}}
    @if($featuredProjects->count())
    <section class="bg-brand-900/50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="flex items-center justify-between mb-10">
                <h2 class="text-2xl font-bold text-white">Featured Projects</h2>
                <a href="{{ route('projects.index') }}" class="text-sm text-brand-400 hover:text-brand-300">View all →</a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($featuredProjects as $project)
                <a href="{{ route('projects.show', $project) }}" class="group block bg-brand-900 rounded-xl border border-brand-800 overflow-hidden hover:border-brand-600/50 transition-all hover:shadow-lg hover:shadow-brand-600/5">
                    @if($project->featured_image)
                    <div class="aspect-video bg-brand-800 overflow-hidden">
                        <img src="{{ Storage::url($project->featured_image) }}" alt="{{ $project->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    </div>
                    @endif
                    <div class="p-6">
                        <h3 class="font-semibold text-lg text-white mb-2 group-hover:text-brand-400 transition-colors">{{ $project->title }}</h3>
                        <p class="text-gray-400 text-sm mb-4">{{ $project->description }}</p>
                        @if($project->tech_stack)
                        <div class="flex flex-wrap gap-2">
                            @foreach($project->tech_stack as $tech)
                            <span class="px-2 py-1 bg-brand-800 text-brand-300 text-xs font-medium rounded">{{ $tech }}</span>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- Latest Posts --}}
    @if($latestPosts->count())
    <section>
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="flex items-center justify-between mb-10">
                <h2 class="text-2xl font-bold text-white">Latest Posts</h2>
                <a href="{{ route('blog.index') }}" class="text-sm text-brand-400 hover:text-brand-300">View all →</a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($latestPosts as $post)
                <article class="group">
                    <a href="{{ route('blog.show', $post) }}" class="block">
                        @if($post->featured_image)
                        <div class="aspect-video rounded-xl overflow-hidden mb-4 bg-brand-800">
                            <img src="{{ Storage::url($post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        </div>
                        @endif
                        @if($post->category)
                        <span class="text-xs font-semibold text-brand-400 uppercase tracking-wide">{{ $post->category->name }}</span>
                        @endif
                        <h3 class="font-semibold text-lg text-white mt-1 mb-2 group-hover:text-brand-400 transition-colors">{{ $post->title }}</h3>
                        <p class="text-gray-400 text-sm line-clamp-2">{{ $post->excerpt }}</p>
                        <div class="mt-3 flex items-center gap-3 text-xs text-gray-500">
                            <time>{{ $post->published_at->format('M d, Y') }}</time>
                            <span>·</span>
                            <span>{{ $post->reading_time }} min read</span>
                        </div>
                    </a>
                </article>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- Podcasts Section --}}
    <section class="bg-brand-900/50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="flex items-center justify-between mb-10">
                <h2 class="text-2xl font-bold text-white">Podcasts</h2>
                <a href="{{ route('podcast.index') }}" class="text-sm text-brand-400 hover:text-brand-300">View all →</a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-brand-900 rounded-xl border border-brand-800 p-8 hover:border-brand-600/50 transition-all">
                    <div class="text-4xl mb-4">☕</div>
                    <h3 class="text-xl font-bold text-white mb-2">Coffee With The Laravel Architect</h3>
                    <p class="text-gray-400 text-sm mb-4">Conversations about Laravel, web development, and the developer life — one cup at a time.</p>
                    <a href="{{ route('podcast.index') }}" class="text-sm text-brand-400 hover:text-brand-300">Listen now →</a>
                </div>
                <div class="bg-brand-900 rounded-xl border border-brand-800 p-8 hover:border-accent-600/50 transition-all">
                    <div class="text-4xl mb-4">🌧️</div>
                    <h3 class="text-xl font-bold text-white mb-2">Embracing Cloudy Days</h3>
                    <p class="text-gray-400 text-sm mb-4">Real talk about mental health, burnout, work-life balance, and finding your way through the fog.</p>
                    <a href="{{ route('podcast.index') }}" class="text-sm text-accent-500 hover:text-accent-400">Listen now →</a>
                </div>
            </div>
        </div>
    </section>

    {{-- YouTube CTA --}}
    <section>
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
            <h2 class="text-2xl font-bold text-white mb-4">Watch on YouTube</h2>
            <p class="text-gray-400 mb-8 max-w-xl mx-auto">
                Tutorials, live coding, and conversations about Laravel and web development.
            </p>
            <a href="https://youtube.com/channel/UC42H30o7l5QvvCzC86dSu_A" target="_blank" class="inline-flex items-center px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                Subscribe to The Laravel Architect
            </a>
        </div>
    </section>

    {{-- CTA --}}
    <section class="bg-brand-900/50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
            <h2 class="text-2xl font-bold text-white mb-4">Need a Laravel Developer?</h2>
            <p class="text-gray-400 mb-8 max-w-xl mx-auto">
                I'm available for freelance projects, consulting, and collaborations. Let's build something great together.
            </p>
            <a href="{{ route('contact') }}" class="inline-flex items-center px-6 py-3 bg-brand-600 hover:bg-brand-500 text-white font-medium rounded-lg transition-colors">
                Get in Touch
            </a>
        </div>
    </section>
@endsection
