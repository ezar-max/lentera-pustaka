<x-layouts.app :title="$buku->judul">
    <section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
        <div class="mb-6">
            <a href="{{ route('publik.katalog') }}" class="text-xs font-semibold uppercase tracking-wider text-sage hover:text-forest">
                &larr; Kembali ke Katalog Koleksi
            </a>
        </div>

        {{-- Informasi Utama Buku --}}
        <div class="grid grid-cols-1 md:grid-cols-12 gap-8 pb-12 border-b border-sage/20">
            <div class="md:col-span-4">
                <div class="aspect-[3/4] rounded-card bg-ivory-dark border border-sage/25 overflow-hidden flex items-center justify-center">
                    @if ($buku->cover)
                        <img src="{{ asset('storage/' . $buku->cover) }}" alt="{{ $buku->judul }}" class="w-full h-full object-cover">
                    @else
                        <div class="p-8 text-center">
                            <p class="font-sans text-lg font-semibold text-forest">{{ $buku->judul }}</p>
                            <p class="mt-2 text-xs text-sage">{{ $buku->penulis }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="md:col-span-8 flex flex-col justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-3">
                        @foreach ($buku->kategori as $kat)
                            <span class="px-3 py-1 rounded-card text-xs font-medium bg-ivory-dark text-charcoal">
                                {{ $kat->nama_kategori }}
                            </span>
                        @endforeach

                        <span class="px-3 py-1 rounded-card text-xs font-semibold {{ $buku->stok_tersedia > 0 ? 'bg-status-disetujui text-status-disetujui-text' : 'bg-status-ditolak text-status-ditolak-text' }}">
                            {{ $buku->stok_tersedia > 0 ? 'Stok Tersedia: ' . $buku->stok_tersedia . ' dari ' . $buku->stok_total . ' eksemplar' : 'Stok Sedang Habis Dipinjam' }}
                        </span>
                    </div>

                    <h1 class="font-heading text-2xl sm:text-4xl font-semibold text-forest leading-tight">
                        {{ $buku->judul }}
                    </h1>
                    <p class="mt-2 text-base text-sage">
                        Ditulis oleh <strong class="text-charcoal font-semibold">{{ $buku->penulis }}</strong>
                    </p>

                    {{-- Metadata Tabel Ringkas --}}
                    <dl class="mt-6 grid grid-cols-2 sm:grid-cols-4 gap-4 py-4 border-y border-sage/20 text-xs">
                        <div>
                            <dt class="text-sage uppercase tracking-wider">Penerbit</dt>
                            <dd class="mt-1 font-semibold text-charcoal">{{ $buku->penerbit }}</dd>
                        </div>
                        <div>
                            <dt class="text-sage uppercase tracking-wider">Tahun Terbit</dt>
                            <dd class="mt-1 font-semibold text-charcoal">{{ $buku->tahun_terbit }}</dd>
                        </div>
                        <div>
                            <dt class="text-sage uppercase tracking-wider">ISBN</dt>
                            <dd class="mt-1 font-semibold text-charcoal">{{ $buku->isbn ?: '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sage uppercase tracking-wider">Rating Pembaca</dt>
                            <dd class="mt-1 font-semibold text-forest">
                                {{ $rataRating > 0 ? $rataRating . ' / 5.0' : 'Belum ada rating' }}
                                ({{ $buku->ulasan->count() }} ulasan)
                            </dd>
                        </div>
                    </dl>

                    <div class="mt-6">
                        <h2 class="text-xs font-semibold uppercase tracking-wider text-sage mb-2">Sinopsis Buku</h2>
                        <p class="text-sm text-charcoal leading-relaxed whitespace-pre-line">
                            {{ $buku->sinopsis ?: 'Belum ada sinopsis untuk buku ini.' }}
                        </p>
                    </div>
                </div>

                {{-- Aksi Pinjam --}}
                <div class="mt-8 pt-6 border-t border-sage/20 flex flex-wrap items-center justify-between gap-4">
                    @auth
                        @if (auth()->user()->isPeminjam())
                            <x-button href="{{ route('member.katalog.show', $buku) }}" variant="primary" size="lg">
                                Buka Halaman Peminjaman Anggota &rarr;
                            </x-button>
                        @else
                            <x-button href="{{ auth()->user()->isAdmin() ? route('admin.buku.edit', $buku) : route('petugas.buku.edit', $buku) }}" variant="secondary">
                                Sunting Data Buku Ini
                            </x-button>
                        @endif
                    @else
                        <div>
                            <p class="text-xs text-sage">Ingin meminjam buku ini?</p>
                            <p class="text-sm font-medium text-charcoal">Silakan masuk dengan akun anggota aktif Anda.</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <x-button href="{{ route('login') }}" variant="primary" size="lg">
                                Masuk untuk Meminjam
                            </x-button>
                            <x-button href="{{ route('register') }}" variant="secondary" size="lg">
                                Daftar Anggota
                            </x-button>
                        </div>
                    @endguest
                </div>
            </div>
        </div>

        {{-- Daftar Ulasan Pembaca --}}
        <div class="mt-10">
            <h2 class="font-heading text-xl font-semibold text-forest mb-6">
                Ulasan Anggota ({{ $buku->ulasan->count() }})
            </h2>

            @if ($buku->ulasan->isEmpty())
                <x-empty-state
                    judul="Belum Ada Ulasan"
                    deskripsi="Jadilah anggota pertama yang memberikan ulasan setelah membaca buku ini."
                />
            @else
                <div class="divide-y divide-sage/20 border-y border-sage/20">
                    @foreach ($buku->ulasan as $ulasan)
                        <div class="py-5">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-semibold text-charcoal">{{ $ulasan->user->nama_lengkap }}</p>
                                <span class="px-2.5 py-0.5 rounded-card text-xs font-semibold bg-gold/20 text-charcoal">
                                    Rating: {{ $ulasan->rating }} / 5
                                </span>
                            </div>
                            <p class="mt-2 text-sm text-charcoal-light leading-relaxed">{{ $ulasan->ulasan }}</p>
                            <p class="mt-1.5 text-[11px] text-sage">{{ $ulasan->created_at?->format('d M Y') }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</x-layouts.app>

