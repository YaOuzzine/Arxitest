<!-- components/form/textarea.blade.php -->
@props([
    'name',
    'label' => null,
    'placeholder' => '',
    'value' => null,
    'required' => false,
    'disabled' => false,
    'error' => null,
    'helpText' => null,
    'rows' => 3,
])

<div class="space-y-1">
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <textarea
        name="{{ $name }}"
        id="{{ $name }}"
        placeholder="{{ $placeholder }}"
        rows="{{ $rows }}"
        @if($required) required @endif
        @if($disabled) disabled @endif
        {{ $attributes->merge([
            'class' => 'w-full rounded-lg px-4 py-2 border ' .
                ($error ? 'border-red-300 dark:border-red-700 focus:ring-red-500 focus:border-red-500 ' : 'border-zinc-300 dark:border-zinc-600 focus:ring-indigo-500 focus:border-indigo-500 ') .
                'dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 shadow-sm'
        ]) }}
    >{{ $value }}</textarea>

    @if($error)
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $error }}</p>
    @elseif($helpText)
        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ $helpText }}</p>
    @endif
</div>
