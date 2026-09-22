@props([
    'url',
    'label',
    'description',
    'color' => 'blue',
    'icon',
])

<a href="{{ $url }}" class="tla-dashboard-action">
    <span @class(['tla-dashboard-action__icon', "tla-dashboard-action__icon--{$color}"])>
        <x-dynamic-component :component="$icon" class="size-5" aria-hidden="true" />
    </span>
    <span>
        <strong>{{ $label }}</strong>
        <small>{{ $description }}</small>
    </span>
</a>
