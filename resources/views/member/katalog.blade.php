<x-layouts.app title="Katalog & Peminjaman Buku">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        {{-- Header & Filter --}}
        <div class="pb-8 border-b border-sage/20">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-sage">Katalog Anggota</p>
                    <h1 class="mt-1 font-sans text-2xl sm:text-3xl font-semibold text-forest">Cari & Ajukan Peminjaman Buku</h1>
                </div>
            </div>

            <form action="{{ route('member.katalog') }}" method="GET" class="mt-6 grid grid-cols-1 sm:grid-cols-12 gap-3">
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
                    <x-button type="submit" variant="primary" class="w-full">Filter</x-button>
                    @if (request()->hasAny(['q', 'kategori', 'urut']))
                        <x-button href="{{ route('member.katalog') }}" variant="secondary">Reset</x-button>
                    @endif
                </div>
            </form>
        </div>

        {{-- Grid Katalog --}}
        <div class="mt-8">
            @if ($bukuList->isEmpty())
                <x-empty-state
                    judul="Buku Tidak Ditemukan"
                    deskripsi="Tidak ada buku yang sesuai dengan pencarian Anda."
                />
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach ($bukuList as $buku)
                        @php
                            $kelayakan = $kelayakanMap[$buku->id] ?? ['bisa' => false, 'alasan' => 'Tidak tersedia'];
                            $tersimpan = in_array($buku->id, $koleksiIds, true);
                        @endphp
                        <article class="bg-ivory-paper border border-sage/25 rounded-card p-5 flex flex-col justify-between">
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
                                        Stok: {{ $buku->stok_tersedia }}/{{ $buku->stok_total }}
                                    </span>
                                </div>

                                <div class="flex flex-wrap gap-1 mb-2">
                                    @foreach ($buku->kategori as $kat)
                                        <span class="text-[11px] font-medium text-sage bg-ivory-dark px-2 py-0.5 rounded-card">
                                            {{ $kat->nama_kategori }}
                                        </span>
                                    @endforeach
                                </div>

                                <h2 class="font-semibold text-base text-charcoal leading-snug line-clamp-2">
                                    <a href="{{ route('member.katalog.show', $buku) }}" class="hover:text-forest">
                                        {{ $buku->judul }}
                                    </a>
                                </h2>
                                <p class="mt-1 text-xs text-sage">{{ $buku->penulis }} · {{ $buku->tahun_terbit }}</p>
                            </div>

                            <div class="mt-4 pt-3 border-t border-sage/15 space-y-2">
                                @if ($kelayakan['bisa'])
                                    <form action="{{ route('member.peminjaman.store') }}" method="POST"
                                          data-confirm="Ajukan peminjaman untuk buku '{{ $buku->judul }}'?"
                                          data-confirm-title="Konfirmasi Pengajuan Pinjam"
                                          data-confirm-button="Ya, Ajukan Pinjam">
                                        @csrf
                                        <input type="hidden" name="buku_id" value="{{ $buku->id }}">
                                        <x-button type="submit" variant="primary" size="sm" class="w-full">
                                            Ajukan Pinjam
                                        </x-button>
                                    </form>
                                @else
                                    <button type="button" disabled class="w-full px-3 py-2 rounded-card text-xs font-medium bg-ivory-dark text-sage border border-sage/20 cursor-not-allowed">
                                        Tidak Dapat Dipinjam
                                    </button>
                                    <p class="text-[11px] text-status-ditolak-text leading-tight">
                                        {{ $kelayakan['alasan'] }}
                                    </p>
                                @endif

                                <div class="flex items-center justify-between gap-2 pt-1">
                                    <form action="{{ route('member.koleksi.toggle', $buku) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-xs font-medium {{ $tersimpan ? 'text-gold-hover font-semibold' : 'text-sage hover:text-charcoal' }}">
                                            {{ $tersimpan ? 'Tersimpan di Koleksi' : '+ Simpan Koleksi' }}
                                        </button>
                                    </form>

                                    <a href="{{ route('member.katalog.show', $buku) }}" class="text-xs font-semibold text-forest hover:underline">
                                        Detail & Ulasan
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-8">
                    <x-pagination :paginator="$bukuList" />
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>

