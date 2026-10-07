@props(['type' => 'success', 'announce' => true])

{{-- A success or error message box. Callers add its spacing and shape; announce adds the status or alert role. --}}
@php($isError = $type === 'error')

<div
    {{
        $attributes->class([
            'border text-sm',
            'border-green-500/30 bg-green-500/10 text-green-800 dark:text-green-400' => ! $isError,
            'border-red-500/30 bg-red-500/10 text-red-700 dark:text-red-400' => $isError,
        ])
    }}
    @if ($announce) role="{{ $isError ? 'alert' : 'status' }}" @endif
>
    {{ $slot }}
</div>
