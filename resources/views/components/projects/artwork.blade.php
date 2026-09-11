@props(['project', 'priority' => false, 'detail' => false])
@inject('projectImages', 'App\Services\ResponsiveImageVariants')

@php
    $srcset = $project->featured_image_url ? $projectImages->srcset($project->featured_image_path) : null;
    $sizes = $detail
        ? '(min-width: 1280px) 1152px, calc(100vw - 4rem)'
        : '(min-width: 1280px) 560px, (min-width: 1024px) 44vw, calc(100vw - 4rem)';
@endphp

<div
    {{ $attributes->class('overflow-hidden rounded-xl border border-brand-200 bg-brand-50 text-brand-950 dark:border-brand-800 dark:bg-brand-900 dark:text-brand-100') }}
    data-project-artwork
>
    <div class="border-brand-200 dark:border-brand-800 flex items-center justify-between gap-3 border-b px-4 py-3 text-xs sm:px-6">
        <span class="truncate font-semibold">{{ $project->title }}</span>
        <span class="text-brand-700 dark:text-brand-300 shrink-0">The Laravel Architect</span>
    </div>
    <div class="p-4 sm:p-6">
        @if ($project->featured_image_url)
            <picture class="dark:bg-brand-950 block overflow-hidden rounded-lg bg-white">
                @if ($srcset)
                    <source type="image/webp" srcset="{{ $srcset }}" sizes="{{ $sizes }}" />
                @endif
                <img
                    src="{{ $project->featured_image_url }}"
                    alt="{{ $project->title }} project preview"
                    loading="{{ $priority ? 'eager' : 'lazy' }}"
                    decoding="async"
                    @if ($priority) fetchpriority="high" @endif
                    class="aspect-video w-full object-contain"
                />
            </picture>
        @else
            <div
                class="border-brand-300 dark:border-brand-700 dark:bg-brand-950 flex aspect-video flex-col items-center justify-center gap-3 rounded-lg border border-dashed bg-white px-6 text-center"
                data-project-placeholder
            >
                <x-heroicon-o-photo class="text-brand-600 dark:text-brand-400 size-8" aria-hidden="true" />
                <p class="text-sm font-medium">Project preview coming soon</p>
                <p class="text-brand-700 dark:text-brand-300 max-w-xs text-xs leading-5">
                    A closer look at {{ $project->title }}.
                </p>
            </div>
        @endif
    </div>
</div>
