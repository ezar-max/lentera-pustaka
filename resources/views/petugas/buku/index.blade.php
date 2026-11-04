<x-layouts.dashboard title="Data Buku & Stok" header="Pendataan Koleksi Buku & Stok" subheader="Kelola katalog buku, sampul, relasi multi-kategori, serta pantau stok total dan stok tersedia.">
    @php
        $prefix = auth()->user()->isAdmin() && request()->is('admin*') ? 'admin' : 'petugas';
    @endphp

    {{-- Bar Pencarian, Filter & Tombol Tambah --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-6 border-b border-sage/20">
        <form action="{{ route($prefix . '.buku.index') }}" method="GET" class="flex flex-wrap items-center gap-2.5 flex-1">
            <input
                type="text"
                name="q"
                value="{{ request('q') }}"
                placeholder="Cari judul, penulis, penerbit, ISBN..."
                class="w-full sm:w-64 rounded-card border border-sage/35 bg-ivory-paper px-3.5 py-2 text-xs text-charcoal focus:border-forest focus:outline-none"
            >
            <select name="kategori" class="rounded-card border border-sage/35 bg-ivory-paper px-3.5 py-2 text-xs text-charcoal focus:border-forest focus:outline-none">
                <option value="">Semua Kategori</option>
                @foreach ($kategoriList as $kat)
                    <option value="{{ $kat->id }}" @selected((string) request('kategori') === (string) $kat->id)>
                        {{ $kat->nama_kategori }}
                    </option>
                @endforeach
            </select>

            <label class="inline-flex items-center gap-1.5 text-xs text-charcoal px-2 cursor-pointer">
                <input type="checkbox" name="menipis" value="1" @checked(request()->boolean('menipis')) class="rounded border-sage/40 text-forest">
                <span>Stok Menipis</span>
            </label>

            <x-button type="submit" variant="secondary" size="sm">Cari / Filter</x-button>
            @if (request()->hasAny(['q', 'kategori', 'menipis']))
                <x-button href="{{ route($prefix . '.buku.index') }}" variant="secondary" size="sm">Reset</x-button>
            @endif
        </form>

        <x-button href="{{ route($prefix . '.buku.create') }}" variant="primary">
            + Tambah Buku Baru
        </x-button>
    </div>

    {{-- Tabel Data Buku --}}
    <div class="mt-6">
        @if ($bukuList->isEmpty())
            <x-empty-state judul="Data Buku Tidak Ditemukan" deskripsi="Belum ada koleksi buku yang sesuai dengan filter pencarian Anda." />
        @else
            <x-table>
                <x-slot:head>
                    <tr>
                        <th class="px-4 py-3.5">Sampul</th>
                        <th class="px-4 py-3.5">Judul & Penulis</th>
                        <th class="px-4 py-3.5">Kategori</th>
                        <th class="px-4 py-3.5">Penerbit / Tahun</th>
                        <th class="px-4 py-3.5 text-center">Stok Total</th>
                        <th class="px-4 py-3.5 text-center">Stok Tersedia</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </x-slot:head>

                @foreach ($bukuList as $buku)
                    <tr class="hover:bg-ivory/60 transition-colors">
                        <td class="px-4 py-3">
                            <div class="w-11 h-14 rounded-card bg-ivory-dark border border-sage/20 overflow-hidden flex items-center justify-center text-[10px] text-sage font-sans">
                                @if ($buku->cover)
                                    <img src="{{ asset('storage/' . $buku->cover) }}" alt="{{ $buku->judul }}" class="w-full h-full object-cover">
                                @else
                                    Sampul
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-semibold text-charcoal">{{ $buku->judul }}</p>
                            <p class="text-xs text-sage">{{ $buku->penulis }} @if($buku->isbn) · ISBN {{ $buku->isbn }} @endif</p>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1">
                                @foreach ($buku->kategori as $kat)
                                    <span class="px-2 py-0.5 rounded-card text-[11px] bg-ivory-dark text-charcoal">
                                        {{ $kat->nama_kategori }}
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-4 py-3 text-xs text-charcoal">
                            {{ $buku->penerbit }} ({{ $buku->tahun_terbit }})
                        </td>
                        <td class="px-4 py-3 text-center font-semibold text-charcoal">
                            {{ $buku->stok_total }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2.5 py-1 rounded-card text-xs font-semibold {{ $buku->isStokMenipis() ? 'bg-status-diajukan text-status-diajukan-text' : 'bg-status-disetujui text-status-disetujui-text' }}">
                                {{ $buku->stok_tersedia }}
                                @if ($buku->isStokMenipis())
                                    (Menipis)
                                @endif
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <div class="inline-flex items-center gap-2">
                                <x-button href="{{ route($prefix . '.buku.edit', $buku) }}" variant="secondary" size="sm">
                                    Ubah
                                </x-button>

                                <form action="{{ route($prefix . '.buku.destroy', $buku) }}" method="POST"
                                      data-confirm="Hapus buku '{{ $buku->judul }}' secara permanen dari katalog?"
                                      data-confirm-title="Hapus Data Buku"
                                      data-confirm-button="Ya, Hapus Buku">
                                    @csrf
                                    @method('DELETE')
                                    <x-button type="submit" variant="danger" size="sm">
                                        Hapus
                                    </x-button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-table>

            <div class="mt-6">
                <x-pagination :paginator="$bukuList" />
            </div>
        @endif
    </div>
</x-layouts.dashboard>

