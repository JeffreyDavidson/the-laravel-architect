@props([
    'post',
    'sizes' => '100vw',
    'priority' => false,
])

@inject('responsiveImages', 'App\Services\ResponsiveImageVariants')

@php
    $bundledArtwork = match ($post->slug) {
        'hello-world-why-im-starting-this-blog' => [
            'small' => Vite::asset('resources/images/post-hello-world-768.webp'),
            'large' => Vite::asset('resources/images/post-hello-world-1280.webp'),
        ],
        'from-kansas-to-florida-a-developers-journey' => [
            'small' => Vite::asset('resources/images/post-kansas-florida-768.webp'),
            'large' => Vite::asset('resources/images/post-kansas-florida-1280.webp'),
        ],
        'how-i-structure-every-laravel-project' => [
            'small' => Vite::asset('resources/images/home-writing-fallback-768.webp'),
            'large' => Vite::asset('resources/images/home-writing-fallback-1280.webp'),
        ],
        'why-i-still-choose-laravel-in-2026' => [
            'small' => Vite::asset('resources/images/home-writing-review-768.webp'),
            'large' => Vite::asset('resources/images/home-writing-review-1280.webp'),
        ],
        'what-15-years-of-web-development-taught-me' => [
            'small' => Vite::asset('resources/images/home-writing-modules-768.webp'),
            'large' => Vite::asset('resources/images/home-writing-modules-1280.webp'),
        ],
        default => null,
    };

    $uploadedSrcset = $post->featured_image_url
        ? $responsiveImages->srcset($post->featured_image_path)
        : null;
    $src = $post->featured_image_url ?? $bundledArtwork['large'] ?? null;
    $srcset = $uploadedSrcset ?? ($bundledArtwork
        ? "{$bundledArtwork['small']} 768w, {$bundledArtwork['large']} 1280w"
        : null);
@endphp

@if($src)
<picture {{ $attributes->class('block overflow-hidden') }} data-post-artwork="{{ $post->slug }}">
    @if($srcset)
    <source type="image/webp" srcset="{{ $srcset }}" sizes="{{ $sizes }}">
    @endif
    <img
        src="{{ $src }}"
        alt=""
        decoding="async"
        @if($priority) fetchpriority="high" @else loading="lazy" @endif
        class="h-full w-full object-cover"
    >
</picture>
@endif
