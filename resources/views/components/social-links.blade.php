@props(['variant' => 'buttons'])

@php
$buttonLinks = [
    ['name' => 'github', 'url' => 'https://github.com/JeffreyDavidson', 'label' => 'GitHub', 'hover' => 'hover:text-gray-900 dark:hover:text-white hover:border-[#4A7FBF]/50 hover:bg-[#4A7FBF]/5'],
    ['name' => 'x-twitter', 'url' => 'https://x.com/thelaravelarch', 'label' => 'X / Twitter', 'hover' => 'hover:text-gray-900 dark:hover:text-white hover:border-[#4A7FBF]/50 hover:bg-[#4A7FBF]/5'],
    ['name' => 'youtube', 'url' => 'https://youtube.com/@thelaravelarchitect', 'label' => 'YouTube', 'hover' => 'hover:text-red-500 hover:border-red-500/50 hover:bg-red-500/5'],
    ['name' => 'bluesky', 'url' => 'https://bsky.app/profile/thelaravelarch', 'label' => 'Bluesky', 'hover' => 'hover:text-blue-400 hover:border-blue-400/50 hover:bg-blue-400/5'],
    ['name' => 'instagram', 'url' => 'https://instagram.com/thelaravelarch', 'label' => 'Instagram', 'hover' => 'hover:text-pink-400 hover:border-pink-400/50 hover:bg-pink-400/5'],
    ['name' => 'facebook', 'url' => 'https://facebook.com/thelaravelarch', 'label' => 'Facebook', 'hover' => 'hover:text-blue-500 hover:border-blue-500/50 hover:bg-blue-500/5'],
];

$listLinks = [
    ['name' => 'github', 'url' => 'https://github.com/JeffreyDavidson', 'label' => 'GitHub'],
    ['name' => 'x-twitter', 'url' => 'https://x.com/thelaravelarch', 'label' => '@thelaravelarch'],
    ['name' => 'bluesky', 'url' => 'https://bsky.app/profile/thelaravelarch', 'label' => 'Bluesky'],
    ['name' => 'youtube', 'url' => 'https://youtube.com/@thelaravelarchitect', 'label' => 'YouTube'],
];
@endphp

@if($variant === 'buttons')
<div class="flex flex-wrap items-center gap-3">
    @foreach($buttonLinks as $link)
    <a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer" class="relative flex size-12 items-center justify-center rounded-lg border border-gray-200 text-gray-500 transition-all dark:border-[#1e2a3a] dark:text-gray-400 {{ $link['hover'] }}" title="{{ $link['label'] }}" aria-label="{{ $link['label'] }}">
        <x-svg-icon :name="$link['name']" class="w-4 h-4" />
    </a>
    @endforeach
</div>
@elseif($variant === 'list')
<div class="space-y-3">
    @foreach($listLinks as $link)
    <a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-3 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
        <x-svg-icon :name="$link['name']" class="w-5 h-5 flex-shrink-0" />
        {{ $link['label'] }}
    </a>
    @endforeach
</div>
@endif
