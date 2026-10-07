@props([
    'post',
    'sizes' => '100vw',
    'priority' => false,
])

@php($artwork = \App\Presenters\PostPresenter::from($post)->artwork())

@if ($artwork)
    <picture {{ $attributes->class('block overflow-hidden') }} data-post-artwork="{{ $post->slug }}">
        @if ($artwork->srcset)
            <source type="image/webp" srcset="{{ $artwork->srcset }}" sizes="{{ $sizes }}" />
        @endif
        <img
            src="{{ $artwork->src }}"
            alt=""
            decoding="async"
            @if ($priority) fetchpriority="high" @else loading="lazy" @endif
            class="h-full w-full object-cover"
        />
    </picture>
@endif
