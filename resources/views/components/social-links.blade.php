@if ($variant === 'buttons')
    @if ($profiles->isNotEmpty())
        <div class="flex flex-wrap items-center gap-3">
            @foreach ($profiles as $profile)
                @php($platformLabel = $profile->platform->getLabel())
                <a
                    href="{{ $profile->url }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="relative flex size-12 items-center justify-center rounded-lg border border-gray-200 text-gray-500 transition-[border-color,color,background-color] dark:border-surface-border dark:text-gray-400 {{ $profile->platform->hoverClasses() }}"
                    title="{{ $platformLabel }}"
                    aria-label="{{ filled($profile->label) ? $platformLabel.': '.$profile->label : $platformLabel }}"
                >
                    <x-svg-icon :name="$profile->platform->icon()" class="h-4 w-4" />
                </a>
            @endforeach
        </div>
    @endif
@elseif ($variant === 'list')
    @if ($profiles->isNotEmpty())
        <div class="space-y-3">
            @foreach ($profiles as $profile)
                <a
                    href="{{ $profile->url }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="flex items-center gap-3 text-sm text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                >
                    <x-svg-icon :name="$profile->platform->icon()" class="h-5 w-5 flex-shrink-0" />
                    {{ $profile->label ?: $profile->platform->getLabel() }}
                </a>
            @endforeach
        </div>
    @endif
@endif
