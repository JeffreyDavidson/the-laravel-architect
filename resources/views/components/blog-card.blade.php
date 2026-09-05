@props(['post', 'showCategory' => true, 'showTags' => true, 'showExcerpt' => true])
<article class="blog-card group grid gap-5 border-b border-gray-200 py-8 dark:border-[#1e2a3a] sm:grid-cols-[13rem_minmax(0,1fr)] sm:items-start sm:gap-7">
    <a href="{{ route('blog.show', $post) }}" class="block focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-4 focus-visible:ring-offset-gray-50 dark:focus-visible:ring-offset-[#0b1016]">
        <x-post-artwork
            :post="$post"
            sizes="(min-width: 640px) 208px, calc(100vw - 2rem)"
            class="aspect-[3/2] rounded-lg bg-gray-100 outline-1 -outline-offset-1 outline-black/5 dark:bg-brand-900 dark:outline-white/10"
        />
    </a>

    <div>
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
</article>
