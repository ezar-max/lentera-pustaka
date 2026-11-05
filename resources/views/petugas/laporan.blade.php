<x-layouts.dashboard title="Generate Laporan" header="Laporan Sirkulasi & Stok" subheader="Pilih jenis laporan, atur rentang tanggal atau status, lihat pratinjau tabel, lalu unduh dalam format PDF.">
    @php
        $prefix = auth()->user()->isAdmin() && request()->is('admin*') ? 'admin' : 'petugas';
    @endphp

    {{-- Pilihan Jenis Laporan & Filter --}}
    <form action="{{ route($prefix . '.laporan.index') }}" method="GET" class="bg-ivory-paper border border-sage/25 rounded-card p-6 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-charcoal mb-1">Jenis Laporan</label>
                <select name="jenis" onchange="this.form.submit()" class="w-full rounded-card border border-sage/35 bg-ivory-paper px-3.5 py-2 text-xs font-semibold text-forest focus:border-forest focus:outline-none">
                    <option value="peminjaman" @selected($jenis === 'peminjaman')>1. Laporan Peminjaman</option>
                    <option value="stok" @selected($jenis === 'stok')>2. Laporan Stok Buku</option>
                    @if (auth()->user()->isAdmin())
                        <option value="member" @selected($jenis === 'member')>3. Laporan Keanggotaan (Admin)</option>
                    @endif
                </select>
            </div>

            @if ($jenis === 'peminjaman')
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-charcoal mb-1">Dari Tanggal</label>
                    <input type="date" name="tanggal_mulai" value="{{ $filters['tanggal_mulai'] ?? '' }}" class="w-full rounded-card border border-sage/35 bg-ivory-paper px-3.5 py-2 text-xs text-charcoal">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-charcoal mb-1">Sampai Tanggal</label>
                    <input type="date" name="tanggal_selesai" value="{{ $filters['tanggal_selesai'] ?? '' }}" class="w-full rounded-card border border-sage/35 bg-ivory-paper px-3.5 py-2 text-xs text-charcoal">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-charcoal mb-1">Status Transaksi</label>
                    <select name="status" class="w-full rounded-card border border-sage/35 bg-ivory-paper px-3.5 py-2 text-xs text-charcoal">
                        <option value="">Semua Status</option>
                        @foreach (['diajukan', 'disetujui', 'dipinjam', 'pengajuan_kembali', 'kembali_disetujui', 'selesai', 'ditolak', 'dibatalkan'] as $st)
                            <option value="{{ $st }}" @selected(($filters['status'] ?? '') === $st)>{{ ucfirst(str_replace('_', ' ', $st)) }}</option>
                        @endforeach
                    </select>
                </div>
            @elseif ($jenis === 'stok')
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-charcoal mb-1">Kategori Buku</label>
                    <select name="kategori_id" class="w-full rounded-card border border-sage/35 bg-ivory-paper px-3.5 py-2 text-xs text-charcoal">
                        <option value="">Semua Kategori</option>
                        @foreach (\App\Models\KategoriBuku::orderBy('nama_kategori')->get() as $k)
                            <option value="{{ $k->id }}" @selected((string)($filters['kategori_id'] ?? '') === (string)$k->id)>{{ $k->nama_kategori }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end pb-2">
                    <label class="inline-flex items-center gap-2 text-xs text-charcoal cursor-pointer">
                        <input type="checkbox" name="stok_menipis" value="1" @checked(!empty($filters['stok_menipis'])) class="rounded border-sage/40 text-forest">
                        <span>Hanya Stok Menipis</span>
                    </label>
                </div>
            @elseif ($jenis === 'member')
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-charcoal mb-1">Status Akun Member</label>
                    <select name="status_akun" class="w-full rounded-card border border-sage/35 bg-ivory-paper px-3.5 py-2 text-xs text-charcoal">
                        <option value="">Semua Status</option>
                        <option value="aktif" @selected(($filters['status_akun'] ?? '') === 'aktif')>Aktif</option>
                        <option value="pending" @selected(($filters['status_akun'] ?? '') === 'pending')>Pending</option>
                        <option value="ditolak" @selected(($filters['status_akun'] ?? '') === 'ditolak')>Ditolak</option>
                    </select>
                </div>
            @endif

            <div class="flex items-end gap-2">
                <x-button type="submit" variant="primary" size="sm" class="w-full">Terapkan Filter</x-button>
                <x-button href="{{ route($prefix . '.laporan.index', ['jenis' => $jenis]) }}" variant="secondary" size="sm">Reset</x-button>
            </div>
        </div>
    </form>

    {{-- Toolbar Cetak PDF --}}
    <div class="mt-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-sage/20">
        <div>
            <span class="text-xs text-sage">Ditemukan <strong>{{ $data->count() }}</strong> data laporan</span>
        </div>
        <div class="flex items-center gap-3">
            <x-button href="{{ route($prefix . '.laporan.pdf', request()->query()) }}" target="_blank" variant="secondary" size="sm">
                Unduh / Cetak Laporan PDF
            </x-button>
        </div>
    </div>

    {{-- Tabel Hasil Laporan --}}
    <div class="mt-6">
        @if ($data->isEmpty())
            <x-empty-state judul="Data Laporan Kosong" deskripsi="Tidak ditemukan data yang sesuai dengan kriteria filter laporan yang Anda terapkan." />
        @elseif ($jenis === 'peminjaman')
            <x-table>
                <x-slot:head>
                    <tr>
                        <th class="px-4 py-3">Peminjam</th>
                        <th class="px-4 py-3">Buku</th>
                        <th class="px-4 py-3">Tgl Pinjam</th>
                        <th class="px-4 py-3">Jatuh Tempo</th>
                        <th class="px-4 py-3">Tgl Kembali</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </x-slot:head>
                @foreach ($data as $row)
                    <tr>
                        <td class="px-4 py-3 text-xs font-semibold text-charcoal">{{ $row->user->nama_lengkap }}</td>
                        <td class="px-4 py-3 text-xs text-charcoal">{{ $row->buku->judul }}</td>
                        <td class="px-4 py-3 text-xs">{{ $row->tanggal_pinjam?->format('d/m/Y') ?? '-' }}</td>
                        <td class="px-4 py-3 text-xs">{{ $row->tanggal_jatuh_tempo?->format('d/m/Y') ?? '-' }}</td>
                        <td class="px-4 py-3 text-xs">{{ $row->tanggal_kembali?->format('d/m/Y') ?? '-' }}</td>
                        <td class="px-4 py-3"><x-badge-status :status="$row->status" /></td>
                    </tr>
                @endforeach
            </x-table>
        @elseif ($jenis === 'stok')
            <x-table>
                <x-slot:head>
                    <tr>
                        <th class="px-4 py-3">Judul Buku</th>
                        <th class="px-4 py-3">Penulis / ISBN</th>
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3 text-center">Stok Total</th>
                        <th class="px-4 py-3 text-center">Tersedia</th>
                        <th class="px-4 py-3 text-center">Dipinjam</th>
                    </tr>
                </x-slot:head>
                @foreach ($data as $row)
                    <tr>
                        <td class="px-4 py-3 text-xs font-semibold text-charcoal">{{ $row->judul }}</td>
                        <td class="px-4 py-3 text-xs text-sage">{{ $row->penulis }} · {{ $row->isbn ?? '-' }}</td>
                        <td class="px-4 py-3 text-xs">{{ $row->kategori->pluck('nama_kategori')->join(', ') }}</td>
                        <td class="px-4 py-3 text-center text-xs">{{ $row->stok_total }}</td>
                        <td class="px-4 py-3 text-center text-xs font-semibold">{{ $row->stok_tersedia }}</td>
                        <td class="px-4 py-3 text-center text-xs">{{ $row->sedang_dipinjam_count }}</td>
                    </tr>
                @endforeach
            </x-table>
        @elseif ($jenis === 'member')
            <x-table>
                <x-slot:head>
                    <tr>
                        <th class="px-4 py-3">Nama Lengkap</th>
                        <th class="px-4 py-3">Username & Email</th>
                        <th class="px-4 py-3">Status Akun</th>
                        <th class="px-4 py-3">Tanggal Daftar</th>
                    </tr>
                </x-slot:head>
                @foreach ($data as $row)
                    <tr>
                        <td class="px-4 py-3 text-xs font-semibold">{{ $row->nama_lengkap }}</td>
                        <td class="px-4 py-3 text-xs">{{ $row->username }} · {{ $row->email }}</td>
                        <td class="px-4 py-3"><x-badge-status :status="$row->status" /></td>
                        <td class="px-4 py-3 text-xs">{{ $row->created_at?->format('d/m/Y') }}</td>
                    </tr>
                @endforeach
            </x-table>
        @endif
    </div>
</x-layouts.dashboard>
