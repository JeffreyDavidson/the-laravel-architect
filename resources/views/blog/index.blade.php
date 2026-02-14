@extends('layouts.app')

@section('title', 'Blog')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <h1 class="text-3xl font-bold mb-2">Blog</h1>
        <p class="text-gray-600 dark:text-gray-400 mb-10">Thoughts on Laravel, PHP, web development, and building things that matter.</p>

        <div class="space-y-8">
            @forelse($posts as $post)
            <article class="group flex gap-6">
                <a href="{{ route('blog.show', $post) }}" class="hidden sm:block flex-shrink-0 w-48 h-32 rounded-lg overflow-hidden">
                    @if($post->featured_image)
                    <img src="{{ Storage::url($post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    @else
                    @php $catSlug = $post->category?->slug ?? 'default'; $catInitial = strtoupper(substr($post->category?->name ?? 'B', 0, 1)); @endphp
                    <div class="w-full h-full post-art post-art-{{ $catSlug }} relative">
                        <div class="post-art-orb" style="width:100px;height:100px;background:var(--art-color);top:-20px;right:-15px;"></div>
                        <div class="post-art-shape rounded-lg" style="width:30px;height:30px;border-color:var(--art-color);top:25%;left:20%;transform:rotate(15deg);"></div>
                        <div class="post-art-shape rounded-full" style="width:20px;height:20px;border-color:var(--art-color2);bottom:20%;right:25%;"></div>
                        <span class="post-art-mono" style="color:var(--art-color);font-size:4rem;bottom:-0.5rem;right:0.5rem;">{{ $catInitial }}</span>
                    </div>
                    @endif
                </a>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-3 mb-2">
                        @if($post->category)
                        <a href="{{ route('blog.category', $post->category) }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wide hover:underline">{{ $post->category->name }}</a>
                        <span class="text-xs text-gray-500">·</span>
                        @endif
                        <span class="text-xs text-gray-500">{{ $post->published_at->format('M d, Y') }}</span>
                        <span class="text-xs text-gray-500">·</span>
                        <span class="text-xs text-gray-500">{{ $post->reading_time }} min read</span>
                    </div>
                    <a href="{{ route('blog.show', $post) }}">
                        <h2 class="font-semibold text-xl mb-2 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ $post->title }}</h2>
                    </a>
                    <p class="text-gray-600 dark:text-gray-400 text-sm line-clamp-2 mb-3">{{ $post->excerpt }}</p>
                    <div class="flex items-center gap-2">
                        @foreach($post->tags as $tag)
                        <a href="{{ route('blog.tag', $tag) }}" class="px-2 py-0.5 bg-gray-100 dark:bg-gray-800 text-xs rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">{{ $tag->name }}</a>
                        @endforeach
                    </div>
                </div>
            </article>
            @empty
            <div class="text-center py-20 text-gray-500">
                <p class="text-lg">No posts yet. Check back soon!</p>
            </div>
            @endforelse

            <div class="pt-4">
                {{ $posts->links() }}
            </div>
        </div>
    </div>
@endsection
