<x-layouts.app title="Beranda">
    {{-- Hero Section --}}
    <section class="bg-forest text-ivory border-b border-sage/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24">
            <div class="max-w-3xl">
                <span class="inline-flex items-center px-3.5 py-1 rounded-card text-xs font-semibold tracking-wider uppercase bg-gold/20 text-gold border border-gold/40">
                    Ruang Literasi & Sirkulasi Terpadu
                </span>
                <h1 class="mt-5 font-heading text-3xl sm:text-5xl font-semibold tracking-tight text-ivory leading-tight">
                    Menjaga Khazanah Ilmu dalam Ketenangan Lentera Pustaka.
                </h1>
                <p class="mt-5 text-base sm:text-lg text-ivory/80 leading-relaxed max-w-2xl">
                    Telusuri ketersediaan koleksi secara akurat, ajukan peminjaman maupun pengembalian buku secara terstruktur, dan konsultasikan bacaan Anda bersama Asisten Pustakawan Digital kami.
                </p>

                <div class="mt-8 flex flex-wrap items-center gap-3.5">
                    <x-button href="{{ route('publik.katalog') }}" variant="gold" size="lg">
                        Jelajahi Katalog Buku
                    </x-button>
                    @guest
                        <x-button href="{{ route('register') }}" variant="secondary" size="lg" class="bg-ivory/10 text-ivory border-ivory/25 hover:bg-ivory/20">
                            Daftar Menjadi Anggota
                        </x-button>
                    @else
                        <x-button href="{{ auth()->user()->isPeminjam() ? route('member.dashboard') : (auth()->user()->isAdmin() ? route('admin.dashboard') : route('petugas.dashboard')) }}" variant="secondary" size="lg" class="bg-ivory/10 text-ivory border-ivory/25 hover:bg-ivory/20">
                            Buka Dashboard Saya
                        </x-button>
                    @endguest
                </div>
            </div>

            {{-- Statistik Singkat --}}
            <div class="mt-14 pt-8 border-t border-ivory/15 grid grid-cols-2 sm:grid-cols-4 gap-6">
                <div>
                    <p class="text-2xl sm:text-3xl font-heading font-semibold text-gold">{{ $statistik['total_judul'] }}</p>
                    <p class="mt-1 text-xs uppercase tracking-wider text-ivory/70">Judul Koleksi</p>
                </div>
                <div>
                    <p class="text-2xl sm:text-3xl font-heading font-semibold text-gold">{{ $statistik['total_eksemplar'] }}</p>
                    <p class="mt-1 text-xs uppercase tracking-wider text-ivory/70">Total Eksemplar</p>
                </div>
                <div>
                    <p class="text-2xl sm:text-3xl font-heading font-semibold text-gold">{{ $statistik['total_kategori'] }}</p>
                    <p class="mt-1 text-xs uppercase tracking-wider text-ivory/70">Kategori Keilmuan</p>
                </div>
                <div>
                    <p class="text-2xl sm:text-3xl font-heading font-semibold text-gold">{{ $statistik['total_member'] }}</p>
                    <p class="mt-1 text-xs uppercase tracking-wider text-ivory/70">Anggota Aktif</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Koleksi Terpopuler --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 border-b border-sage/20">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-sage">Pilihan Pembaca</p>
                <h2 class="mt-1 font-heading text-2xl sm:text-3xl font-semibold text-forest">Koleksi Paling Sering Dipinjam</h2>
            </div>
            <a href="{{ route('publik.katalog', ['urut' => 'terpopuler']) }}" class="text-sm font-semibold text-forest hover:underline">
                Lihat Seluruh Katalog &rarr;
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach ($bukuPopuler as $buku)
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
                                {{ $buku->stok_tersedia > 0 ? 'Tersedia ' . $buku->stok_tersedia : 'Habis' }}
                            </span>
                        </div>

                        <div class="flex flex-wrap gap-1.5 mb-2">
                            @foreach ($buku->kategori->take(2) as $kat)
                                <span class="text-[11px] font-medium text-sage bg-ivory-dark px-2.5 py-0.5 rounded-card">
                                    {{ $kat->nama_kategori }}
                                </span>
                            @endforeach
                        </div>

                        <h3 class="font-semibold text-base text-charcoal leading-snug line-clamp-2">
                            <a href="{{ route('publik.buku.show', $buku) }}" class="hover:text-forest">
                                {{ $buku->judul }}
                            </a>
                        </h3>
                        <p class="mt-1 text-xs text-sage">{{ $buku->penulis }} · {{ $buku->tahun_terbit }}</p>
                    </div>

                    <div class="mt-4 pt-3 border-t border-sage/15 flex items-center justify-between text-xs">
                        <span class="text-sage">
                            Rating: <strong class="text-charcoal">{{ $buku->ulasan_avg_rating ? number_format($buku->ulasan_avg_rating, 1) : '-' }}</strong>/5
                        </span>
                        <a href="{{ route('publik.buku.show', $buku) }}" class="font-semibold text-forest hover:underline">
                            Detail Buku
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    {{-- Koleksi Terbaru --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-sage">Katalog Terkini</p>
                <h2 class="mt-1 font-heading text-2xl sm:text-3xl font-semibold text-forest">Koleksi Pustaka Terbaru</h2>
            </div>
            <a href="{{ route('publik.katalog') }}" class="text-sm font-semibold text-forest hover:underline">
                Telusuri Semua Judul &rarr;
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach ($bukuTerbaru as $buku)
                <article class="bg-ivory-paper border border-sage/25 rounded-card p-5 flex flex-col justify-between hover:border-forest/50 transition-colors">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-[11px] font-medium text-sage truncate">
                                {{ $buku->kategori->pluck('nama_kategori')->join(', ') ?: 'Umum' }}
                            </span>
                            <span class="shrink-0 px-2.5 py-0.5 rounded-card text-[11px] font-semibold {{ $buku->stok_tersedia > 0 ? 'bg-status-disetujui text-status-disetujui-text' : 'bg-status-ditolak text-status-ditolak-text' }}">
                                Stok: {{ $buku->stok_tersedia }}/{{ $buku->stok_total }}
                            </span>
                        </div>

                        <h3 class="font-semibold text-base text-charcoal leading-snug line-clamp-2">
                            <a href="{{ route('publik.buku.show', $buku) }}" class="hover:text-forest">
                                {{ $buku->judul }}
                            </a>
                        </h3>
                        <p class="mt-1 text-xs text-sage">{{ $buku->penulis }}</p>
                        <p class="mt-2.5 text-xs text-charcoal-light line-clamp-3 leading-relaxed">
                            {{ $buku->sinopsis }}
                        </p>
                    </div>

                    <div class="mt-4 pt-3 border-t border-sage/15 flex items-center justify-between text-xs">
                        <span class="text-sage">{{ $buku->penerbit }} ({{ $buku->tahun_terbit }})</span>
                        <a href="{{ route('publik.buku.show', $buku) }}" class="font-semibold text-forest hover:underline">
                            Detail &rarr;
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    </section>
</x-layouts.app>

