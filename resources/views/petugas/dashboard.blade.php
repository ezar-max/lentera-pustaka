<x-layouts.dashboard title="Dashboard Petugas" header="Ringkasan Kerja Sirkulasi Harian" subheader="Pantau antrean peminjaman, pengembalian, keterlambatan, dan ketersediaan stok buku.">
    @php
        $prefix = auth()->user()->isAdmin() && request()->is('admin*') ? 'admin' : 'petugas';
    @endphp

    {{-- 4 Kartu Ringkasan --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <x-stat-card
            label="Pengajuan Pinjam Menunggu"
            :value="$ringkasan['pinjam_menunggu']"
            subtitle="Perlu verifikasi setuju / tolak"
            :accent="$ringkasan['pinjam_menunggu'] > 0"
        />
        <x-stat-card
            label="Pengajuan Kembali Menunggu"
            :value="$ringkasan['kembali_menunggu']"
            subtitle="Menunggu persetujuan & fisik buku"
        />
        <x-stat-card
            label="Buku Sedang Terlambat"
            :value="$ringkasan['buku_terlambat']"
            subtitle="Melewati tanggal jatuh tempo"
        />
        <x-stat-card
            label="Koleksi Stok Menipis"
            :value="$ringkasan['stok_menipis']"
            subtitle="Stok tersedia <= {{ config('perpustakaan.batas_stok_menipis', 2) }} eksemplar"
        />
    </div>

    {{-- Grafik Ringkas --}}
    <div class="mt-8 grid grid-cols-1 lg:grid-cols-12 gap-6 pb-8 border-b border-sage/20">
        <div class="lg:col-span-7 bg-ivory-paper border border-sage/25 rounded-card p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-forest">Tren Peminjaman (6 Bulan Terakhir)</h2>
                <a href="{{ route($prefix . '.statistik.index') }}" class="text-xs font-semibold text-forest hover:underline">Statistik Lengkap &rarr;</a>
            </div>
            <div class="h-64">
                <canvas id="chartPinjamDashboard"></canvas>
            </div>
        </div>

        <div class="lg:col-span-5 bg-ivory-paper border border-sage/25 rounded-card p-6">
            <h2 class="text-sm font-semibold text-forest mb-4">Komposisi Status Transaksi</h2>
            <div class="h-64 flex items-center justify-center">
                <canvas id="chartStatusDashboard"></canvas>
            </div>
        </div>
    </div>

    {{-- Antrean Pengajuan & Peringatan Stok Menipis --}}
    <div class="mt-8 grid grid-cols-1 lg:grid-cols-12 gap-8">
        <div class="lg:col-span-7">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-semibold text-forest">Antrean Pengajuan Peminjaman Terbaru</h2>
                <a href="{{ route($prefix . '.peminjaman.index') }}" class="text-xs font-semibold text-forest hover:underline">Buka Halaman Verifikasi &rarr;</a>
            </div>

            @if ($pengajuanTerbaru->isEmpty())
                <x-empty-state judul="Tidak Ada Antrean Menunggu" deskripsi="Seluruh pengajuan peminjaman yang masuk telah diproses." />
            @else
                <x-table>
                    <x-slot:head>
                        <tr>
                            <th class="px-4 py-3">Peminjam</th>
                            <th class="px-4 py-3">Buku</th>
                            <th class="px-4 py-3">Stok</th>
                            <th class="px-4 py-3 text-right">Aksi Cepat</th>
                        </tr>
                    </x-slot:head>
                    @foreach ($pengajuanTerbaru as $pinjam)
                        <tr>
                            <td class="px-4 py-3 text-xs">
                                <strong class="text-charcoal block">{{ $pinjam->user->nama_lengkap }}</strong>
                                <span class="text-sage">Poin: {{ $pinjam->user->total_poin_denda }}</span>
                            </td>
                            <td class="px-4 py-3 text-xs font-medium text-charcoal">
                                {{ $pinjam->buku->judul }}
                            </td>
                            <td class="px-4 py-3 text-xs">
                                {{ $pinjam->buku->stok_tersedia }}/{{ $pinjam->buku->stok_total }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <x-button href="{{ route($prefix . '.peminjaman.index', ['tab' => 'menunggu']) }}" variant="secondary" size="sm">
                                    Proses
                                </x-button>
                            </td>
                        </tr>
                    @endforeach
                </x-table>
            @endif
        </div>

        <div class="lg:col-span-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-semibold text-forest">Peringatan Stok Menipis</h2>
                <a href="{{ route($prefix . '.buku.index', ['menipis' => 1]) }}" class="text-xs font-semibold text-forest hover:underline">Kelola Stok &rarr;</a>
            </div>

            @if ($bukuStokMenipis->isEmpty())
                <p class="text-sm text-sage py-6 border-y border-sage/20">Seluruh koleksi buku memiliki stok tersedia yang mencukupi.</p>
            @else
                <div class="divide-y divide-sage/20 border-y border-sage/20">
                    @foreach ($bukuStokMenipis as $buku)
                        <div class="py-3.5 flex items-center justify-between gap-3 text-xs">
                            <div>
                                <p class="font-semibold text-charcoal">{{ $buku->judul }}</p>
                                <p class="text-sage">{{ $buku->penulis }}</p>
                            </div>
                            <span class="px-2.5 py-1 rounded-card font-semibold {{ $buku->stok_tersedia === 0 ? 'bg-status-ditolak text-status-ditolak-text' : 'bg-status-diajukan text-status-diajukan-text' }}">
                                Tersisa {{ $buku->stok_tersedia }} / {{ $buku->stok_total }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                if (!window.Chart) return;

                const dataBulan = @json($grafikRingkas['peminjaman_per_bulan']);
                const dataStatus = @json($grafikRingkas['komposisi_status']);

                const ctxPinjam = document.getElementById('chartPinjamDashboard');
                if (ctxPinjam) {
                    new window.Chart(ctxPinjam, {
                        type: 'line',
                        data: {
                            labels: dataBulan.labels,
                            datasets: [{
                                label: 'Jumlah Peminjaman',
                                data: dataBulan.data,
                                borderColor: '#183B32',
                                backgroundColor: 'rgba(24, 59, 50, 0.12)',
                                borderWidth: 2.5,
                                fill: true,
                                tension: 0.35,
                                pointBackgroundColor: '#D9A441',
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
                        }
                    });
                }

                const ctxStatus = document.getElementById('chartStatusDashboard');
                if (ctxStatus) {
                    new window.Chart(ctxStatus, {
                        type: 'doughnut',
                        data: {
                            labels: dataStatus.labels,
                            datasets: [{
                                data: dataStatus.data,
                                backgroundColor: ['#D9A441', '#183B32', '#2A4B63', '#6B7C70', '#4D3869', '#295238', '#87332A', '#94A398']
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { position: 'right', labels: { boxWidth: 12, font: { size: 11 } } } }
                        }
                    });
                }
            });
        </script>
    @endpush
</x-layouts.dashboard>

