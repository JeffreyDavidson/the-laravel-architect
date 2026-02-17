@props(['post', 'showCategory' => true])
<div {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-3']) }}>
    @if($showCategory && $post->category)
    <span class="text-xs font-semibold uppercase tracking-wider" style="color: #4A7FBF;">{{ $post->category->name }}</span>
    <span class="text-gray-300 dark:text-gray-700">·</span>
    @endif
    <span class="text-xs text-gray-500">{{ $post->published_at->format('M d, Y') }}</span>
    <span class="text-gray-300 dark:text-gray-700">·</span>
    <span class="text-xs text-gray-500">{{ $post->reading_time }} min read</span>
</div>
