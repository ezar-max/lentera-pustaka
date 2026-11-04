<x-layouts.app :title="$buku->judul">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="mb-6">
            <a href="{{ route('member.katalog') }}" class="text-xs font-semibold uppercase tracking-wider text-sage hover:text-forest">
                &larr; Kembali ke Katalog Anggota
            </a>
        </div>

        {{-- Detail Buku & Tombol Aksi --}}
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
                            Stok Tersedia: {{ $buku->stok_tersedia }} dari {{ $buku->stok_total }}
                        </span>
                    </div>

                    <h1 class="font-sans text-2xl sm:text-4xl font-semibold text-forest">{{ $buku->judul }}</h1>
                    <p class="mt-1.5 text-sm text-sage">Penulis: <strong class="text-charcoal">{{ $buku->penulis }}</strong></p>

                    <dl class="mt-5 grid grid-cols-2 sm:grid-cols-4 gap-4 py-4 border-y border-sage/20 text-xs">
                        <div>
                            <dt class="text-sage uppercase">Penerbit</dt>
                            <dd class="mt-1 font-semibold text-charcoal">{{ $buku->penerbit }}</dd>
                        </div>
                        <div>
                            <dt class="text-sage uppercase">Tahun Terbit</dt>
                            <dd class="mt-1 font-semibold text-charcoal">{{ $buku->tahun_terbit }}</dd>
                        </div>
                        <div>
                            <dt class="text-sage uppercase">ISBN</dt>
                            <dd class="mt-1 font-semibold text-charcoal">{{ $buku->isbn ?: '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sage uppercase">Rating Rata-rata</dt>
                            <dd class="mt-1 font-semibold text-forest">{{ $rataRating > 0 ? $rataRating . ' / 5.0' : 'Belum ada' }}</dd>
                        </div>
                    </dl>

                    <div class="mt-5">
                        <h2 class="text-xs font-semibold uppercase tracking-wider text-sage mb-2">Sinopsis</h2>
                        <p class="text-sm text-charcoal leading-relaxed whitespace-pre-line">{{ $buku->sinopsis ?: 'Tidak ada sinopsis.' }}</p>
                    </div>
                </div>

                {{-- Panel Tombol Pinjam & Koleksi --}}
                <div class="mt-8 pt-6 border-t border-sage/20 space-y-3">
                    <div class="flex flex-wrap items-center gap-3">
                        @if ($kelayakan['bisa'])
                            <form action="{{ route('member.peminjaman.store') }}" method="POST"
                                  data-confirm="Ajukan peminjaman buku '{{ $buku->judul }}' sekarang?"
                                  data-confirm-title="Ajukan Peminjaman Buku"
                                  data-confirm-button="Ya, Ajukan Pinjam">
                                @csrf
                                <input type="hidden" name="buku_id" value="{{ $buku->id }}">
                                <x-button type="submit" variant="primary" size="lg">
                                    Ajukan Peminjaman Buku Ini
                                </x-button>
                            </form>
                        @else
                            <button type="button" disabled class="px-5 py-3 rounded-card text-sm font-semibold bg-ivory-dark text-sage border border-sage/30 cursor-not-allowed">
                                Tombol Pinjam Nonaktif
                            </button>
                        @endif

                        <form action="{{ route('member.koleksi.toggle', $buku) }}" method="POST">
                            @csrf
                            <x-button type="submit" variant="secondary" size="lg">
                                {{ $diKoleksi ? 'Hapus dari Koleksi Pribadi' : 'Simpan ke Koleksi Pribadi' }}
                            </x-button>
                        </form>
                    </div>

                    @if (! $kelayakan['bisa'])
                        <p class="text-xs font-medium text-status-ditolak-text bg-status-ditolak px-4 py-2.5 rounded-card">
                            Alasan tidak dapat dipinjam: {{ $kelayakan['alasan'] }}
                        </p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Form Tulis Ulasan & Daftar Ulasan --}}
        <div class="mt-10 grid grid-cols-1 lg:grid-cols-12 gap-8">
            <div class="lg:col-span-5">
                <h2 class="font-sans text-lg font-semibold text-forest mb-4">
                    {{ $ulasanSaya ? 'Perbarui Ulasan Anda' : 'Tulis Ulasan & Rating' }}
                </h2>

                <form action="{{ route('member.katalog.ulasan', $buku) }}" method="POST" class="bg-ivory-paper border border-sage/25 rounded-card p-5 space-y-4">
                    @csrf
                    <div>
                        <label for="rating" class="block text-xs font-semibold uppercase tracking-wider text-charcoal mb-1.5">
                            Pilih Rating (1 - 5) <span class="text-status-ditolak-text">*</span>
                        </label>
                        <select id="rating" name="rating" required class="w-full rounded-card border border-sage/35 bg-ivory-paper px-3.5 py-2.5 text-sm text-charcoal focus:border-forest focus:outline-none">
                            @for ($i = 5; $i >= 1; $i--)
                                <option value="{{ $i }}" @selected((int) old('rating', $ulasanSaya?->rating ?? 5) === $i)>
                                    {{ $i }} Bintang — {{ $i === 5 ? 'Sangat Direkomendasikan' : ($i === 4 ? 'Bagus' : ($i === 3 ? 'Cukup' : ($i === 2 ? 'Kurang' : 'Tidak Direkomendasikan'))) }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <div>
                        <label for="ulasan" class="block text-xs font-semibold uppercase tracking-wider text-charcoal mb-1.5">
                            Ulasan Anda <span class="text-status-ditolak-text">*</span>
                        </label>
                        <textarea
                            id="ulasan"
                            name="ulasan"
                            rows="4"
                            required
                            placeholder="Bagikan kesan dan manfaat yang Anda peroleh dari buku ini..."
                            class="w-full rounded-card border border-sage/35 bg-ivory-paper px-3.5 py-2.5 text-sm text-charcoal focus:border-forest focus:outline-none"
                        >{{ old('ulasan', $ulasanSaya?->ulasan) }}</textarea>
                        @error('ulasan')
                            <p class="mt-1 text-xs text-status-ditolak-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-button type="submit" variant="primary" class="w-full">
                        Simpan Ulasan
                    </x-button>
                </form>
            </div>

            <div class="lg:col-span-7">
                <h2 class="font-sans text-lg font-semibold text-forest mb-4">
                    Daftar Ulasan Pembaca ({{ $buku->ulasan->count() }})
                </h2>

                @if ($buku->ulasan->isEmpty())
                    <x-empty-state judul="Belum Ada Ulasan" deskripsi="Belum ada anggota yang menuliskan ulasan untuk buku ini." />
                @else
                    <div class="divide-y divide-sage/20 border-y border-sage/20">
                        @foreach ($buku->ulasan as $item)
                            <div class="py-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-semibold text-charcoal">{{ $item->user->nama_lengkap }}</span>
                                    <span class="px-2.5 py-0.5 rounded-card text-xs font-semibold bg-gold/20 text-charcoal">
                                        Rating {{ $item->rating }}/5
                                    </span>
                                </div>
                                <p class="mt-1.5 text-sm text-charcoal-light leading-relaxed">{{ $item->ulasan }}</p>
                                <p class="mt-1 text-[11px] text-sage">{{ $item->updated_at?->format('d M Y H:i') }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>

