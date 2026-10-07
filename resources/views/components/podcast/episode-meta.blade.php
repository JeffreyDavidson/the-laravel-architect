{{-- An episode's code · date · duration line. Pass code-class to show the code; the duration and its separator appear only when the episode has one. --}}
@props([
    'episode',
    'separatorClass',
    'codeClass' => null,
    'itemClass' => null,
])
@php($presenter = \App\Presenters\EpisodePresenter::from($episode))
@if ($codeClass)
    <span class="{{ $codeClass }}">{{ $presenter->code() }}</span>
    <span class="{{ $separatorClass }}">·</span>
@endif
@if ($itemClass)
    <x-display-date :date="$episode->published_at" :class="$itemClass" />
@else
    <x-display-date :date="$episode->published_at" />
@endif
@if ($presenter->duration())
    <span class="{{ $separatorClass }}">·</span>
    <span @if ($itemClass) class="{{ $itemClass }}" @endif>{{ $presenter->duration() }}</span>
@endif
