@use('App\Enums\SocialPlatform')
@if ($variant === 'buttons')
    @if ($profiles->isNotEmpty())
        <div class="flex flex-wrap items-center gap-3">
            @foreach ($profiles as $profile)
                @php
                    $platformLabel = $profile->platform->getLabel();
                    $hoverClasses = match ($profile->platform) {
                        SocialPlatform::YouTube => 'hover:text-red-500 hover:border-red-500/50 hover:bg-red-500/5',
                        SocialPlatform::Bluesky => 'hover:text-blue-400 hover:border-blue-400/50 hover:bg-blue-400/5',
                        SocialPlatform::Instagram => 'hover:text-pink-400 hover:border-pink-400/50 hover:bg-pink-400/5',
                        SocialPlatform::Facebook => 'hover:text-blue-500 hover:border-blue-500/50 hover:bg-blue-500/5',
                        SocialPlatform::LinkedIn => 'hover:text-blue-600 hover:border-blue-600/50 hover:bg-blue-600/5',
                        default => 'hover:text-gray-900 dark:hover:text-white hover:border-brand-600/50 hover:bg-brand-600/5',
                    };
                @endphp
                <a
                    href="{{ $profile->url }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="relative flex size-12 items-center justify-center rounded-lg border border-gray-200 text-gray-500 transition-[border-color,color,background-color] dark:border-surface-border dark:text-gray-400 {{ $hoverClasses }}"
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
