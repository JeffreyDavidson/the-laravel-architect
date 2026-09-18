@props(['title', 'links'])

<div>
    <h2 class="mb-3 text-xs font-semibold tracking-widest text-gray-500 uppercase dark:text-gray-500">{{ $title }}</h2>
    <ul role="list" class="space-y-2 text-sm">
        @foreach ($links as $link)
            <li>
                <a
                    href="{{ isset($link['route']) ? route($link['route']) : $link['url'] }}"
                    @if ($link['external'] ?? false) target="_blank" rel="noopener noreferrer" @endif
                    class="text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                >{{ $link['label'] }}</a>
            </li>
        @endforeach
    </ul>
</div>
