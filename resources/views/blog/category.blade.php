@extends('layouts.app')

@section('title', $category->name)

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <h1 class="text-3xl font-bold mb-2">{{ $category->name }}</h1>
        @if($category->description)
        <p class="text-gray-600 dark:text-gray-400 mb-10">{{ $category->description }}</p>
        @endif

        <div class="space-y-8">
            @forelse($posts as $post)
            <article class="group">
                <a href="{{ route('blog.show', $post) }}">
                    <h2 class="font-semibold text-xl mb-2 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ $post->title }}</h2>
                </a>
                <p class="text-gray-600 dark:text-gray-400 text-sm line-clamp-2 mb-2">{{ $post->excerpt }}</p>
                <div class="flex items-center gap-3 text-xs text-gray-500">
                    <time>{{ $post->published_at->format('M d, Y') }}</time>
                    <span>·</span>
                    <span>{{ $post->reading_time }} min read</span>
                </div>
            </article>
            @empty
            <p class="text-gray-500">No posts in this category yet.</p>
            @endforelse
        </div>

        <div class="mt-8">{{ $posts->links() }}</div>
    </div>
@endsection
