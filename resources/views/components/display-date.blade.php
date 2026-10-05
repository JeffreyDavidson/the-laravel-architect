{{-- A publication date shown in the site's display timezone; renders nothing without a date. --}}
@props(['date', 'format' => 'M d, Y'])
@if ($date)
    @php($displayDate = \App\Support\DisplayTimezone::convert($date))
    <time {{ $attributes->merge(['datetime' => $displayDate->toDateString()]) }}>{{ $displayDate->format($format) }}</time>
@endif
