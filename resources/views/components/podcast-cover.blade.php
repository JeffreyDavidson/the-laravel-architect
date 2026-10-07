@props([
    'podcast',
    'alt' => null,
    'sizes',
    'width',
    'height',
    'priority' => false,
])

@php($cover = \App\Presenters\PodcastPresenter::from($podcast)->cover())

@if ($cover)
    <picture>
        @if ($cover->srcset)
            <source type="image/webp" srcset="{{ $cover->srcset }}" sizes="{{ $sizes }}" />
        @endif
        <img
            src="{{ $cover->src }}"
            alt="{{ $alt ?? $podcast->name }}"
            width="{{ $width }}"
            height="{{ $height }}"
            decoding="async"
            @if ($priority)
                fetchpriority="high"
            @else
                loading="lazy"
            @endif
            {{ $attributes }}
        />
    </picture>
@endif
