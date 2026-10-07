@props(['type' => 'success', 'announce' => true])

<x-alert
    :type="$type"
    :announce="$announce"
    :attributes="$attributes->class('mx-auto mb-4 max-w-md rounded-lg p-3')->merge($type === 'error' ? ['id' => 'newsletter-email-error'] : [])"
>
    {{ $slot }}
</x-alert>
