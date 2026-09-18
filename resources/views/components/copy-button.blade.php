@props(['label' => 'Copy code', 'success' => 'Copied', 'text' => null])

<button
    type="button"
    x-data="copyButton"
    x-on:click="copy"
    x-bind:aria-label="label"
    x-bind:title="label"
    x-bind:class="buttonClasses"
    data-copy-label="{{ $label }}"
    data-copy-success="{{ $success }}"
    @if ($text !== null) data-copy-text="{{ $text }}" @endif
    aria-label="{{ $label }}"
    title="{{ $label }}"
    aria-live="polite"
    {{ $attributes }}
>
    <svg x-bind:hidden="copied" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" /></svg>
    <svg
        hidden
        x-bind:hidden="notCopied"
        class="h-4 w-4 text-green-400"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
        aria-hidden="true"
    ><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
</button>
