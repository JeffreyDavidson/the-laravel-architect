@extends('layouts.app')

@section('content')
<style>
    /* ===== Gradient Mesh Hero Background ===== */
    @keyframes meshShift {
        0%, 100% { background-position: 0% 50%; }
        25% { background-position: 50% 0%; }
        50% { background-position: 100% 50%; }
        75% { background-position: 50% 100%; }
    }
    .hero-mesh {
        background: linear-gradient(-45deg, #0D1117, #1a1040, #0d2847, #1a0d30, #0D1117);
        background-size: 400% 400%;
        animation: meshShift 20s ease infinite;
    }

    /* ===== Laravel Gradient Glow Text ===== */
    .laravel-glow {
        font-family: 'Empera', serif;
        background: linear-gradient(135deg, #4A7FBF, #6fa3d6, #4A7FBF);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        filter: drop-shadow(0 0 20px rgba(74, 127, 191, 0.5)) drop-shadow(0 0 40px rgba(74, 127, 191, 0.25));
    }

    /* ===== Typing Effect ===== */
    .typing-wrapper {
        display: inline-flex;
        align-items: center;
        min-height: 1.5em;
    }
    .typing-text {
        border-right: 2px solid #4A7FBF;
        animation: blink 0.7s step-end infinite;
        white-space: nowrap;
        overflow: hidden;
    }
    @keyframes blink {
        50% { border-color: transparent; }
    }

    /* ===== Floating Code Snippet ===== */
    @keyframes floatIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 0.12; transform: translateY(0); }
    }
    .floating-code {
        animation: floatIn 2s ease 1s forwards;
        opacity: 0;
    }

    /* ===== Glowing Buttons ===== */
    .glow-btn {
        position: relative;
        transition: all 0.3s ease;
    }
    .glow-btn:hover {
        box-shadow: 0 0 20px rgba(74, 127, 191, 0.4), 0 0 40px rgba(74, 127, 191, 0.2);
        transform: translateY(-2px);
    }
    .glow-btn-outline:hover {
        box-shadow: 0 0 20px rgba(74, 127, 191, 0.3), 0 0 40px rgba(74, 127, 191, 0.15);
        transform: translateY(-2px);
        border-color: #4A7FBF;
        color: white;
    }

    /* ===== Glassmorphism Cards ===== */
    .glass-card {
        background: rgba(26, 29, 33, 0.6);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(74, 127, 191, 0.15);
        transition: all 0.4s ease;
        transform-style: preserve-3d;
        perspective: 800px;
    }
    .glass-card:hover {
        border-color: rgba(74, 127, 191, 0.5);
        box-shadow: 0 0 30px rgba(74, 127, 191, 0.15), inset 0 0 30px rgba(74, 127, 191, 0.03);
        transform: rotateY(-2deg) rotateX(2deg) scale(1.02);
    }

    /* ===== Gradient Top Border Cards ===== */
    .gradient-border-card {
        position: relative;
        overflow: hidden;
    }
    .gradient-border-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, #4A7FBF, #E47A9D);
    }

    /* ===== Scroll Fade Up ===== */
    .fade-up {
        opacity: 0;
        transform: translateY(30px);
        transition: opacity 0.6s ease, transform 0.6s ease;
    }
    .fade-up.visible {
        opacity: 1;
        transform: translateY(0);
    }

    /* ===== Equalizer Bars ===== */
    @keyframes eq1 { 0%,100% { height: 8px; } 50% { height: 24px; } }
    @keyframes eq2 { 0%,100% { height: 16px; } 50% { height: 8px; } }
    @keyframes eq3 { 0%,100% { height: 12px; } 50% { height: 28px; } }
    @keyframes eq4 { 0%,100% { height: 20px; } 50% { height: 10px; } }
    @keyframes eq5 { 0%,100% { height: 6px; } 50% { height: 22px; } }
    .eq-bar { width: 3px; border-radius: 2px; display: inline-block; vertical-align: bottom; }
    .eq-bar:nth-child(1) { animation: eq1 1.2s ease-in-out infinite; }
    .eq-bar:nth-child(2) { animation: eq2 1.0s ease-in-out infinite 0.1s; }
    .eq-bar:nth-child(3) { animation: eq3 1.4s ease-in-out infinite 0.2s; }
    .eq-bar:nth-child(4) { animation: eq4 0.9s ease-in-out infinite 0.3s; }
    .eq-bar:nth-child(5) { animation: eq5 1.1s ease-in-out infinite 0.15s; }

    /* ===== Browser Frame ===== */
    .browser-frame {
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 0 40px rgba(239, 68, 68, 0.2), 0 0 80px rgba(239, 68, 68, 0.1);
        transition: box-shadow 0.3s;
    }
    .browser-frame:hover {
        box-shadow: 0 0 60px rgba(239, 68, 68, 0.3), 0 0 100px rgba(239, 68, 68, 0.15);
    }
    .browser-dots span {
        width: 10px; height: 10px; border-radius: 50%; display: inline-block;
    }

    /* ===== Count Up ===== */
    .count-up { display: inline-block; }
