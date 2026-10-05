@props(['type' => 'success', 'announce' => true])

@php($isError = $type === 'error')

<div
    {{
        $attributes
            ->class([
                'mx-auto mb-4 max-w-md rounded-lg border p-3 text-sm',
                'border-green-500/30 bg-green-500/10 text-green-800 dark:text-green-400' => ! $isError,
                'border-red-500/30 bg-red-500/10 text-red-700 dark:text-red-400' => $isError,
            ])
            ->merge($isError ? ['id' => 'newsletter-email-error'] : [])
    }}
    @if ($announce) role="{{ $isError ? 'alert' : 'status' }}" @endif
>
    {{ $slot }}
</div>
