<x-layouts.dashboard title="Verifikasi Peminjaman" header="Verifikasi & Penyerahan Peminjaman" subheader="Setujui atau tolak pengajuan dengan alasan wajib, serta serahkan buku fisik saat anggota datang.">
    @php
        $prefix = auth()->user()->isAdmin() && request()->is('admin*') ? 'admin' : 'petugas';
    @endphp

    {{-- Tab Status & Pencarian --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-sage/20">
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route($prefix . '.peminjaman.index', ['tab' => 'menunggu']) }}"
               class="px-4 py-2 rounded-card text-xs font-semibold transition-colors {{ $tab === 'menunggu' ? 'bg-forest text-ivory' : 'bg-ivory-paper text-charcoal border border-sage/30' }}">
                Menunggu Keputusan ({{ $counts['menunggu'] }})
            </a>
            <a href="{{ route($prefix . '.peminjaman.index', ['tab' => 'disetujui']) }}"
               class="px-4 py-2 rounded-card text-xs font-semibold transition-colors {{ $tab === 'disetujui' ? 'bg-forest text-ivory' : 'bg-ivory-paper text-charcoal border border-sage/30' }}">
                Disetujui · Belum Diambil ({{ $counts['disetujui'] }})
            </a>
            <a href="{{ route($prefix . '.peminjaman.index', ['tab' => 'dipinjam']) }}"
               class="px-4 py-2 rounded-card text-xs font-semibold transition-colors {{ $tab === 'dipinjam' ? 'bg-forest text-ivory' : 'bg-ivory-paper text-charcoal border border-sage/30' }}">
                Sedang Dipinjam ({{ $counts['dipinjam'] }})
            </a>
            <a href="{{ route($prefix . '.peminjaman.index', ['tab' => 'semua']) }}"
               class="px-4 py-2 rounded-card text-xs font-semibold transition-colors {{ $tab === 'semua' ? 'bg-forest text-ivory' : 'bg-ivory-paper text-charcoal border border-sage/30' }}">
                Semua Riwayat
            </a>
        </div>

        <form action="{{ route($prefix . '.peminjaman.index') }}" method="GET" class="flex items-center gap-2">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <input
                type="text"
                name="q"
                value="{{ request('q') }}"
                placeholder="Cari nama member atau judul..."
                class="rounded-card border border-sage/35 bg-ivory-paper px-3.5 py-2 text-xs text-charcoal focus:border-forest focus:outline-none"
            >
            <x-button type="submit" variant="secondary" size="sm">Cari</x-button>
        </form>
    </div>

    {{-- Tabel Transaksi --}}
    <div class="mt-6">
        @if ($peminjamanList->isEmpty())
            <x-empty-state judul="Tidak Ada Data pada Tab Ini" deskripsi="Belum ada transaksi peminjaman yang berada pada tahap ini." />
        @else
            <x-table>
                <x-slot:head>
                    <tr>
                        <th class="px-4 py-3.5">Peminjam</th>
                        <th class="px-4 py-3.5">Buku & Stok</th>
                        <th class="px-4 py-3.5">Tanggal Penting</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5 text-right">Tindakan Petugas</th>
                    </tr>
                </x-slot:head>

                @foreach ($peminjamanList as $pinjam)
                    <tr>
                        <td class="px-4 py-4">
                            <p class="font-semibold text-charcoal">{{ $pinjam->user->nama_lengkap }}</p>
                            <p class="text-xs text-sage">{{ '@' . $pinjam->user->username }}</p>
                        </td>
                        <td class="px-4 py-4">
                            <p class="font-semibold text-charcoal">{{ $pinjam->buku->judul }}</p>
                            <p class="text-xs text-sage">Stok tersedia saat ini: <strong>{{ $pinjam->buku->stok_tersedia }}</strong>/{{ $pinjam->buku->stok_total }}</p>
                        </td>
                        <td class="px-4 py-4 text-xs space-y-0.5">
                            <div><span class="text-sage">Diajukan:</span> {{ $pinjam->tanggal_pengajuan->format('d M Y') }}</div>
                            @if ($pinjam->tanggal_disetujui)
                                <div><span class="text-sage">Batas Ambil:</span> <strong class="text-forest">{{ $pinjam->batas_pengambilan?->format('d M Y') }}</strong></div>
                            @endif
                            @if ($pinjam->tanggal_jatuh_tempo)
                                <div>
                                    <span class="text-sage">Jatuh Tempo:</span>
                                    <strong class="{{ $pinjam->isTerlambatSaatIni() ? 'text-status-ditolak-text' : 'text-charcoal' }}">
                                        {{ $pinjam->tanggal_jatuh_tempo->format('d M Y') }}
                                    </strong>
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            <x-badge-status :status="$pinjam->status" />
                            @if ($pinjam->alasan_penolakan)
                                <p class="mt-1 text-[11px] text-status-ditolak-text max-w-xs">{{ $pinjam->alasan_penolakan }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-right whitespace-nowrap">
                            @if ($pinjam->status === 'diajukan')
                                <div class="inline-flex items-center gap-2">
                                    <form action="{{ route($prefix . '.peminjaman.setuju', $pinjam) }}" method="POST"
                                          data-confirm="Setujui peminjaman buku '{{ $pinjam->buku->judul }}' untuk {{ $pinjam->user->nama_lengkap }}? Stok tersedia akan dikurangi 1."
                                          data-confirm-title="Setujui Peminjaman"
                                          data-confirm-button="Ya, Setujui">
                                        @csrf
                                        <x-button type="submit" variant="primary" size="sm">
                                            Setujui
                                        </x-button>
                                    </form>

                                    <form action="{{ route($prefix . '.peminjaman.tolak', $pinjam) }}" method="POST"
                                          data-reason="Masukkan alasan penolakan pengajuan buku '{{ $pinjam->buku->judul }}' oleh {{ $pinjam->user->nama_lengkap }}."
                                          data-reason-title="Tolak Pengajuan Peminjaman"
                                          data-reason-placeholder="Tuliskan alasan penolakan (wajib diisi)...">
                                        @csrf
                                        <x-button type="submit" variant="danger" size="sm">
                                            Tolak
                                        </x-button>
                                    </form>
                                </div>
                            @elseif ($pinjam->status === 'disetujui')
                                <form action="{{ route($prefix . '.peminjaman.serahkan', $pinjam) }}" method="POST"
                                      data-confirm="Konfirmasi bahwa {{ $pinjam->user->nama_lengkap }} telah datang dan menerima fisik buku '{{ $pinjam->buku->judul }}'?"
                                      data-confirm-title="Serahkan Buku Fisik"
                                      data-confirm-button="Ya, Serahkan Buku">
                                    @csrf
                                    <x-button type="submit" variant="gold" size="sm">
                                        Serahkan Buku
                                    </x-button>
                                </form>
                            @else
                                <span class="text-xs text-sage">
                                    {{ $pinjam->petugas ? 'Oleh ' . $pinjam->petugas->nama_lengkap : '-' }}
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