</style>

{{-- ===== HERO ===== --}}
<section class="hero-mesh relative overflow-hidden min-h-[90vh] flex items-center">
    {{-- Floating Code Snippet --}}
    <div class="floating-code absolute bottom-8 right-8 md:bottom-16 md:right-16 font-mono text-xs md:text-sm text-brand-400 leading-relaxed pointer-events-none select-none hidden md:block">
        <pre class="text-left"><code>Route::middleware('architect')
    ->group(function () {
        Route::get('/build', [
            ProjectController::class,
            'create'
        ]);
    });</code></pre>
    </div>

    <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-24 lg:py-32 text-center z-10">
        <p class="text-brand-400 font-semibold mb-6 text-sm tracking-wide uppercase">Hey, I'm Jeffrey Davidson 👋</p>

        <h1 class="text-4xl sm:text-5xl lg:text-7xl font-bold tracking-tight leading-tight mb-6 text-white">
            I architect things for the web with
            <br>
            <span class="laravel-glow text-5xl sm:text-6xl lg:text-8xl">Laravel</span>
        </h1>

        <div class="text-lg sm:text-xl text-gray-400 mb-4">
            Crafting <span class="typing-wrapper"><span class="typing-text text-brand-300" id="typed-text"></span></span>
        </div>

        <p class="text-gray-500 mb-10 max-w-2xl mx-auto">
            Software developer, content creator, and architect of clean, maintainable applications.
            I write about Laravel, PHP, web development, and the lessons learned along the way.
        </p>

        <div class="flex flex-wrap justify-center gap-4 mb-10">
            <a href="{{ route('blog.index') }}" class="glow-btn inline-flex items-center px-8 py-3.5 bg-brand-600 hover:bg-brand-500 text-white font-semibold rounded-lg">
                Read the Blog
            </a>
            <a href="{{ route('projects.index') }}" class="glow-btn glow-btn-outline inline-flex items-center px-8 py-3.5 border border-brand-700 text-gray-300 font-semibold rounded-lg transition-all">
                View Projects
            </a>
        </div>

        {{-- Social links --}}
        <div class="flex items-center justify-center gap-6">
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
</section>

{{-- ===== STATS BAR ===== --}}
<section class="border-y border-brand-800/50 bg-brand-900/80 backdrop-blur">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex flex-wrap justify-center items-center gap-x-8 gap-y-3 text-sm text-gray-400 font-medium">
            <span class="flex items-center gap-2">
                <span class="count-up text-white text-lg font-bold" data-target="15">0</span>+ years experience
            </span>
            <span class="hidden sm:inline text-brand-700">•</span>
            <span>Laravel & PHP</span>
            <span class="hidden sm:inline text-brand-700">•</span>
            <span>Content Creator</span>
            <span class="hidden sm:inline text-brand-700">•</span>
            <span class="text-brand-400 font-semibold">Available for hire</span>
        </div>
    </div>
</section>

