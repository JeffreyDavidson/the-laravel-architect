@props(['for'])

<label
    for="{{ $for }}"
    {{ $attributes->class('mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300') }}
>{{ $slot }}</label>
