<!-- components/form/checkbox.blade.php -->
@props([
    'name',
    'label',
    'checked' => false,
    'disabled' => false,
    'error' => null,
    'helpText' => null,
])

<div class="flex items-start">
    <div class="flex items-center h-5">
        <input
            type="checkbox"
            name="{{ $name }}"
            id="{{ $name }}"
            @checked($checked)
            @disabled($disabled)
            {{ $attributes->merge([
                'class' => 'h-4 w-4 rounded ' .
                    ($error ? 'border-red-300 dark:border-red-700 focus:ring-red-500 ' : 'border-zinc-300 dark:border-zinc-600 focus:ring-indigo-500 ') .
                    'text-indigo-600 dark:text-indigo-500 dark:bg-zinc-700 focus:ring-offset-2 dark:focus:ring-offset-zinc-800'
            ]) }}
        />
    </div>
    <div class="ml-3 text-sm">
        <label for="{{ $name }}" class="font-medium text-zinc-700 dark:text-zinc-300">
            {{ $label }}
        </label>
        @if($helpText)
            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $helpText }}</p>
        @endif
        @if($error)
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $error }}</p>
        @endif
    </div>
</div>
