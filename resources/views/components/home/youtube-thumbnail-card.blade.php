@props(['video'])

<a
    href="{{ $video->youtube_url }}"
    target="_blank"
    rel="noopener noreferrer"
    {{ $attributes->class('thumbnail-card block overflow-hidden rounded-xl transition-transform hover:-translate-y-1') }}
>
    <div class="relative aspect-video overflow-hidden bg-gray-100 dark:bg-[#111111]">
        @if($video->thumbnail_url)
            <img src="{{ $video->thumbnail_url }}" alt="{{ $video->title }}" class="h-full w-full object-cover">
        @else
            <div class="flex h-full items-center justify-center">
                <img src="/images/logo-color.svg" alt="" class="h-16 w-16 opacity-60">
            </div>
        @endif

        @if($video->formatted_duration)
            <div class="absolute right-2 bottom-2 rounded bg-black/80 px-1.5 py-0.5 font-mono text-[10px] text-white">
                {{ $video->formatted_duration }}
            </div>
        @endif
    </div>

    <div class="p-4">
        <p class="mb-1 line-clamp-2 text-sm font-bold text-gray-900 dark:text-white">{{ $video->title }}</p>
        <p class="text-[11px] text-gray-500">The Laravel Architect · {{ $video->published_at->format('M j, Y') }}</p>
    </div>
</a>
