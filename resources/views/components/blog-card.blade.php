{{--
    A post card. The variant picks the layout:
    - list: the blog's bordered row with a small 3:2 thumbnail (the default);
    - editorial: the larger 4:3 card with a "Read article" link (the older `editorial` flag still selects it);
    - featured: the homepage's lead card, artwork beside the copy;
    - compact: the homepage's smaller cards under the lead;
    - related: the "Continue reading" cards under an article.
    showCategory, showTags and showExcerpt apply to the list and editorial layouts. Other attributes,
    such as reveal hooks, go on the article.
--}}
@props([
    'post',
    'variant' => null,
    'showCategory' => true,
    'showTags' => true,
    'showExcerpt' => true,
    'editorial' => false,
    'priority' => false,
])

@php
    $variant ??= $editorial ? 'editorial' : 'list';
@endphp

@if (in_array($variant, ['list', 'editorial'], true))
    @php
        $editorial = $variant === 'editorial';
    @endphp

    <article {{
        $attributes->class([
            'group flex h-full min-w-0 flex-col gap-5',
            'grid gap-5 border-b border-gray-200 py-8 dark:border-surface-border sm:grid-cols-[13rem_minmax(0,1fr)] sm:items-start sm:gap-7' => ! $editorial,
        ])
    }}>
        <a href="{{ route('blog.show', $post) }}" aria-hidden="true" tabindex="-1" class="block">
            <x-post-artwork
                :post="$post"
                :priority="$priority"
                :sizes="$editorial ? '(min-width: 1024px) 58vw, calc(100vw - 2rem)' : '(min-width: 640px) 208px, calc(100vw - 2rem)'"
                @class([
                    'bg-gray-100 outline-1 -outline-offset-1 outline-black/5 dark:bg-brand-900 dark:outline-white/10',
                    'aspect-[4/3] rounded-lg [&_img]:transition-transform [&_img]:duration-450 [&_img]:ease-[cubic-bezier(0.16,1,0.3,1)] group-hover:[&_img]:scale-[1.025] group-focus-within:[&_img]:scale-[1.025] motion-reduce:[&_img]:transition-none' => $editorial,
                    'aspect-[3/2] rounded-lg' => ! $editorial,
                ])
            />
        </a>

        <div @class(['flex flex-1 flex-col' => $editorial])>
            <x-post-meta :post="$post" :showCategory="$showCategory" :editorial="$editorial" class="mb-3" />
            <a
                href="{{ route('blog.show', $post) }}"
                class="focus-visible:ring-brand-500 dark:focus-visible:ring-offset-brand-950 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-4 focus-visible:ring-offset-white"
            >
                <h2 @class([
                    'mb-3 text-balance font-semibold text-gray-900 group-hover:text-brand-action dark:group-hover:text-brand-600 dark:text-gray-100',
                    'line-clamp-2 text-2xl tracking-[-0.025em] md:text-3xl transition-colors duration-200 motion-reduce:transition-none' => $editorial,
                    'text-xl md:text-2xl' => ! $editorial,
                ])>
                    {{ $post->title }}
                </h2>
            </a>

            @if ($showExcerpt && $post->excerpt)
                <p @class([
                    'mb-4 text-pretty text-gray-600 dark:text-gray-400',
                    'line-clamp-3 text-base' => $editorial,
                    'line-clamp-2 text-sm leading-relaxed' => ! $editorial,
                ])>
                    {{ $post->excerpt }}
                </p>
            @endif

            @if ($showTags && $post->tags && $post->tags->isNotEmpty())
                <div class="flex flex-wrap items-center gap-2">
                    @foreach ($post->tags as $tag)
                        <x-tag-pill :tag="$tag" />
                    @endforeach
                </div>
            @endif

            @if ($editorial)
                <a
                    href="{{ route('blog.show', $post) }}"
                    aria-label="Read article: {{ $post->title }}"
                    class="focus-visible:ring-brand-500 dark:focus-visible:ring-offset-brand-950 text-brand-action dark:text-brand-600 mt-auto inline-flex items-center gap-2 rounded-sm text-sm font-semibold transition-colors duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-4 focus-visible:ring-offset-white motion-reduce:transition-none"
                >
                    Read article
                    <x-svg-icon
                        name="arrow-right"
                        class="h-4 w-4 transition-transform duration-200 group-focus-within:translate-x-1 group-hover:translate-x-1 motion-reduce:transition-none"
                    />
                </a>
            @endif
        </div>
    </article>
