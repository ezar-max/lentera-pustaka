<x-layouts.app title="Koleksi Pribadi">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="pb-6 border-b border-sage/20 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-sage">Daftar Bacaan Favorit</p>
                <h1 class="mt-1 font-sans text-2xl sm:text-3xl font-semibold text-forest">Koleksi Pribadi Saya</h1>
            </div>
            <x-button href="{{ route('member.katalog') }}" variant="secondary">
                + Tambah dari Katalog
            </x-button>
        </div>

        <div class="mt-8">
            @if ($koleksiList->isEmpty())
                <x-empty-state
                    judul="Koleksi Pribadi Masih Kosong"
                    deskripsi="Simpan buku favorit atau daftar bacaan yang ingin Anda pinjam nanti melalui halaman Katalog."
                >
                    <x-button href="{{ route('member.katalog') }}" variant="primary">
                        Jelajahi Katalog Buku
                    </x-button>
                </x-empty-state>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach ($koleksiList as $item)
                        @php
                            $buku = $item->buku;
                            $kelayakan = $kelayakanMap[$buku->id] ?? ['bisa' => false, 'alasan' => ''];
                        @endphp
                        <article class="bg-ivory-paper border border-sage/25 rounded-card p-5 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between gap-2 mb-3">
                                    <span class="text-[11px] font-medium text-sage truncate">
                                        {{ $buku->kategori->pluck('nama_kategori')->join(', ') }}
                                    </span>
                                    <span class="px-2.5 py-0.5 rounded-card text-[11px] font-semibold {{ $buku->stok_tersedia > 0 ? 'bg-status-disetujui text-status-disetujui-text' : 'bg-status-ditolak text-status-ditolak-text' }}">
                                        Stok: {{ $buku->stok_tersedia }}/{{ $buku->stok_total }}
                                    </span>
                                </div>

                                <h2 class="font-semibold text-base text-charcoal leading-snug">
                                    <a href="{{ route('member.katalog.show', $buku) }}" class="hover:text-forest">
                                        {{ $buku->judul }}
                                    </a>
                                </h2>
                                <p class="mt-1 text-xs text-sage">{{ $buku->penulis }} ({{ $buku->tahun_terbit }})</p>
                                <p class="mt-2.5 text-xs text-charcoal-light line-clamp-3">{{ $buku->sinopsis }}</p>
                            </div>

                            <div class="mt-5 pt-3 border-t border-sage/15 space-y-2">
                                @if ($kelayakan['bisa'])
                                    <form action="{{ route('member.peminjaman.store') }}" method="POST"
                                          data-confirm="Ajukan peminjaman buku '{{ $buku->judul }}'?"
                                          data-confirm-title="Shortcut Pinjam Buku"
                                          data-confirm-button="Ya, Ajukan Pinjam">
                                        @csrf
                                        <input type="hidden" name="buku_id" value="{{ $buku->id }}">
                                        <x-button type="submit" variant="primary" size="sm" class="w-full">
                                            Ajukan Pinjam Sekarang
                                        </x-button>
                                    </form>
                                @else
                                    <button type="button" disabled class="w-full px-3 py-2 rounded-card text-xs font-medium bg-ivory-dark text-sage cursor-not-allowed">
                                        {{ $kelayakan['alasan'] ?: 'Tidak Dapat Dipinjam' }}
                                    </button>
                                @endif

                                <form action="{{ route('member.koleksi.destroy', $item) }}" method="POST"
                                      data-confirm="Hapus '{{ $buku->judul }}' dari Koleksi Pribadi Anda?"
                                      data-confirm-title="Hapus dari Koleksi"
                                      data-confirm-button="Ya, Hapus">
                                    @csrf
                                    @method('DELETE')
                                    <x-button type="submit" variant="danger" size="sm" class="w-full">
                                        Hapus dari Koleksi
                                    </x-button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>

