<x-layouts.dashboard title="Verifikasi Pengembalian" header="Verifikasi Pengembalian Dua Tahap" subheader="Setujui pengajuan kembali dari member, lalu periksa kondisi buku dan ketepatan waktu saat buku diserahkan.">
    @php
        $prefix = auth()->user()->isAdmin() && request()->is('admin*') ? 'admin' : 'petugas';
    @endphp

    {{-- Tab Tahap Pengembalian --}}
    <div class="flex flex-wrap items-center gap-2 pb-6 border-b border-sage/20">
        <a href="{{ route($prefix . '.pengembalian.index', ['tab' => 'pengajuan']) }}"
           class="px-4 py-2 rounded-card text-xs font-semibold transition-colors {{ $tab === 'pengajuan' ? 'bg-forest text-ivory' : 'bg-ivory-paper text-charcoal border border-sage/30' }}">
            1. Pengajuan Kembali ({{ $counts['pengajuan'] }})
        </a>
        <a href="{{ route($prefix . '.pengembalian.index', ['tab' => 'menunggu_buku']) }}"
           class="px-4 py-2 rounded-card text-xs font-semibold transition-colors {{ $tab === 'menunggu_buku' ? 'bg-forest text-ivory' : 'bg-ivory-paper text-charcoal border border-sage/30' }}">
            2. Menunggu Fisik Buku ({{ $counts['menunggu_buku'] }})
        </a>
        <a href="{{ route($prefix . '.pengembalian.index', ['tab' => 'selesai']) }}"
           class="px-4 py-2 rounded-card text-xs font-semibold transition-colors {{ $tab === 'selesai' ? 'bg-forest text-ivory' : 'bg-ivory-paper text-charcoal border border-sage/30' }}">
            Riwayat Selesai ({{ $counts['selesai'] }})
        </a>
    </div>

    <div class="mt-6">
        @if ($peminjamanList->isEmpty())
            <x-empty-state judul="Tidak Ada Antrean Pengembalian" deskripsi="Belum ada transaksi pengembalian pada tahap ini." />
        @else
            <x-table>
                <x-slot:head>
                    <tr>
                        <th class="px-4 py-3.5">Peminjam</th>
                        <th class="px-4 py-3.5">Buku</th>
                        <th class="px-4 py-3.5">Tenggat & Ketepatan</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5 text-right">Aksi Konfirmasi</th>
                    </tr>
                </x-slot:head>

                @foreach ($peminjamanList as $pinjam)
                    @php
                        $hariTerlambatLive = $pinjam->status === 'selesai' ? $pinjam->hari_terlambat : $pinjam->hitungHariTerlambatSaatIni();
                        $labelKeterlambatan = $hariTerlambatLive > 0
                            ? "Terlambat {$hariTerlambatLive} hari (+" . ($hariTerlambatLive * config('perpustakaan.denda.terlambat_per_hari', 2)) . " poin)"
                            : 'Tepat Waktu (0 poin)';
                    @endphp
                    <tr>
                        <td class="px-4 py-4">
                            <p class="font-semibold text-charcoal">{{ $pinjam->user->nama_lengkap }}</p>
                            <p class="text-xs text-sage">{{ '@' . $pinjam->user->username }}</p>
                        </td>
                        <td class="px-4 py-4">
                            <p class="font-semibold text-charcoal">{{ $pinjam->buku->judul }}</p>
                            <p class="text-xs text-sage">Dipinjam: {{ $pinjam->tanggal_pinjam?->format('d M Y') }}</p>
                        </td>
                        <td class="px-4 py-4 text-xs">
                            <div>Jatuh Tempo: <strong>{{ $pinjam->tanggal_jatuh_tempo?->format('d M Y') }}</strong></div>
                            <div class="mt-1">
                                @if ($hariTerlambatLive > 0)
                                    <span class="text-status-ditolak-text font-semibold">{{ $labelKeterlambatan }}</span>
                                @else
                                    <span class="text-status-selesai-text font-semibold">Tepat Waktu</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <x-badge-status :status="$pinjam->status" />
                            @if ($pinjam->status === 'selesai')
                                <div class="mt-1 text-xs text-sage">
                                    Kondisi: <strong class="text-charcoal capitalize">{{ $pinjam->kondisi_buku }}</strong>
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-right whitespace-nowrap">
                            @if ($pinjam->status === 'pengajuan_kembali')
                                <div class="inline-flex items-center gap-2">
                                    <form action="{{ route($prefix . '.pengembalian.setujui', $pinjam) }}" method="POST"
                                          data-confirm="Setujui pengajuan pengembalian buku '{{ $pinjam->buku->judul }}'? Member akan diminta membawa fisik buku ke perpustakaan."
                                          data-confirm-title="Setujui Pengajuan Kembali"
                                          data-confirm-button="Ya, Setujui">
                                        @csrf
                                        <x-button type="submit" variant="primary" size="sm">
                                            Setujui Pengajuan
                                        </x-button>
                                    </form>

                                    <form action="{{ route($prefix . '.pengembalian.konfirmasi', $pinjam) }}" method="POST"
                                          data-return-confirm="true"
                                          data-buku="{{ $pinjam->buku->judul }}"
                                          data-peminjam="{{ $pinjam->user->nama_lengkap }}"
                                          data-keterlambatan="{{ $labelKeterlambatan }}">
                                        @csrf
                                        <x-button type="submit" variant="gold" size="sm">
                                            Konfirmasi Fisik Langsung
                                        </x-button>
                                    </form>
                                </div>
                            @elseif ($pinjam->status === 'kembali_disetujui')
                                <form action="{{ route($prefix . '.pengembalian.konfirmasi', $pinjam) }}" method="POST"
                                      data-return-confirm="true"
                                      data-buku="{{ $pinjam->buku->judul }}"
                                      data-peminjam="{{ $pinjam->user->nama_lengkap }}"
                                      data-keterlambatan="{{ $labelKeterlambatan }}">
                                    @csrf
                                    <x-button type="submit" variant="gold" size="sm">
                                        Konfirmasi Pengembalian Fisik
                                    </x-button>
                                </form>
                            @else
                                <span class="text-xs text-sage">
                                    Diterima {{ $pinjam->tanggal_kembali?->format('d M Y') }}
                                </span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-table>

            <div class="mt-6">
                <x-pagination :paginator="$peminjamanList" />
            </div>
        @endif
    </div>
</x-layouts.dashboard>

