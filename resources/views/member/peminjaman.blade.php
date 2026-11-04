<x-layouts.app title="Peminjaman Saya">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="pb-6 border-b border-sage/20 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-sage">Riwayat & Transaksi</p>
                <h1 class="mt-1 font-sans text-2xl sm:text-3xl font-semibold text-forest">Daftar Peminjaman Saya</h1>
            </div>

            <form action="{{ route('member.peminjaman.index') }}" method="GET" class="flex items-center gap-2">
                <select name="status" onchange="this.form.submit()" class="rounded-card border border-sage/35 bg-ivory-paper px-3.5 py-2 text-xs font-medium text-charcoal focus:border-forest focus:outline-none">
                    <option value="">Semua Status Transaksi</option>
                    @foreach (['diajukan', 'disetujui', 'dipinjam', 'pengajuan_kembali', 'kembali_disetujui', 'selesai', 'ditolak', 'dibatalkan'] as $st)
                        <option value="{{ $st }}" @selected(request('status') === $st)>
                            {{ ucfirst(str_replace('_', ' ', $st)) }}
                        </option>
                    @endforeach
                </select>
                @if (request()->filled('status'))
                    <x-button href="{{ route('member.peminjaman.index') }}" variant="secondary" size="sm">Reset</x-button>
                @endif
            </form>
        </div>

        <div class="mt-8">
            @if ($peminjamanList->isEmpty())
                <x-empty-state
                    judul="Belum Ada Transaksi Peminjaman"
                    deskripsi="Anda belum memiliki transaksi peminjaman dengan kriteria status ini."
                >
                    <x-button href="{{ route('member.katalog') }}" variant="primary">
                        Telusuri Katalog Buku
                    </x-button>
                </x-empty-state>
            @else
                <x-table>
                    <x-slot:head>
                        <tr>
                            <th class="px-4 py-3.5">Buku</th>
                            <th class="px-4 py-3.5">Tanggal Pengajuan</th>
                            <th class="px-4 py-3.5">Batas Ambil / Jatuh Tempo</th>
                            <th class="px-4 py-3.5">Status</th>
                            <th class="px-4 py-3.5">Keterangan / Alasan</th>
                            <th class="px-4 py-3.5 text-right">Aksi</th>
                        </tr>
                    </x-slot:head>

                    @foreach ($peminjamanList as $pinjam)
                        <tr class="hover:bg-ivory/60 transition-colors">
                            <td class="px-4 py-4">
                                <a href="{{ route('member.peminjaman.show', $pinjam) }}" class="font-semibold text-charcoal hover:text-forest block">
                                    {{ $pinjam->buku->judul }}
                                </a>
                                <span class="text-xs text-sage">{{ $pinjam->buku->penulis }}</span>
                            </td>
                            <td class="px-4 py-4 text-xs text-charcoal">
                                {{ $pinjam->tanggal_pengajuan->format('d M Y') }}
                            </td>
                            <td class="px-4 py-4 text-xs">
                                @if ($pinjam->status === 'disetujui')
                                    <span class="text-sage block">Batas Ambil:</span>
                                    <strong class="text-forest">{{ $pinjam->batas_pengambilan?->format('d M Y') }}</strong>
                                @elseif ($pinjam->tanggal_jatuh_tempo)
                                    <span class="text-sage block">Jatuh Tempo:</span>
                                    <strong class="{{ $pinjam->isTerlambatSaatIni() ? 'text-status-ditolak-text' : 'text-charcoal' }}">
                                        {{ $pinjam->tanggal_jatuh_tempo->format('d M Y') }}
                                    </strong>
                                @else
                                    <span class="text-sage">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <x-badge-status :status="$pinjam->status" />
                            </td>
                            <td class="px-4 py-4 text-xs max-w-xs">
                                @if ($pinjam->status === 'ditolak' && $pinjam->alasan_penolakan)
                                    <span class="text-status-ditolak-text font-medium">Alasan ditolak: {{ $pinjam->alasan_penolakan }}</span>
                                @elseif ($pinjam->status === 'selesai')
                                    <span class="text-charcoal">
                                        Kondisi: <strong>{{ ucfirst($pinjam->kondisi_buku ?? 'baik') }}</strong> ·
                                        {{ $pinjam->ketepatan_waktu === 'terlambat' ? 'Terlambat ' . $pinjam->hari_terlambat . ' hari' : 'Tepat Waktu' }}
                                    </span>
                                @elseif ($pinjam->catatan_petugas)
                                    <span class="text-sage">{{ $pinjam->catatan_petugas }}</span>
                                @else
                                    <span class="text-sage">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-2">
                                    @if ($pinjam->status === 'diajukan')
                                        <form action="{{ route('member.peminjaman.batalkan', $pinjam) }}" method="POST"
                                              data-confirm="Batalkan pengajuan peminjaman buku '{{ $pinjam->buku->judul }}'?"
                                              data-confirm-title="Batalkan Pengajuan"
                                              data-confirm-button="Ya, Batalkan">
                                            @csrf
                                            <x-button type="submit" variant="danger" size="sm">
                                                Batalkan
                                            </x-button>
                                        </form>
                                    @elseif ($pinjam->status === 'dipinjam')
                                        <form action="{{ route('member.peminjaman.ajukan-kembali', $pinjam) }}" method="POST"
                                              data-confirm="Ajukan pengembalian untuk buku '{{ $pinjam->buku->judul }}'?"
                                              data-confirm-title="Ajukan Pengembalian"
                                              data-confirm-button="Ya, Ajukan Kembali">
                                            @csrf
                                            <x-button type="submit" variant="gold" size="sm">
                                                Ajukan Pengembalian
                                            </x-button>
                                        </form>
                                    @endif

                                    <x-button href="{{ route('member.peminjaman.show', $pinjam) }}" variant="secondary" size="sm">
                                        Timeline
                                    </x-button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-table>

                <div class="mt-6">
                    <x-pagination :paginator="$peminjamanList" />
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>

