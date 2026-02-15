@extends('layouts.app')

@section('title', "Tagged: {$tag->name}")

@section('content')
<style>
    .noise-overlay { position: relative; }
    .noise-overlay::after {
        content: ''; position: absolute; inset: 0; opacity: 0.04; pointer-events: none; z-index: 1;
        background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
        background-repeat: repeat; background-size: 256px 256px;
    }
    .dot-grid-bg { position: relative; }
    .dot-grid-bg::before {
        content: ''; position: absolute; inset: 0; opacity: 0.03; pointer-events: none;
        background-image: radial-gradient(circle, #ffffff 1px, transparent 1px); background-size: 24px 24px; z-index: 0;
    }
    .dot-grid-bg > * { position: relative; z-index: 1; }
    .blog-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .blog-card:hover { transform: translateY(-4px); box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.4); border-color: #4A7FBF33; }
</style>

{{-- Hero --}}
<div class="noise-overlay relative overflow-hidden border-b border-[#1e2a3a]">
    <div class="absolute top-1/3 left-1/4 w-[500px] h-[500px] rounded-full opacity-[0.06] blur-[120px]" style="background: radial-gradient(circle, #4A7FBF, transparent 70%);"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-20">
        <a href="{{ route('blog.index') }}" class="text-sm text-[#4A7FBF] hover:text-[#5A8FD0] transition-colors mb-4 inline-flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            All Posts
        </a>
        <h1 class="text-4xl md:text-5xl font-extrabold mb-4">Tagged: <span class="text-[#4A7FBF]">{{ $tag->name }}</span></h1>
    </div>
</div>

{{-- Posts --}}
<div class="dot-grid-bg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
        <div class="space-y-6">
            @forelse($posts as $post)
            <a href="{{ route('blog.show', $post) }}" class="blog-card group block rounded-2xl border border-[#1e2a3a] bg-[#0D1117] overflow-hidden">
                <div class="p-6 md:p-8">
                    <div class="flex items-center gap-3 mb-3">
                        @if($post->category)
                        <span class="text-xs font-semibold uppercase tracking-wider" style="color: #4A7FBF;">{{ $post->category->name }}</span>
                        <span class="text-gray-700">·</span>
                        @endif
                        <span class="text-xs text-gray-500">{{ $post->published_at->format('M d, Y') }}</span>
                    </div>
                    <h2 class="text-xl md:text-2xl font-bold text-white mb-3 group-hover:text-[#4A7FBF] transition-colors">{{ $post->title }}</h2>
                    <p class="text-gray-400 text-sm leading-relaxed line-clamp-2">{{ $post->excerpt }}</p>
                </div>
            </a>
            @empty
            <div class="text-center py-20 text-gray-500">No posts with this tag yet.</div>
            @endforelse

            @if($posts->hasPages())
            <div class="pt-8">{{ $posts->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
