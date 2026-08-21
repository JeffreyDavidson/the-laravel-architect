@props([
    'years',
    'publishedPosts',
    'publishedProjects',
    'recommendations',
])

@php
    $proofPoints = [
        ['value' => $years, 'suffix' => '+', 'label' => 'Years building PHP'],
        ['value' => $publishedPosts, 'suffix' => '', 'label' => 'Published articles'],
        ['value' => $publishedProjects, 'suffix' => '', 'label' => 'Portfolio projects'],
        ['value' => $recommendations, 'suffix' => '', 'label' => 'Recommendations'],
    ];
@endphp

<section {{ $attributes->class(['border-y border-gray-200 bg-white py-10 dark:border-brand-800/50 dark:bg-transparent sm:py-14']) }} aria-label="Experience and published work">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <dl class="grid grid-cols-2 gap-x-6 gap-y-8 sm:grid-cols-4 sm:gap-8">
            @foreach($proofPoints as $point)
                <div class="text-center">
                    <dd class="stat-number tabular-nums">
                        <span class="count-up" data-target="{{ $point['value'] }}">{{ $point['value'] }}</span>{{ $point['suffix'] }}
                    </dd>
                    <dt class="mt-2 text-sm font-medium text-gray-600 dark:text-gray-400">
                        {{ $point['label'] }}
                    </dt>
                </div>
            @endforeach
        </dl>
    </div>
</section>
