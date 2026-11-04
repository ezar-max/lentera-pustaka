@props(['paginator'])

@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi Halaman" class="flex items-center justify-between pt-4 border-t border-sage/20">
        <div class="text-xs text-sage">
            Menampilkan <span class="font-semibold text-charcoal">{{ $paginator->firstItem() }}</span>
            sampai <span class="font-semibold text-charcoal">{{ $paginator->lastItem() }}</span>
            dari <span class="font-semibold text-charcoal">{{ $paginator->total() }}</span> data
        </div>

        <div class="flex items-center gap-1.5">
            @if ($paginator->onFirstPage())
                <span class="px-3.5 py-2 text-xs rounded-card border border-sage/20 text-sage/50 cursor-not-allowed">
                    Sebelumnya
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="px-3.5 py-2 text-xs font-medium rounded-card border border-sage/30 bg-ivory-paper text-charcoal hover:bg-forest hover:text-ivory transition-colors">
                    Sebelumnya
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="px-3.5 py-2 text-xs font-medium rounded-card border border-sage/30 bg-ivory-paper text-charcoal hover:bg-forest hover:text-ivory transition-colors">
                    Berikutnya
                </a>
            @else
                <span class="px-3.5 py-2 text-xs rounded-card border border-sage/20 text-sage/50 cursor-not-allowed">
                    Berikutnya
                </span>
            @endif
        </div>
    </nav>
@endif

