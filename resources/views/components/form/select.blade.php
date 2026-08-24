@props([
    'id',
    'name',
])

@php($hasError = $errors->has($name))

<select
    id="{{ $id }}"
    name="{{ $name }}"
    @if($hasError) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
    {{ $attributes->class([
        'w-full rounded-xl border bg-white px-4 py-3 text-base text-gray-900 outline-none transition-all focus:ring-2 dark:bg-[#0D1117] dark:text-gray-200 sm:text-sm',
        'border-red-500 focus:border-red-500 focus:ring-red-500/10' => $hasError,
        'border-gray-300 focus:border-brand-600 focus:ring-brand-600/10 dark:border-[#1e2a3a]' => ! $hasError,
    ]) }}
>
    {{ $slot }}
</select>

@error($name)
    <p id="{{ $id }}-error" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
@enderror
