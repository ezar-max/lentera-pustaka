@props([
    'label' => null,
    'name',
    'type' => 'text',
    'value' => '',
    'required' => false,
    'hint' => null,
])

<div>
    @if ($label)
        <label for="{{ $name }}" class="block text-xs font-semibold uppercase tracking-wider text-charcoal mb-1.5">
            {{ $label }}
            @if ($required)
                <span class="text-status-ditolak-text">*</span>
            @endif
        </label>
    @endif

    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ old($name, $value) }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge([
            'class' => 'w-full rounded-card border bg-ivory-paper px-4 py-2.5 text-sm text-charcoal placeholder:text-sage/70 focus:border-forest focus:outline-none transition-colors ' . ($errors->has($name) ? 'border-status-ditolak-text' : 'border-sage/35')
        ]) }}
    >

    @if ($hint)
        <p class="mt-1 text-xs text-sage">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="mt-1 text-xs font-medium text-status-ditolak-text">{{ $message }}</p>
    @enderror
</div>

