@props([
    'youtubeUrl' => null,
])

@if ($youtubeUrl)
    <div class="mb-10 flex flex-wrap gap-3">
        <span class="mr-2 self-center text-xs font-semibold tracking-wide text-gray-500 uppercase">Listen on</span>

        <a
            href="{{ $youtubeUrl }}"
            target="_blank"
            rel="noopener noreferrer"
            class="inline-flex items-center gap-2 rounded-lg border border-red-500/20 bg-red-500/10 px-4 py-2.5 text-sm font-medium text-red-400 transition-colors hover:-translate-y-0.5 hover:bg-red-500/20 motion-reduce:transition-none"
        >
            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505-.505 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" /></svg>
            YouTube
        </a>
    </div>
@endif
