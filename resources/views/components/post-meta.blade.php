@props(['post', 'showCategory' => true, 'editorial' => false])
<div {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-3']) }}>
    @if($showCategory && $post->category)
        <span class="text-xs font-semibold uppercase tracking-wider text-brand-600">{{ $post->category->name }}</span>
        @unless($editorial)
            <span class="text-gray-300 dark:text-gray-700">·</span>
        @endunless
    @endif
    <time datetime="{{ $post->published_at->toDateString() }}" @class([
        'text-xs text-gray-500',
        'border-l border-gray-300 pl-3 dark:border-gray-700' => $editorial && $showCategory && $post->category,
    ])>{{ $post->published_at->format('M d, Y') }}</time>
    @unless($editorial)
        <span class="text-gray-300 dark:text-gray-700">·</span>
    @endunless
    <span @class([
        'text-xs text-gray-500',
        'border-l border-gray-300 pl-3 dark:border-gray-700' => $editorial,
    ])>{{ $post->reading_time }} min read</span>
</div>
