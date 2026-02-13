@extends('layouts.app')

@section('title', "Tagged: {$tag->name}")

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <h1 class="text-3xl font-bold mb-10">Tagged: <span class="text-indigo-600 dark:text-indigo-400">{{ $tag->name }}</span></h1>

        <div class="space-y-8">
            @forelse($posts as $post)
            <article class="group">
                <a href="{{ route('blog.show', $post) }}">
                    <h2 class="font-semibold text-xl mb-2 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ $post->title }}</h2>
                </a>
                <p class="text-gray-600 dark:text-gray-400 text-sm line-clamp-2 mb-2">{{ $post->excerpt }}</p>
                <div class="flex items-center gap-3 text-xs text-gray-500">
                    @if($post->category)
                    <span>{{ $post->category->name }}</span>
                    <span>·</span>
                    @endif
                    <time>{{ $post->published_at->format('M d, Y') }}</time>
                </div>
            </article>
            @empty
            <p class="text-gray-500">No posts with this tag yet.</p>
            @endforelse
        </div>

        <div class="mt-8">{{ $posts->links() }}</div>
    </div>
@endsection
