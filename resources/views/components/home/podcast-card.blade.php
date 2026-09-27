@props([
    'title',
    'description',
    'image',
    'imageAlt',
    'episodeCount' => null,
    'href',
])

<a
    data-reveal
    href="{{ $href }}"
    {{ $attributes->class('data-[reveal=pending]:translate-y-3 data-[reveal=pending]:opacity-0 motion-safe:data-[reveal]:transition-[opacity,transform,translate,border-color,background-color,box-shadow] motion-safe:data-[reveal]:duration-450 motion-safe:data-[reveal]:ease-[ease] group relative overflow-hidden rounded-xl border border-brand-200 border-t-[3px] border-t-brand-600 bg-white p-8 transition-[border-color,background-color,box-shadow] duration-300 hover:border-brand-600/50 hover:shadow-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-400 dark:border-brand-600/30 dark:border-t dark:bg-transparent') }}
>
    <div class="mb-4 flex items-start justify-between">
        <img
            src="{{ $image }}"
            alt="{{ $imageAlt }}"
            width="64"
            height="64"
            loading="lazy"
            decoding="async"
            class="h-16 w-16 rounded-xl object-cover"
        />

        <div class="flex h-8 items-end gap-1">
            @for ($i = 0; $i < 5; $i++)
                <span
                    @class([
                        'inline-block w-[3px] origin-bottom rounded-[2px] align-bottom bg-brand-400 motion-reduce:animate-none',
                        'h-6 [transform:scaleY(0.3333)] animate-[eq-1_1.2s_ease-in-out_infinite]' => $i === 0,
                        'h-4 animate-[eq-2_1s_ease-in-out_infinite_0.1s]' => $i === 1,
                        'h-7 [transform:scaleY(0.4286)] animate-[eq-3_1.4s_ease-in-out_infinite_0.2s]' => $i === 2,
                        'h-5 animate-[eq-4_0.9s_ease-in-out_infinite_0.3s]' => $i === 3,
                        'h-[22px] [transform:scaleY(0.2727)] animate-[eq-5_1.1s_ease-in-out_infinite_0.15s]' => $i === 4,
                    ])
                ></span>
            @endfor
        </div>
    </div>

    <h3 class="group-hover:text-brand-600 dark:group-hover:text-brand-300 mb-2 text-xl font-bold text-gray-900 transition-colors dark:text-white">
        {{ $title }}
    </h3>
    <p class="mb-3 text-sm text-gray-600 dark:text-gray-400">{{ $description }}</p>

    @if ($episodeCount > 0)
        <span class="text-brand-600 dark:text-brand-300 mb-2 inline-block text-xs">{{ $episodeCount }} episodes</span>
    @endif

    <span class="text-brand-600 group-hover:text-brand-500 dark:text-brand-400 dark:group-hover:text-brand-300 block text-sm font-medium transition-colors">Listen now →</span>
</a>
