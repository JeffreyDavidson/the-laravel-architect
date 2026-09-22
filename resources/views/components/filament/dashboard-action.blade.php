@props([
    'url',
    'label',
    'description',
    'color' => 'blue',
    'icon',
])

<a href="{{ $url }}" class="tla-dashboard-action">
    <span @class(['tla-dashboard-action__icon', "tla-dashboard-action__icon--{$color}"])>
        {{ \Filament\Support\generate_icon_html($icon, attributes: (new \Filament\Support\View\ComponentAttributeBag)->class(['size-5'])) }}
    </span>
    <span>
        <strong>{{ $label }}</strong>
        <small>{{ $description }}</small>
    </span>
</a>