{{-- ===== FEATURED PROJECTS ===== --}}
@if($featuredProjects->count())
<section class="py-20">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-12">
            <h2 class="text-3xl font-bold text-white">Featured Projects</h2>
            <a href="{{ route('projects.index') }}" class="text-sm text-brand-400 hover:text-brand-300 transition-colors">View all →</a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            @foreach($featuredProjects as $project)
            <a href="{{ route('projects.show', $project) }}" class="glass-card group block rounded-xl overflow-hidden fade-up">
                @if($project->featured_image)
                <div class="aspect-video bg-brand-800 overflow-hidden">
                    <img src="{{ Storage::url($project->featured_image) }}" alt="{{ $project->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                </div>
                @endif
                <div class="p-6">
                    <h3 class="font-semibold text-lg text-white mb-2 group-hover:text-brand-400 transition-colors">{{ $project->title }}</h3>
                    <p class="text-gray-400 text-sm mb-4">{{ $project->description }}</p>
                    @if($project->tech_stack)
                    <div class="flex flex-wrap gap-2">
                        @foreach($project->tech_stack as $tech)
                        <span class="px-2.5 py-1 bg-brand-800/80 text-brand-300 text-xs font-medium rounded-md border border-brand-700/50">{{ $tech }}</span>
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

{{-- ===== LATEST POSTS ===== --}}
@if($latestPosts->count())
<section class="py-20">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-12">
            <h2 class="text-3xl font-bold text-white">Latest Posts</h2>
            <a href="{{ route('blog.index') }}" class="text-sm text-brand-400 hover:text-brand-300 transition-colors">View all →</a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($latestPosts as $post)
            <article class="group fade-up gradient-border-card bg-brand-900/60 rounded-xl overflow-hidden border border-brand-800/50 hover:border-brand-600/40 transition-all duration-300">
                <a href="{{ route('blog.show', $post) }}" class="block">
                    @if($post->featured_image)
                    <div class="aspect-video overflow-hidden bg-brand-800">
                        <img src="{{ Storage::url($post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    </div>
                    @endif
                    <div class="p-5">
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
                    </div>
                </a>
            </article>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ===== PODCASTS ===== --}}
<section class="py-20">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-12">
            <h2 class="text-3xl font-bold text-white">Podcasts</h2>
            <a href="{{ route('podcast.index') }}" class="text-sm text-brand-400 hover:text-brand-300 transition-colors">View all →</a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            {{-- Coffee podcast --}}
            <div class="fade-up relative rounded-xl p-8 overflow-hidden border border-brand-600/30" style="background: linear-gradient(135deg, rgba(74,127,191,0.15), rgba(13,17,23,0.9));">
                <div class="flex items-start justify-between mb-4">
                    <div class="text-4xl">☕</div>
                    <div class="flex items-end gap-1 h-8">
                        <span class="eq-bar bg-brand-400"></span>
                        <span class="eq-bar bg-brand-400"></span>
                        <span class="eq-bar bg-brand-400"></span>
                        <span class="eq-bar bg-brand-400"></span>
                        <span class="eq-bar bg-brand-400"></span>
                    </div>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">Coffee With The Laravel Architect</h3>
                <p class="text-gray-400 text-sm mb-5">Conversations about Laravel, web development, and the developer life — one cup at a time.</p>
                <a href="{{ route('podcast.index') }}" class="text-sm text-brand-400 hover:text-brand-300 font-medium transition-colors">Listen now →</a>
            </div>
            {{-- Cloudy Days podcast --}}
            <div class="fade-up relative rounded-xl p-8 overflow-hidden border border-accent-600/30" style="background: linear-gradient(135deg, rgba(196,112,136,0.12), rgba(13,17,23,0.9));">
                <div class="flex items-start justify-between mb-4">
                    <div class="text-4xl">🌧️</div>
                    <div class="flex items-end gap-1 h-8">
                        <span class="eq-bar bg-accent-500"></span>
                        <span class="eq-bar bg-accent-500"></span>
                        <span class="eq-bar bg-accent-500"></span>
                        <span class="eq-bar bg-accent-500"></span>
                        <span class="eq-bar bg-accent-500"></span>
                    </div>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">Embracing Cloudy Days</h3>
                <p class="text-gray-400 text-sm mb-5">Real talk about mental health, burnout, work-life balance, and finding your way through the fog.</p>
                <a href="{{ route('podcast.index') }}" class="text-sm text-accent-500 hover:text-accent-400 font-medium transition-colors">Listen now →</a>
            </div>
        </div>
    </div>
