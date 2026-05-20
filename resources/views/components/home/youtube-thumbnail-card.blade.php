@props([
    'variant',
    'thumbnail',
    'imageAlt',
    'badge',
    'previewTitle',
    'previewSubtitle',
    'duration',
    'title',
    'meta',
])

<div {{ $attributes->class([
    'thumbnail-card cursor-default overflow-hidden rounded-xl',
    'yt-thumbnail-card-'.$variant,
]) }}>
    <div class="relative aspect-video overflow-hidden">
        <img src="{{ $thumbnail }}" alt="{{ $imageAlt }}" class="hidden h-full w-full object-cover dark:block">

        <div class="yt-thumbnail-light relative h-full w-full dark:hidden">
            <div class="absolute inset-0 flex flex-col justify-between p-5">
                <div class="flex items-center justify-between">
                    <span class="yt-thumbnail-badge rounded-full border px-2 py-0.5 text-[9px] font-bold uppercase tracking-widest">{{ $badge }}</span>
                    <svg class="yt-thumbnail-play h-10 w-10" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                </div>

                <div>
                    <p class="yt-thumbnail-title text-xl font-extrabold leading-tight">
                        @foreach($previewTitle as $line)
                            {{ $line }}@if(! $loop->last)<br>@endif
                        @endforeach
                    </p>
                    <p class="yt-thumbnail-subtitle mt-1 text-xs">{{ $previewSubtitle }}</p>
                </div>
            </div>
        </div>

        <div class="yt-duration-badge absolute bottom-2 right-2 rounded bg-black/80 px-1.5 py-0.5 font-mono text-[10px] text-white dark:bg-black/80 dark:text-gray-400">{{ $duration }}</div>
    </div>

    <div class="p-4">
        <p class="mb-1 line-clamp-2 text-sm font-bold text-gray-900 dark:text-white">{{ $title }}</p>
        <p class="text-[11px] text-gray-500">{{ $meta }}</p>
    </div>
</div>
