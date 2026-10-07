{{--
    Text with every case-insensitive match of the query wrapped in a mark. The raw text is split on the
    raw query and each segment is escaped on its own, so a match can never split an entity such as &amp;.
    The loop is kept free of indentation because whitespace between segments would show as spaces.
--}}
@props(['text', 'query'])
@php
    $segments = $query === '' ? false : preg_split('/('.preg_quote($query, '/').')/iu', $text, flags: PREG_SPLIT_DELIM_CAPTURE);
@endphp
{{-- format-ignore-start --}}@foreach ($segments ?: [$text] as $index => $segment)
@if ($index % 2 === 1)<mark class="rounded bg-brand-100 px-0.5 text-inherit dark:bg-brand-800">{{ $segment }}</mark>@else{{ $segment }}@endif
@endforeach{{-- format-ignore-end --}}
