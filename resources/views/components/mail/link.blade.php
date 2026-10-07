@props(['href'])

{{-- An email body link; mail-link switches to the lighter colour in dark mode (see the mail layout). --}}
<a href="{{ $href }}" {{ $attributes->merge(['class' => 'mail-link', 'style' => 'color: #3f6fa8']) }}>{{ $slot }}</a>
