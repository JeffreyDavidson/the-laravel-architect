@props([
    'title',
    'description',
    'image',
    'imageAlt',
    'episodeCount' => null,
    'href',
])

<a href="{{ $href }}" {{ $attributes->class('podcast-card fade-up group relative overflow-hidden rounded-xl border border-brand-200 bg-white p-8 transition-[border-color,background-color,box-shadow] duration-300 hover:border-brand-600/50 hover:shadow-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-400 dark:border-brand-600/30 dark:bg-transparent') }}>
    <div class="mb-4 flex items-start justify-between">
        <img src="{{ $image }}" alt="{{ $imageAlt }}" width="64" height="64" loading="lazy" decoding="async" class="h-16 w-16 rounded-xl object-cover">

        <div class="flex h-8 items-end gap-1">
            @for($i = 0; $i < 5; $i++)
                <span class="eq-bar bg-brand-400"></span>
            @endfor
        </div>
    </div>

    <h3 class="mb-2 text-xl font-bold text-gray-900 transition-colors group-hover:text-brand-600 dark:text-white dark:group-hover:text-brand-300">{{ $title }}</h3>
    <p class="mb-3 text-sm text-gray-600 dark:text-gray-400">{{ $description }}</p>

    @if($episodeCount > 0)
        <span class="mb-2 inline-block text-xs text-brand-600 dark:text-brand-300">{{ $episodeCount }} episodes</span>
    @endif

    <span class="block text-sm font-medium text-brand-600 transition-colors group-hover:text-brand-500 dark:text-brand-400 dark:group-hover:text-brand-300">Listen now →</span>
</a>
