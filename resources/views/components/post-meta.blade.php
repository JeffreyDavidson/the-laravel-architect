@props(['post', 'showCategory' => true])
<div {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-3']) }}>
    @if($showCategory && $post->category)
    <span class="text-xs font-semibold uppercase tracking-wider text-brand-600">{{ $post->category->name }}</span>
    <span class="text-gray-300 dark:text-gray-700">·</span>
    @endif
    <time datetime="{{ $post->published_at->toDateString() }}" class="text-xs text-gray-500">{{ $post->published_at->format('M d, Y') }}</time>
    <span class="text-gray-300 dark:text-gray-700">·</span>
    <span class="text-xs text-gray-500">{{ $post->reading_time }} min read</span>
</div>