</section>

{{-- ===== YOUTUBE ===== --}}
<section class="py-20">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold text-white mb-4">Watch on YouTube</h2>
        <p class="text-gray-400 mb-10 max-w-xl mx-auto">
            Tutorials, live coding, and conversations about Laravel and web development.
        </p>
        <div class="browser-frame mx-auto">
            {{-- Browser chrome --}}
            <div class="bg-brand-900 px-4 py-3 flex items-center gap-3 border-b border-white/10">
                <div class="browser-dots flex gap-2">
                    <span class="bg-red-500/80"></span>
                    <span class="bg-yellow-500/80"></span>
                    <span class="bg-green-500/80"></span>
                </div>
                <div class="flex-1 bg-brand-800 rounded-md px-3 py-1 text-xs text-gray-500 text-left truncate">
                    youtube.com/@thelaravelarchitect
                </div>
            </div>
            <div class="aspect-video bg-brand-800">
                <img src="/images/social-banner.jpg" alt="The Laravel Architect YouTube" class="w-full h-full object-cover">
            </div>
        </div>
        <div class="mt-8">
            <a href="https://youtube.com/channel/UC42H30o7l5QvvCzC86dSu_A" target="_blank" class="glow-btn inline-flex items-center px-8 py-3.5 bg-red-600 hover:bg-red-500 text-white font-semibold rounded-lg transition-all" style="--tw-shadow-color: rgba(239,68,68,0.3);">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                Subscribe
            </a>
        </div>
    </div>
</section>

{{-- ===== FINAL CTA ===== --}}
<section class="relative overflow-hidden" style="background: linear-gradient(135deg, #4A7FBF, #E47A9D);">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center relative z-10">
        <h2 class="text-3xl sm:text-4xl font-bold text-white mb-4">Need a Laravel Developer?</h2>
        <p class="text-white/80 mb-8 max-w-xl mx-auto text-lg">
            I'm available for freelance projects, consulting, and collaborations. Let's build something great together.
        </p>
        <a href="{{ route('contact') }}" class="inline-flex items-center px-10 py-4 bg-white text-brand-600 font-bold rounded-lg hover:bg-gray-100 transition-colors text-lg">
            Get in Touch
        </a>
    </div>
</section>

<script>
// Typing effect
(function() {
    const phrases = ['elegant APIs', 'scalable apps', 'clean code', 'Filament dashboards'];
    const el = document.getElementById('typed-text');
    let phraseIdx = 0, charIdx = 0, deleting = false;

    function tick() {
        const current = phrases[phraseIdx];
        if (!deleting) {
            el.textContent = current.substring(0, ++charIdx);
            if (charIdx === current.length) { setTimeout(() => { deleting = true; tick(); }, 2000); return; }
        } else {
            el.textContent = current.substring(0, --charIdx);
            if (charIdx === 0) { deleting = false; phraseIdx = (phraseIdx + 1) % phrases.length; }
        }
        setTimeout(tick, deleting ? 40 : 80);
    }
    tick();
})();

// IntersectionObserver for fade-up + count-up
(function() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                e.target.classList.add('visible');
                observer.unobserve(e.target);
            }
        });
    }, { threshold: 0.1 });
    document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));

    // Count-up
    const countObserver = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                const target = +e.target.dataset.target;
                let current = 0;
                const step = () => {
                    current += Math.ceil(target / 30);
                    if (current >= target) { e.target.textContent = target; return; }
                    e.target.textContent = current;
                    requestAnimationFrame(step);
                };
                step();
                countObserver.unobserve(e.target);
            }
        });
    }, { threshold: 0.5 });
    document.querySelectorAll('.count-up').forEach(el => countObserver.observe(el));
})();
</script>
@endsection
