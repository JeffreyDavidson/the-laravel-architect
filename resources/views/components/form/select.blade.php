@props([
    'id',
    'name',
    'options' => [],
    'placeholder' => null,
    'value' => null,
    'variant' => 'default',
])

@php($hasError = $errors->has($name))
@php($selectedValue = old($name, $value))
@php($selectClasses = $variant === 'compact'
    ? 'h-11 w-full appearance-none rounded-lg border bg-white px-3 pr-10 text-sm text-gray-900 outline-none transition-all focus:ring-1 dark:bg-surface-control dark:text-gray-200'
    : 'w-full appearance-none rounded-xl border bg-white px-4 py-3 pr-10 text-base text-gray-900 outline-none transition-all focus:ring-2 dark:bg-surface-control dark:text-gray-200 sm:text-sm')

<div class="relative">
    <select
        id="{{ $id }}"
        name="{{ $name }}"
        @if ($hasError) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
        {{
            $attributes->class([
                $selectClasses,
                'border-red-500 focus:border-red-500 focus:ring-red-500/10' => $hasError,
                'border-gray-300 focus:border-brand-600 focus:ring-brand-600/10 dark:border-surface-border' => ! $hasError,
            ])
        }}
    >
        @if ($placeholder !== null)
            <option value="" @selected(blank($selectedValue))>{{ $placeholder }}</option>
        @endif
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected((string) $selectedValue === (string) $optionValue)>
                {{ $optionLabel }}
            </option>
        @endforeach
        {{ $slot }}
    </select>
    <x-heroicon-o-chevron-down
        class="pointer-events-none absolute inset-y-0 right-3 my-auto size-4 text-gray-500 dark:text-gray-400"
        aria-hidden="true"
    />
</div>

@error($name)
    <p id="{{ $id }}-error" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
@enderror
