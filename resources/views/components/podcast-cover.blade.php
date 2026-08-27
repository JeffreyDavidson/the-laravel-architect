@props([
    'podcast',
    'alt' => null,
    'sizes',
    'width',
    'height',
    'priority' => false,
])

@inject('responsiveImages', 'App\Services\ResponsiveImageVariants')

@php($srcset = $responsiveImages->srcset($podcast->cover_image_path))

<picture>
    @if($srcset)
        <source type="image/webp" srcset="{{ $srcset }}" sizes="{{ $sizes }}">
    @endif
    <img
        src="{{ $podcast->cover_image_url }}"
        alt="{{ $alt ?? $podcast->name }}"
        width="{{ $width }}"
        height="{{ $height }}"
        decoding="async"
        @if($priority)
            fetchpriority="high"
        @else
            loading="lazy"
        @endif
        {{ $attributes }}
    >
</picture>
