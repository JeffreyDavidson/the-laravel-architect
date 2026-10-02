@props([
    'post',
    'sizes' => '100vw',
    'priority' => false,
])

@inject('responsiveImages', 'App\Services\ResponsiveImageVariants')
@inject('bundledPostArtwork', 'App\Support\Content\BundledPostArtwork')

@php
    $bundledArtwork = $bundledPostArtwork->urls($post->slug);

    $uploadedSrcset = $post->featured_image_url
        ? $responsiveImages->srcset($post->featured_image_path)
        : null;
    $src = $post->featured_image_url ?? $bundledArtwork['large'] ?? null;
    $srcset = $uploadedSrcset ?? ($bundledArtwork
        ? "{$bundledArtwork['small']} 384w, {$bundledArtwork['medium']} 768w, {$bundledArtwork['large']} 1280w"
        : null);
@endphp

@if ($src)
    <picture {{ $attributes->class('block overflow-hidden') }} data-post-artwork="{{ $post->slug }}">
        @if ($srcset)
            <source type="image/webp" srcset="{{ $srcset }}" sizes="{{ $sizes }}" />
        @endif
        <img
            src="{{ $src }}"
            alt=""
            decoding="async"
            @if ($priority) fetchpriority="high" @else loading="lazy" @endif
            class="h-full w-full object-cover"
        />
    </picture>
@endif
