@extends('layouts.app')

@push('head')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css" rel="stylesheet" />
    <style>
        pre { position: relative; }
        .copy-btn {
            position: absolute; top: 8px; right: 8px;
            padding: 6px; border-radius: 6px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.4);
            cursor: pointer; opacity: 0;
            transition: all 0.2s ease;
            z-index: 10;
        }
        pre:hover .copy-btn { opacity: 1; }
        .copy-btn:hover { background: rgba(255,255,255,0.15); color: rgba(255,255,255,0.8); }
        .copy-btn.copied { color: #4ade80; border-color: rgba(74,222,128,0.3); background: rgba(74,222,128,0.1); }
    </style>
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-php.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-bash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-json.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-markup-templating.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-css.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('pre').forEach(function(pre) {
                pre.style.position = 'relative';
                const btn = document.createElement('button');
                btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>';
                btn.className = 'copy-btn';
                btn.title = 'Copy code';
                btn.addEventListener('click', function() {
                    const code = pre.querySelector('code');
                    navigator.clipboard.writeText(code.innerText).then(function() {
                        btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
                        btn.classList.add('copied');
                        setTimeout(function() {
                            btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>';
                            btn.classList.remove('copied');
                        }, 2000);
                    });
                });
                pre.appendChild(btn);
            });
        });
    </script>
@endpush

@section('content')
    <article class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        {{-- Header --}}
        <header class="mb-10">
            @if($post->category)
            <a href="{{ route('blog.category', $post->category) }}" class="text-sm font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wide hover:underline">{{ $post->category->name }}</a>
            @endif
            <h1 class="text-3xl sm:text-4xl font-bold mt-2 mb-4 text-gray-900 dark:text-white">{{ $post->title }}</h1>
            <div class="flex items-center gap-4 text-sm text-gray-500">
                <span>{{ $post->author->name ?? 'Jeffrey Davidson' }}</span>
                <span>·</span>
                <time>{{ $post->published_at->format('F d, Y') }}</time>
                <span>·</span>
                <span>{{ $post->reading_time }} min read</span>
            </div>
        </header>

        {{-- Featured Image --}}
        @if($post->hasMedia('featured_image'))
        <div class="rounded-xl overflow-hidden mb-10 bg-gray-100 dark:bg-gray-800">
            <img src="{{ $post->getFirstMediaUrl('featured_image') }}" alt="{{ $post->title }}" class="w-full">
        </div>
        @endif

        {{-- Content --}}
        <x-prose class="prose-a:text-brand-400 prose-code:text-brand-300">
            <x-markdown>{!! $post->content !!}</x-markdown>
        </x-prose>

        {{-- Tags --}}
        @if($post->tags->count())
        <div class="mt-10 pt-6 border-t border-gray-200 dark:border-gray-800">
            <div class="flex flex-wrap gap-2">
                @foreach($post->tags as $tag)
                <x-tag-pill :tag="$tag" />
                @endforeach
            </div>
        </div>
        @endif

        {{-- Related Posts --}}
        @if($relatedPosts->count())
        <div class="mt-16 pt-10 border-t border-gray-200 dark:border-gray-700/50">
            <h2 class="text-xl font-bold mb-8 text-gray-900 dark:text-white">Continue Reading</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                @foreach($relatedPosts as $related)
                <a href="{{ route('blog.show', $related) }}" class="group block bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700/50 rounded-xl p-5 hover:border-blue-500/30 hover:bg-gray-100 dark:hover:bg-gray-800/80 transition-all duration-300">
                    <div class="flex items-center gap-3 mb-3">
                        @if($related->category)
                        <span class="text-xs font-semibold uppercase tracking-wider text-blue-600 dark:text-blue-400">{{ $related->category->name }}</span>
                        @endif
                        <span class="text-xs text-gray-500">{{ $related->reading_time }} min read</span>
                    </div>
                    <h3 class="font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors leading-snug mb-2">{{ $related->title }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2">{{ $related->excerpt }}</p>
                    <div class="mt-3 text-xs text-gray-500">{{ $related->published_at->format('M d, Y') }}</div>
                </a>
                @endforeach
            </div>
        </div>
        @endif
    </article>
@endsection