@else
    @php
        $card = match ($variant) {
            'featured' => [
                'article' => 'blog-featured group hover:border-brand-600/40 dark:border-brand-800/50 dark:bg-brand-900/60 overflow-hidden rounded-xl border border-gray-200 bg-white transition-colors duration-200',
                'link' => 'grid md:grid-cols-[minmax(0,1.25fr)_minmax(20rem,0.75fr)]',
                'sizes' => '(min-width: 1024px) 720px, calc(100vw - 2rem)',
                'artwork' => '[&_img]:h-full [&_img]:w-full [&_img]:object-cover bg-surface-media block min-h-60 overflow-hidden md:min-h-[27rem]',
                'body' => 'flex flex-col justify-center p-7 sm:p-9',
                'category' => 'text-brand-400 text-xs font-semibold tracking-wide uppercase',
                'title' => 'group-hover:text-brand-action dark:group-hover:text-brand-400 mt-2 mb-4 text-2xl font-semibold text-gray-900 transition-colors md:text-3xl dark:text-white',
                'excerpt' => 'line-clamp-3 max-w-3xl text-base text-gray-600 dark:text-gray-400',
                'meta' => 'mt-5 flex items-center gap-3 text-xs text-gray-500',
            ],
            'compact' => [
                'article' => 'group hover:border-brand-600/40 dark:border-brand-800/50 dark:bg-brand-900/60 overflow-hidden rounded-xl border border-gray-200 bg-white transition-colors duration-200',
                'link' => '[&>div]:p-4 md:[&>div]:p-6 grid h-full grid-cols-[minmax(6rem,0.65fr)_minmax(0,1.35fr)] md:grid-cols-[minmax(9rem,0.7fr)_minmax(0,1.3fr)]',
                'sizes' => '(min-width: 640px) 280px, calc(100vw - 2rem)',
                'artwork' => '[&_img]:h-full [&_img]:w-full [&_img]:object-cover bg-surface-media block overflow-hidden',
                'body' => 'p-6',
                'category' => 'text-brand-400 text-xs font-semibold tracking-wide uppercase',
                'title' => 'group-hover:text-brand-action dark:group-hover:text-brand-400 mt-2 mb-3 text-lg font-semibold text-gray-900 transition-colors dark:text-white',
                'excerpt' => 'line-clamp-2 hidden text-sm text-gray-600 md:block dark:text-gray-400',
                'meta' => 'mt-4 flex items-center gap-3 text-xs text-gray-500',
            ],
            'related' => [
                'article' => 'group dark:bg-brand-900/60 overflow-hidden rounded-xl bg-white outline-1 -outline-offset-1 outline-black/5 dark:outline-white/10',
                'link' => 'focus-visible:outline-brand-500 block focus-visible:outline-2 focus-visible:outline-offset-4',
                'sizes' => '(min-width: 768px) 560px, calc(100vw - 2rem)',
                'artwork' => 'dark:bg-brand-900 aspect-[3/2] bg-gray-100',
                'body' => 'p-6',
                'category' => 'text-brand-600 dark:text-brand-300 font-mono text-sm font-semibold tracking-wide uppercase',
                'title' => 'group-hover:text-brand-action dark:group-hover:text-brand-300 mt-2 text-xl font-semibold tracking-tight text-gray-950 transition-colors dark:text-white',
                'excerpt' => 'mt-3 line-clamp-2 text-base text-pretty text-gray-600 dark:text-gray-400',
                'meta' => 'mt-5 text-sm text-gray-500 dark:text-gray-400',
            ],
        };
    @endphp

    <article {{ $attributes->class($card['article']) }}>
        <a href="{{ route('blog.show', $post) }}" @class([$card['link']])>
            <x-post-artwork :post="$post" :sizes="$card['sizes']" @class([$card['artwork']]) />
            <div @class([$card['body']])>
                @if ($post->category)
                    <span @class([$card['category']])>{{ $post->category->name }}</span>
                @endif
                <h3 @class([$card['title']])>{{ $post->title }}</h3>
                <p @class([$card['excerpt']])>{{ $post->excerpt }}</p>
                <div @class([$card['meta']])>
                    @if ($variant === 'related')
                        {{ \App\Presenters\PostPresenter::from($post)->readingTime() }} min read
                    @else
                        <x-display-date :date="$post->published_at" />
                        <span>·</span>
                        <span>{{ \App\Presenters\PostPresenter::from($post)->readingTime() }} min read</span>
                    @endif
                </div>
            </div>
        </a>
    </article>
@endif
