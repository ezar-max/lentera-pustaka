<x-layouts.app title="Katalog Koleksi Buku">
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
        {{-- Header & Pencarian --}}
        <div class="pb-8 border-b border-sage/20">
            <p class="text-xs font-semibold uppercase tracking-wider text-sage">Eksplorasi Pustaka</p>
            <h1 class="mt-1 font-heading text-2xl sm:text-3xl font-semibold text-forest">Katalog Koleksi Lentera Pustaka</h1>

            <form action="{{ route('publik.katalog') }}" method="GET" class="mt-6 grid grid-cols-1 sm:grid-cols-12 gap-3">
                <div class="sm:col-span-5">
                    <input
                        type="text"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Cari judul buku, penulis, penerbit, atau ISBN..."
                        class="w-full rounded-card border border-sage/35 bg-ivory-paper px-4 py-2.5 text-sm text-charcoal placeholder:text-sage/70 focus:border-forest focus:outline-none"
                    >
                </div>

                <div class="sm:col-span-3">
                    <select name="kategori" class="w-full rounded-card border border-sage/35 bg-ivory-paper px-3.5 py-2.5 text-sm text-charcoal focus:border-forest focus:outline-none">
                        <option value="">Semua Kategori</option>
                        @foreach ($kategoriList as $kat)
                            <option value="{{ $kat->id }}" @selected((string) request('kategori') === (string) $kat->id)>
                                {{ $kat->nama_kategori }} ({{ $kat->buku_count }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <select name="urut" class="w-full rounded-card border border-sage/35 bg-ivory-paper px-3.5 py-2.5 text-sm text-charcoal focus:border-forest focus:outline-none">
                        <option value="terbaru" @selected(request('urut', 'terbaru') === 'terbaru')>Terbaru</option>
                        <option value="terpopuler" @selected(request('urut') === 'terpopuler')>Terpopuler</option>
                        <option value="tersedia" @selected(request('urut') === 'tersedia')>Stok Terbanyak</option>
                        <option value="judul_asc" @selected(request('urut') === 'judul_asc')>Judul (A-Z)</option>
                    </select>
                </div>

                <div class="sm:col-span-2 flex gap-2">
                    <x-button type="submit" variant="primary" class="w-full">
                        Terapkan
                    </x-button>
                    @if (request()->hasAny(['q', 'kategori', 'urut']))
                        <x-button href="{{ route('publik.katalog') }}" variant="secondary" title="Reset Filter">
                            Reset
                        </x-button>
                    @endif
                </div>
            </form>
        </div>

        {{-- Daftar Buku --}}
        <div class="mt-8">
            @if ($bukuList->isEmpty())
                <x-empty-state
                    judul="Koleksi Tidak Ditemukan"
                    deskripsi="Tidak ada buku yang cocok dengan kata kunci atau filter kategori yang Anda pilih."
                >
                    <x-button href="{{ route('publik.katalog') }}" variant="secondary">
                        Tampilkan Semua Koleksi
                    </x-button>
                </x-empty-state>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach ($bukuList as $buku)
                        <article class="bg-ivory-paper border border-sage/25 rounded-card p-5 flex flex-col justify-between hover:border-forest/50 transition-colors">
                            <div>
                                <div class="aspect-[3/4] rounded-card bg-ivory-dark border border-sage/15 mb-4 overflow-hidden flex items-center justify-center relative">
                                    @if ($buku->cover)
                                        <img src="{{ asset('storage/' . $buku->cover) }}" alt="{{ $buku->judul }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="p-6 text-center">
                                            <p class="font-sans text-sm font-semibold text-forest line-clamp-3">{{ $buku->judul }}</p>
                                            <p class="mt-2 text-xs text-sage">{{ $buku->penulis }}</p>
                                        </div>
                                    @endif

                                    <span class="absolute top-3 right-3 px-2.5 py-1 rounded-card text-[11px] font-semibold {{ $buku->stok_tersedia > 0 ? 'bg-forest text-ivory' : 'bg-status-ditolak text-status-ditolak-text' }}">
                                        {{ $buku->stok_tersedia > 0 ? 'Tersedia: ' . $buku->stok_tersedia : 'Stok Habis' }}
                                    </span>
                                </div>

                                <div class="flex flex-wrap gap-1.5 mb-2">
                                    @foreach ($buku->kategori as $kat)
                                        <span class="text-[11px] font-medium text-sage bg-ivory-dark px-2.5 py-0.5 rounded-card">
                                            {{ $kat->nama_kategori }}
                                        </span>
                                    @endforeach
                                </div>

                                <h2 class="font-semibold text-base text-charcoal leading-snug line-clamp-2">
                                    <a href="{{ route('publik.buku.show', $buku) }}" class="hover:text-forest">
                                        {{ $buku->judul }}
                                    </a>
                                </h2>
                                <p class="mt-1 text-xs text-sage">{{ $buku->penulis }} · {{ $buku->tahun_terbit }}</p>
                            </div>

                            <div class="mt-4 pt-3 border-t border-sage/15 flex items-center justify-between text-xs">
                                <span class="text-sage">
                                    Stok Total: <strong class="text-charcoal">{{ $buku->stok_total }}</strong>
                                </span>
                                <a href="{{ route('publik.buku.show', $buku) }}" class="font-semibold text-forest hover:underline">
                                    Lihat Detail &rarr;
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-8">
                    <x-pagination :paginator="$bukuList" />
                </div>
            @endif
        </div>
    </section>
</x-layouts.app>

