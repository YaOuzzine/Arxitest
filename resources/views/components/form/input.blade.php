<!-- components/form/input.blade.php -->
@props([
    'name',
    'label' => null,
    'type' => 'text',
    'placeholder' => '',
    'value' => null,
    'required' => false,
    'disabled' => false,
    'error' => null,
    'helpText' => null,
    'leadingIcon' => null,
    'trailingIcon' => null,
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

    <div class="relative rounded-md">
        @if($leadingIcon)
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i data-lucide="{{ $leadingIcon }}" class="h-5 w-5 text-zinc-400 dark:text-zinc-500"></i>
            </div>
        @endif

        <input
            type="{{ $type }}"
            name="{{ $name }}"
            id="{{ $name }}"
            value="{{ $value }}"
            placeholder="{{ $placeholder }}"
            @if($required) required @endif
            @if($disabled) disabled @endif
            {{ $attributes->merge([
                'class' => 'w-full rounded-lg ' .
                    ($leadingIcon ? 'pl-10 ' : 'pl-4 ') .
                    ($trailingIcon ? 'pr-10 ' : 'pr-4 ') .
                    'py-2 border ' .
                    ($error ? 'border-red-300 dark:border-red-700 focus:ring-red-500 focus:border-red-500 ' : 'border-zinc-300 dark:border-zinc-600 focus:ring-indigo-500 focus:border-indigo-500 ') .
                    'dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 shadow-sm'
            ]) }}
        />

        @if($trailingIcon)
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <i data-lucide="{{ $trailingIcon }}" class="h-5 w-5 text-zinc-400 dark:text-zinc-500"></i>
            </div>
        @endif
    </div>

    @if($error)
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $error }}</p>
    @elseif($helpText)
        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ $helpText }}</p>
    @endif
</div>
