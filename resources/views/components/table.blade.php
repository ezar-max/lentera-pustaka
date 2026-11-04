<div {{ $attributes->merge(['class' => 'overflow-x-auto border border-sage/25 rounded-card bg-ivory-paper']) }}>
    <table class="w-full text-left border-collapse text-sm">
        @if (isset($head))
            <thead class="bg-ivory-dark/70 border-b border-sage/25 text-xs font-semibold uppercase tracking-wider text-sage">
                {{ $head }}
            </thead>
        @endif
        <tbody class="divide-y divide-sage/15 text-charcoal">
            {{ $slot }}
        </tbody>
    </table>
</div>

