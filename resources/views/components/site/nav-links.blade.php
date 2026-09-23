@props([
    'links' => [],
    'mobile' => false,
    'secondary' => false,
])

@foreach ($links as $link)
    <a
        href="{{ route($link['route']) }}"
        @if (request()->routeIs($link['active'])) aria-current="page" @endif
        @class([
            'nav-link text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors',
            'px-2 py-1' => $mobile,
            'text-xs text-gray-500 dark:text-gray-400' => $secondary && ! $mobile,
            'text-gray-500 dark:text-gray-400' => $secondary && $mobile,
            'is-active text-gray-900 dark:text-white' => request()->routeIs($link['active']),
        ])
    >{{ $link['label'] }}</a>
@endforeach
