@props([
    'variant' => 'primary',
    'type' => 'button',
    'href' => null,
    'size' => 'md',
])

@php
    $baseClass = 'inline-flex items-center justify-center gap-2 font-medium rounded-card transition-colors duration-150 focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed';

    $sizeClass = match ($size) {
        'sm' => 'px-3.5 py-2 text-xs',
        'lg' => 'px-6 py-3 text-base',
        default => 'px-4 py-2.5 text-sm',
    };

    $variantClass = match ($variant) {
        'gold' => 'bg-gold text-charcoal hover:bg-gold-hover font-semibold',
        'secondary' => 'bg-ivory-dark text-charcoal border border-sage/30 hover:bg-sage/15',
        'outline' => 'bg-transparent text-forest border border-forest/40 hover:bg-forest hover:text-ivory',
        'danger' => 'bg-status-ditolak text-status-ditolak-text border border-status-ditolak-text/20 hover:bg-status-ditolak-text hover:text-ivory',
        default => 'bg-forest text-ivory hover:bg-forest-hover',
    };

    $classes = "{$baseClass} {$sizeClass} {$variantClass}";
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif

