<x-layouts.dashboard title="Dashboard Administrator" header="Gambaran Seluruh Sistem Lentera Pustaka" subheader="Pantau seluruh aktivitas petugas sirkulasi, persetujuan registrasi member baru, dan pertumbuhan pengguna.">
    {{-- Kartu Ringkasan Petugas + Khusus Admin --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stat-card
            label="Member Menunggu Approve"
            :value="$ringkasan['member_pending']"
            subtitle="Pendaftaran baru berstatus pending"
            :accent="$ringkasan['member_pending'] > 0"
        />
        <x-stat-card
            label="Pengajuan Pinjam Menunggu"
            :value="$ringkasan['pinjam_menunggu']"
            subtitle="Menunggu keputusan petugas/admin"
        />
        <x-stat-card
            label="Pengajuan Kembali & Terlambat"
            :value="$ringkasan['kembali_menunggu'] . ' / ' . $ringkasan['buku_terlambat']"
            subtitle="Kembali menunggu / Buku terlambat"
        />
        <x-stat-card
            label="Total Pengguna & Stok Menipis"
            :value="$ringkasan['total_pengguna'] . ' Akun'"
            subtitle="Member baru bulan ini: {{ $ringkasan['member_baru_bulan_ini'] }} · Stok tipis: {{ $ringkasan['stok_menipis'] }}"
        />
    </div>

    {{-- Grafik Lebih Lengkap (Peminjaman Bulanan & Pertumbuhan Member Baru) --}}
    <div class="mt-8 grid grid-cols-1 lg:grid-cols-12 gap-6 pb-8 border-b border-sage/20">
        <div class="lg:col-span-6 bg-ivory-paper border border-sage/25 rounded-card p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-forest">Tren Peminjaman per Bulan</h2>
                <a href="{{ route('admin.statistik.index') }}" class="text-xs font-semibold text-forest hover:underline">Lihat 6 Grafik &rarr;</a>
            </div>
            <div class="h-64">
                <canvas id="adminChartPinjam"></canvas>
            </div>
        </div>

        <div class="lg:col-span-6 bg-ivory-paper border border-sage/25 rounded-card p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-forest">Pertumbuhan Member Baru per Bulan</h2>
                <a href="{{ route('admin.pengguna.index') }}" class="text-xs font-semibold text-forest hover:underline">Kelola Akun &rarr;</a>
            </div>
            <div class="h-64">
                <canvas id="adminChartMember"></canvas>
            </div>
        </div>
    </div>

    {{-- Antrean Persetujuan Member & Antrean Peminjaman --}}
    <div class="mt-8 grid grid-cols-1 lg:grid-cols-12 gap-8">
        <div class="lg:col-span-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-semibold text-forest">Antrean Persetujuan Registrasi Member</h2>
                <a href="{{ route('admin.member-pending.index') }}" class="text-xs font-semibold text-forest hover:underline">Lihat Semua ({{ $ringkasan['member_pending'] }}) &rarr;</a>
            </div>

            @if ($memberPendingTerbaru->isEmpty())
                <x-empty-state judul="Tidak Ada Pendaftaran Pending" deskripsi="Seluruh calon anggota telah diverifikasi." />
            @else
                <x-table>
                    <x-slot:head>
                        <tr>
                            <th class="px-4 py-3">Calon Anggota</th>
                            <th class="px-4 py-3">Kontak</th>
                            <th class="px-4 py-3 text-right">Keputusan</th>
                        </tr>
                    </x-slot:head>
                    @foreach ($memberPendingTerbaru as $calon)
                        <tr>
                            <td class="px-4 py-3 text-xs">
                                <strong class="text-charcoal block">{{ $calon->nama_lengkap }}</strong>
                                <span class="text-sage">{{ '@' . $calon->username }}</span>
                            </td>
                            <td class="px-4 py-3 text-xs text-sage">
                                {{ $calon->email }}<br>{{ $calon->no_hp }}
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <form action="{{ route('admin.member-pending.setujui', $calon) }}" method="POST"
                                          data-confirm="Setujui pendaftaran akun {{ $calon->nama_lengkap }} agar dapat masuk ke sistem?"
                                          data-confirm-title="Setujui Member Baru"
                                          data-confirm-button="Ya, Setujui">
                                        @csrf
                                        <x-button type="submit" variant="primary" size="sm">Setujui</x-button>
                                    </form>
                                    <form action="{{ route('admin.member-pending.tolak', $calon) }}" method="POST"
                                          data-reason="Tuliskan alasan penolakan registrasi untuk {{ $calon->nama_lengkap }}."
                                          data-reason-title="Tolak Registrasi Member">
                                        @csrf
                                        <x-button type="submit" variant="danger" size="sm">Tolak</x-button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-table>
            @endif
        </div>

        <div class="lg:col-span-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-semibold text-forest">Antrean Pengajuan Peminjaman Buku</h2>
                <a href="{{ route('admin.peminjaman.index') }}" class="text-xs font-semibold text-forest hover:underline">Verifikasi Pinjaman &rarr;</a>
            </div>

            @if ($pengajuanTerbaru->isEmpty())
                <x-empty-state judul="Tidak Ada Pengajuan Menunggu" deskripsi="Seluruh pengajuan peminjaman telah ditinjau." />
            @else
                <x-table>
                    <x-slot:head>
                        <tr>
                            <th class="px-4 py-3">Peminjam</th>
                            <th class="px-4 py-3">Buku</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </x-slot:head>
                    @foreach ($pengajuanTerbaru as $pinjam)
                        <tr>
                            <td class="px-4 py-3 text-xs font-semibold text-charcoal">{{ $pinjam->user->nama_lengkap }}</td>
                            <td class="px-4 py-3 text-xs text-charcoal">{{ $pinjam->buku->judul }}</td>
                            <td class="px-4 py-3 text-right">
                                <x-button href="{{ route('admin.peminjaman.index') }}" variant="secondary" size="sm">Tinjau</x-button>
                            </td>
                        </tr>
                    @endforeach
                </x-table>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                if (!window.Chart) return;
                const stats = @json($statistik);

                new window.Chart(document.getElementById('adminChartPinjam'), {
                    type: 'line',
                    data: {
                        labels: stats.peminjaman_per_bulan.labels,
                        datasets: [{
                            label: 'Peminjaman',
                            data: stats.peminjaman_per_bulan.data,
                            borderColor: '#183B32',
                            backgroundColor: 'rgba(24, 59, 50, 0.14)',
                            fill: true,
                            tension: 0.35,
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
                });

                new window.Chart(document.getElementById('adminChartMember'), {
                    type: 'line',
                    data: {
                        labels: stats.member_baru_per_bulan.labels,
                        datasets: [{
                            label: 'Member Baru',
                            data: stats.member_baru_per_bulan.data,
                            borderColor: '#D9A441',
                            backgroundColor: 'rgba(217, 164, 65, 0.18)',
                            fill: true,
                            tension: 0.35,
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
                });
            });
        </script>
    @endpush
</x-layouts.dashboard>

