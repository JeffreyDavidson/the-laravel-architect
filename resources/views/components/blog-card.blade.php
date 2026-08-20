@props(['post', 'showCategory' => true, 'showTags' => true, 'showExcerpt' => true])
<div class="blog-card group overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-brand-700 dark:bg-brand-950">
    <div class="p-6 md:p-8">
        <x-post-meta :post="$post" :showCategory="$showCategory" class="mb-3" />

        <a href="{{ route('blog.show', $post) }}" class="focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-4 focus-visible:ring-offset-white dark:focus-visible:ring-offset-brand-950">
            <h2 class="mb-3 text-xl font-bold text-gray-900 transition-colors group-hover:text-brand-600 dark:text-white md:text-2xl">{{ $post->title }}</h2>
        </a>

        @if($showExcerpt && $post->excerpt)
        <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed line-clamp-2 mb-4">{{ $post->excerpt }}</p>
        @endif

        @if($showTags && $post->tags && $post->tags->isNotEmpty())
        <div class="flex flex-wrap items-center gap-2">
            @foreach($post->tags as $tag)
            <x-tag-pill :tag="$tag" />
            @endforeach
        </div>
        @endif
    </div>
</div>
