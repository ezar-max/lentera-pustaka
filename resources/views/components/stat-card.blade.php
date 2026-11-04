@props([
    'label',
    'value',
    'subtitle' => null,
    'accent' => false,
])

<div {{ $attributes->merge([
    'class' => 'rounded-card p-5 border transition-colors ' . ($accent
        ? 'bg-forest text-ivory border-forest'
        : 'bg-ivory-paper text-charcoal border-sage/25')
]) }}>
    <p class="text-xs font-medium uppercase tracking-wider {{ $accent ? 'text-ivory/80' : 'text-sage' }}">
        {{ $label }}
    </p>
    <p class="mt-2 text-2xl sm:text-3xl font-semibold tracking-tight {{ $accent ? 'text-gold' : 'text-forest' }}">
        {{ $value }}
    </p>
    @if ($subtitle)
        <p class="mt-1.5 text-xs {{ $accent ? 'text-ivory/75' : 'text-sage' }}">
            {{ $subtitle }}
        </p>
    @endif
</div>

