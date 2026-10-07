{{--
    Listening platform buttons. Given a podcast, renders the show's subscribe buttons from
    PodcastPresenter::platformLinks(), or a "coming soon" note when it has none. Given only an
    episode's YouTube URL, renders the episode's compact "Listen on" row, or nothing without one.
--}}
@props([
    'podcast' => null,
    'youtubeUrl' => null,
])

@php($links = $podcast
    ? \App\Presenters\PodcastPresenter::from($podcast)->platformLinks()
    : ($youtubeUrl ? [['label' => 'YouTube', 'url' => $youtubeUrl, 'icon' => 'youtube']] : []))

@if ($podcast || $links)
    <div @class([
        'flex flex-wrap justify-center gap-3 md:justify-start' => $podcast,
        'mb-10 flex flex-wrap gap-3' => ! $podcast,
    ])>
        @unless ($podcast)
            <span class="mr-2 self-center text-xs font-semibold tracking-wide text-gray-500 uppercase">Listen on</span>
        @endunless

        @foreach ($links as $link)
            <a
                href="{{ $link['url'] }}"
                target="_blank"
                rel="noopener noreferrer"
                @class([
                    'inline-flex items-center gap-2 border py-2.5 text-sm font-medium transition-colors hover:-translate-y-0.5 motion-reduce:transition-none',
                    'rounded-xl px-5' => $podcast,
                    'rounded-lg px-4' => ! $podcast,
                    'border-social-spotify/20 bg-social-spotify/10 text-social-spotify hover:bg-social-spotify/20' => $link['icon'] === 'spotify',
                    'border-social-overcast/20 bg-social-overcast/10 text-social-overcast hover:bg-social-overcast/20' => $link['icon'] === 'apple-podcasts',
                    'border-red-500/20 bg-red-500/10 text-red-400 hover:bg-red-500/20' => $link['icon'] === 'youtube',
                    'border-orange-500/20 bg-orange-500/10 text-orange-400 hover:bg-orange-500/20' => $link['icon'] === 'rss',
                ])
            >
                @switch ($link['icon'])
                    @case ('spotify')
                        <x-svg-icon name="spotify" class="h-4 w-4" />
                        @break
                    @case ('apple-podcasts')
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M5.34 0A5.328 5.328 0 000 5.34v13.32A5.328 5.328 0 005.34 24h13.32A5.328 5.328 0 0024 18.66V5.34A5.328 5.328 0 0018.66 0H5.34z" /></svg>
                        @break
                    @case ('youtube')
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" /></svg>
                        @break
                    @case ('rss')
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M6.18 15.64a2.18 2.18 0 010 4.36 2.18 2.18 0 010-4.36M4 4.44A15.56 15.56 0 0119.56 20h-2.83A12.73 12.73 0 004 7.27V4.44m0 5.66a9.9 9.9 0 019.9 9.9h-2.83A7.07 7.07 0 004 12.93V10.1z" /></svg>
                        @break
                @endswitch
                {{ $link['label'] }}
            </a>
        @endforeach

        @if ($podcast && ! $links)
            <span class="dark:border-surface-border inline-flex items-center gap-1.5 rounded-xl border border-gray-200 px-5 py-2.5 text-sm text-gray-500">
                <span class="h-1.5 w-1.5 rounded-full bg-[var(--podcast-color)]"></span>
                Subscribe links coming soon
            </span>
        @endif
    </div>
@endif
