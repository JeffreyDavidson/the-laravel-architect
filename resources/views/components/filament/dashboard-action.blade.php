@props([
    'url',
    'label',
    'description',
    'color' => 'blue',
    'icon' => null,
])

{{-- A dashboard shortcut tile. The coloured badge shows the icon, or the slot (a count, for example) when there is none. --}}
<a href="{{ $url }}" class="tla-dashboard-action">
    <span @class(['tla-dashboard-action__icon', "tla-dashboard-action__icon--{$color}"])>
        {{ $icon ? \Filament\Support\generate_icon_html($icon, attributes: (new \Filament\Support\View\ComponentAttributeBag)->class(['size-5'])) : $slot }}
    </span>
    <span>
        <strong>{{ $label }}</strong>
        <small>{{ $description }}</small>
    </span>
</a>
