@props(['post', 'showCategory' => true, 'showTags' => true, 'showExcerpt' => true, 'editorial' => false, 'priority' => false])

<article @class([
    'blog-card group',
    'blog-card--editorial' => $editorial,
    'grid gap-5 border-b border-gray-200 py-8 dark:border-[#1e2a3a] sm:grid-cols-[13rem_minmax(0,1fr)] sm:items-start sm:gap-7' => ! $editorial,
])>
    <a href="{{ route('blog.show', $post) }}" aria-hidden="true" tabindex="-1" class="block">
        <x-post-artwork
            :post="$post"
            :priority="$priority"
            :sizes="$editorial ? '(min-width: 1024px) 58vw, calc(100vw - 2rem)' : '(min-width: 640px) 208px, calc(100vw - 2rem)'"
            @class([
                'bg-gray-100 outline-1 -outline-offset-1 outline-black/5 dark:bg-brand-900 dark:outline-white/10',
                'blog-card__artwork aspect-[4/3] rounded-lg' => $editorial,
                'aspect-[3/2] rounded-lg' => ! $editorial,
            ])
        />
    </a>

    <div @class(['blog-card__content' => $editorial])>
        <x-post-meta :post="$post" :showCategory="$showCategory" :editorial="$editorial" class="mb-3" />
        <a href="{{ route('blog.show', $post) }}" class="focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-4 focus-visible:ring-offset-white dark:focus-visible:ring-offset-brand-950">
            <h2 @class([
                'mb-3 text-balance font-semibold text-gray-900 group-hover:text-brand-600 dark:text-gray-100',
                'blog-card__title text-2xl tracking-[-0.025em] md:text-3xl' => $editorial,
                'text-xl md:text-2xl' => ! $editorial,
            ])>{{ $post->title }}</h2>
        </a>

        @if($showExcerpt && $post->excerpt)
        <p @class([
            'mb-4 text-pretty text-gray-600 dark:text-gray-400',
            'blog-card__excerpt line-clamp-3 text-base' => $editorial,
            'line-clamp-2 text-sm leading-relaxed' => ! $editorial,
        ])>{{ $post->excerpt }}</p>
        @endif

        @if($showTags && $post->tags && $post->tags->isNotEmpty())
        <div class="flex flex-wrap items-center gap-2">
            @foreach($post->tags as $tag)
            <x-tag-pill :tag="$tag" />
            @endforeach
        </div>
        @endif

        @if($editorial)
            <span class="blog-card__link inline-flex items-center gap-2 text-sm font-semibold text-brand-600" aria-hidden="true">
                Read article
                <x-svg-icon name="arrow-right" class="h-4 w-4" />
            </span>
        @endif
    </div>
</article>
