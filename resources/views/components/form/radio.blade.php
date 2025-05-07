<!-- components/form/radio.blade.php -->
@props([
    'name',
    'label',
    'value',
    'checked' => false,
    'disabled' => false,
    'error' => null,
])

<div class="flex items-center">
    <input
        type="radio"
        name="{{ $name }}"
        id="{{ $name }}_{{ $value }}"
        value="{{ $value }}"
        @checked($checked)
        @disabled($disabled)
        {{ $attributes->merge([
            'class' => 'h-4 w-4 ' .
                ($error ? 'border-red-300 dark:border-red-700 focus:ring-red-500 ' : 'border-zinc-300 dark:border-zinc-600 focus:ring-indigo-500 ') .
                'text-indigo-600 dark:text-indigo-500 dark:bg-zinc-700 focus:ring-offset-2 dark:focus:ring-offset-zinc-800'
        ]) }}
    />
    <label for="{{ $name }}_{{ $value }}" class="ml-3 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
        {{ $label }}
    </label>
</div>
