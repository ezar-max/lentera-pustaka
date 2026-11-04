@props([
    'judul' => 'Data Belum Tersedia',
    'deskripsi' => 'Saat ini belum ada data yang dapat ditampilkan pada bagian ini.',
])

<div {{ $attributes->merge(['class' => 'py-12 px-6 text-center border border-dashed border-sage/35 rounded-card bg-ivory-paper/60']) }}>
    <div class="mx-auto w-11 h-11 rounded-card bg-forest/10 text-forest flex items-center justify-center mb-3">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
        </svg>
    </div>
    <h3 class="text-base font-semibold text-charcoal">{{ $judul }}</h3>
    <p class="mt-1 text-sm text-sage max-w-md mx-auto">{{ $deskripsi }}</p>
    @if ($slot->isNotEmpty())
        <div class="mt-5">
            {{ $slot }}
        </div>
    @endif
</div>

