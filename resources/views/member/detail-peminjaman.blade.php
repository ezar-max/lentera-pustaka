<x-layouts.app title="Detail & Timeline Peminjaman">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="mb-6">
            <a href="{{ route('member.peminjaman.index') }}" class="text-xs font-semibold uppercase tracking-wider text-sage hover:text-forest">
                &larr; Kembali ke Daftar Peminjaman Saya
            </a>
        </div>

        {{-- Header Transaksi --}}
        <div class="bg-ivory-paper border border-sage/25 rounded-card p-6 sm:p-8">
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 pb-6 border-b border-sage/20">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-xs font-mono text-sage">ID Transaksi #{{ $peminjaman->id }}</span>
                        <x-badge-status :status="$peminjaman->status" />
                    </div>
                    <h1 class="font-sans text-2xl font-semibold text-forest">{{ $peminjaman->buku->judul }}</h1>
                    <p class="text-sm text-sage mt-0.5">{{ $peminjaman->buku->penulis }} · {{ $peminjaman->buku->penerbit }}</p>
                </div>

                <div class="flex items-center gap-2">
                    @if ($peminjaman->status === 'diajukan')
                        <form action="{{ route('member.peminjaman.batalkan', $peminjaman) }}" method="POST"
                              data-confirm="Apakah Anda yakin ingin membatalkan pengajuan peminjaman ini?"
                              data-confirm-title="Batalkan Pengajuan"
                              data-confirm-button="Ya, Batalkan">
                            @csrf
                            <x-button type="submit" variant="danger" size="sm">Batalkan Pengajuan</x-button>
                        </form>
                    @elseif ($peminjaman->status === 'dipinjam')
                        <form action="{{ route('member.peminjaman.ajukan-kembali', $peminjaman) }}" method="POST"
                              data-confirm="Ajukan pengembalian buku ini sekarang?"
                              data-confirm-title="Ajukan Pengembalian"
                              data-confirm-button="Ya, Ajukan">
                            @csrf
                            <x-button type="submit" variant="gold" size="sm">Ajukan Pengembalian</x-button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Kotak Alasan Penolakan (Jika Ditolak) --}}
            @if ($peminjaman->status === 'ditolak')
                <div class="mt-6 p-4 rounded-card bg-status-ditolak border border-status-ditolak-text/20 text-status-ditolak-text text-sm">
                    <strong class="font-semibold block">Pengajuan Ditolak oleh Petugas</strong>
                    <p class="mt-1">Alasan: {{ $peminjaman->alasan_penolakan }}</p>
                </div>
            @endif

            {{-- Ringkasan Tanggal & Hasil Konfirmasi --}}
            <dl class="mt-6 grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs pb-6 border-b border-sage/20">
                <div>
                    <dt class="text-sage uppercase">Tanggal Pengajuan</dt>
                    <dd class="mt-1 font-semibold text-charcoal">{{ $peminjaman->tanggal_pengajuan->format('d M Y') }}</dd>
                </div>
                <div>
                    <dt class="text-sage uppercase">Tanggal Disetujui</dt>
                    <dd class="mt-1 font-semibold text-charcoal">{{ $peminjaman->tanggal_disetujui?->format('d M Y') ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sage uppercase">Tanggal Pinjam & Tenggat</dt>
                    <dd class="mt-1 font-semibold text-charcoal">
                        {{ $peminjaman->tanggal_pinjam?->format('d M Y') ?? '-' }}
                        @if ($peminjaman->tanggal_jatuh_tempo)
                            <span class="block text-forest">s.d. {{ $peminjaman->tanggal_jatuh_tempo->format('d M Y') }}</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-sage uppercase">Tanggal Kembali</dt>
                    <dd class="mt-1 font-semibold text-charcoal">{{ $peminjaman->tanggal_kembali?->format('d M Y') ?? '-' }}</dd>
                </div>
            </dl>

            {{-- Timeline 7 Langkah Alur Sirkulasi --}}
            @php
                $urutanStatus = ['diajukan', 'disetujui', 'dipinjam', 'pengajuan_kembali', 'kembali_disetujui', 'selesai'];
                $posisiSaatIni = array_search($peminjaman->status, $urutanStatus, true);

                $langkahTimeline = [
                    [
                        'judul' => '1. Pengajuan Peminjaman Dikirim',
                        'deskripsi' => 'Anggota mengajukan peminjaman buku melalui katalog sistem.',
                        'tanggal' => $peminjaman->tanggal_pengajuan?->format('d M Y'),
                        'selesai' => true,
                    ],
                    [
                        'judul' => '2. Keputusan Verifikasi Petugas',
                        'deskripsi' => $peminjaman->status === 'ditolak'
                            ? 'Ditolak dengan alasan: ' . $peminjaman->alasan_penolakan
                            : 'Petugas memeriksa ketersediaan dan menyetujui pengajuan (stok dikurangi 1).',
                        'tanggal' => $peminjaman->tanggal_disetujui?->format('d M Y'),
                        'selesai' => $posisiSaatIni !== false && $posisiSaatIni >= 1,
                        'gagal' => $peminjaman->status === 'ditolak',
                    ],
                    [
                        'judul' => '3. Pengambilan & Penyerahan Buku Fisik',
                        'deskripsi' => $peminjaman->status === 'dibatalkan'
                            ? ($peminjaman->catatan_petugas ?: 'Pengajuan dibatalkan.')
                            : 'Anggota mengambil buku di perpustakaan (maksimal 2 hari setelah disetujui).',
                        'tanggal' => $peminjaman->tanggal_pinjam?->format('d M Y'),
                        'selesai' => $posisiSaatIni !== false && $posisiSaatIni >= 2,
                        'gagal' => $peminjaman->status === 'dibatalkan',
                    ],
                    [
                        'judul' => '4. Masa Peminjaman Berjalan',
                        'deskripsi' => 'Batas waktu peminjaman selama 7 hari kalender.',
                        'tanggal' => $peminjaman->tanggal_jatuh_tempo ? 'Jatuh tempo: ' . $peminjaman->tanggal_jatuh_tempo->format('d M Y') : null,
                        'selesai' => $posisiSaatIni !== false && $posisiSaatIni >= 2,
                    ],
                    [
                        'judul' => '5. Pengajuan Pengembalian oleh Anggota',
                        'deskripsi' => 'Anggota meminta pengembalian buku secara online melalui sistem.',
                        'tanggal' => $peminjaman->tanggal_pengajuan_kembali?->format('d M Y'),
                        'selesai' => $posisiSaatIni !== false && $posisiSaatIni >= 3,
                    ],
                    [
                        'judul' => '6. Persetujuan Pengembalian oleh Petugas',
                        'deskripsi' => 'Petugas menyetujui pengajuan kembali; anggota dipersilakan menyerahkan buku fisik.',
                        'tanggal' => $posisiSaatIni !== false && $posisiSaatIni >= 4 ? 'Disetujui Petugas' : null,
                        'selesai' => $posisiSaatIni !== false && $posisiSaatIni >= 4,
                    ],
                    [
                        'judul' => '7. Konfirmasi Fisik, Kondisi & Selesai',
                        'deskripsi' => $peminjaman->status === 'selesai'
                            ? 'Diterima dalam kondisi ' . strtoupper((string) $peminjaman->kondisi_buku) . ' (' . str_replace('_', ' ', (string) $peminjaman->ketepatan_waktu) . ').'
                            : 'Petugas memeriksa kondisi buku (baik/rusak/hilang), ketepatan waktu, dan memperbarui stok.',
                        'tanggal' => $peminjaman->tanggal_kembali?->format('d M Y'),
                        'selesai' => $peminjaman->status === 'selesai',
                    ],
                ];
            @endphp

            <div class="mt-6">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-forest mb-5">Timeline 7 Langkah Alur Transaksi</h2>

                <ol class="relative border-s border-sage/35 ms-3 space-y-6">
                    @foreach ($langkahTimeline as $step)
                        <li class="ms-6">
                            <span class="absolute flex items-center justify-center w-6 h-6 rounded-card -start-3 ring-4 ring-ivory-paper
                                {{ !empty($step['gagal']) ? 'bg-status-ditolak-text text-ivory' : ($step['selesai'] ? 'bg-forest text-gold' : 'bg-ivory-dark text-sage') }}">
                                @if (!empty($step['gagal']))
                                    &times;
                                @elseif ($step['selesai'])
                                    &check;
                                @else
                                    &bull;
                                @endif
                            </span>

                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                                <h3 class="text-sm font-semibold {{ $step['selesai'] ? 'text-charcoal' : 'text-sage' }}">
                                    {{ $step['judul'] }}
                                </h3>
                                @if ($step['tanggal'])
                                    <span class="text-xs font-medium text-forest bg-ivory-dark px-2.5 py-0.5 rounded-card">
                                        {{ $step['tanggal'] }}
                                    </span>
                                @endif
                            </div>
                            <p class="mt-1 text-xs text-sage leading-relaxed">{{ $step['deskripsi'] }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>

            {{-- Rincian Hasil Konfirmasi --}}
            @if ($peminjaman->status === 'selesai')
                <div class="mt-8 pt-6 border-t border-sage/20">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-sage mb-3">Hasil Pemeriksaan Pengembalian</h3>
                    <div class="flex flex-wrap items-center gap-3 text-xs">
                        @if ($peminjaman->kondisi_buku)
                            <x-badge-status :status="$peminjaman->kondisi_buku" />
                        @endif
                        @if ($peminjaman->ketepatan_waktu)
                            <x-badge-status :status="$peminjaman->ketepatan_waktu" />
                        @endif
                        @if ($peminjaman->petugas)
                            <span class="text-sage">Diverifikasi oleh: <strong class="text-charcoal">{{ $peminjaman->petugas->nama_lengkap }}</strong></span>
                        @endif
                    </div>

                    @if ($peminjaman->catatan_petugas)
                        <p class="mt-3 text-xs text-charcoal bg-ivory p-3.5 rounded-card border border-sage/20">
                            <strong>Catatan Petugas:</strong> {{ $peminjaman->catatan_petugas }}
                        </p>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>

