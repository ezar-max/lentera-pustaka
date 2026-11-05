<x-layouts.dashboard title="Statistik Grafik" header="Visualisasi Statistik Sirkulasi & Pustaka" subheader="Analisis tren peminjaman bulanan, buku terpopuler, distribusi kategori, komposisi status, dan pengembalian buku.">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- 1. Peminjaman per Bulan (Garis) --}}
        <div class="bg-ivory-paper border border-sage/25 rounded-card p-6">
            <h2 class="text-sm font-semibold text-forest mb-4">1. Peminjaman per Bulan (6 Bulan Terakhir)</h2>
            <div class="h-72">
                <canvas id="statPinjamBulan"></canvas>
            </div>
        </div>

        {{-- 2. 10 Buku Terpopuler (Batang Horizontal) --}}
        <div class="bg-ivory-paper border border-sage/25 rounded-card p-6">
            <h2 class="text-sm font-semibold text-forest mb-4">2. 10 Buku Terpopuler</h2>
            <div class="h-72">
                <canvas id="statBukuPopuler"></canvas>
            </div>
        </div>

        {{-- 3. Kategori Terbanyak Dipinjam (Donat) --}}
        <div class="bg-ivory-paper border border-sage/25 rounded-card p-6">
            <h2 class="text-sm font-semibold text-forest mb-4">3. Kategori Terbanyak Dipinjam</h2>
            <div class="h-72 flex items-center justify-center">
                <canvas id="statKategori"></canvas>
            </div>
        </div>

        {{-- 4. Komposisi Status Peminjaman (Donat) --}}
        <div class="bg-ivory-paper border border-sage/25 rounded-card p-6">
            <h2 class="text-sm font-semibold text-forest mb-4">4. Komposisi Status Peminjaman</h2>
            <div class="h-72 flex items-center justify-center">
                <canvas id="statStatus"></canvas>
            </div>
        </div>

        {{-- 5. Pengembalian Buku per Bulan (Batang) --}}
        <div class="bg-ivory-paper border border-sage/25 rounded-card p-6 {{ auth()->user()->isAdmin() ? '' : 'lg:col-span-2' }}">
            <h2 class="text-sm font-semibold text-forest mb-4">5. Buku Dikembalikan per Bulan</h2>
            <div class="h-72">
                <canvas id="statDendaBulan"></canvas>
            </div>
        </div>

        {{-- 6. Member Baru per Bulan (Khusus Admin) --}}
        @if (auth()->user()->isAdmin())
            <div class="bg-ivory-paper border border-sage/25 rounded-card p-6">
                <h2 class="text-sm font-semibold text-forest mb-4">6. Pertumbuhan Member Baru per Bulan (Admin)</h2>
                <div class="h-72">
                    <canvas id="statMemberBulan"></canvas>
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                if (!window.Chart) return;

                const stats = @json($statistik);
                const palette = ['#183B32', '#D9A441', '#6B7C70', '#2A4B63', '#8B651B', '#4D3869', '#87332A', '#94A398'];

                // 1. Garis: Peminjaman per Bulan
                new window.Chart(document.getElementById('statPinjamBulan'), {
                    type: 'line',
                    data: {
                        labels: stats.peminjaman_per_bulan.labels,
                        datasets: [{
                            label: 'Peminjaman',
                            data: stats.peminjaman_per_bulan.data,
                            borderColor: '#183B32',
                            backgroundColor: 'rgba(24, 59, 50, 0.14)',
                            fill: true,
                            tension: 0.3,
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
                });

                // 2. Batang Horizontal: 10 Buku Terpopuler
                new window.Chart(document.getElementById('statBukuPopuler'), {
                    type: 'bar',
                    data: {
                        labels: stats.buku_terpopuler.labels,
                        datasets: [{
                            label: 'Kali Dipinjam',
                            data: stats.buku_terpopuler.data,
                            backgroundColor: '#183B32',
                            borderRadius: 6,
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
                    }
                });

                // 3. Donat: Kategori Terbanyak
                new window.Chart(document.getElementById('statKategori'), {
                    type: 'doughnut',
                    data: {
                        labels: stats.kategori_terbanyak.labels,
                        datasets: [{
                            data: stats.kategori_terbanyak.data,
                            backgroundColor: palette,
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right' } } }
                });

                // 4. Donat: Komposisi Status
                new window.Chart(document.getElementById('statStatus'), {
                    type: 'doughnut',
                    data: {
                        labels: stats.komposisi_status.labels,
                        datasets: [{
                            data: stats.komposisi_status.data,
                            backgroundColor: palette,
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right' } } }
                });

                // 5. Batang: Pengembalian per Bulan
                new window.Chart(document.getElementById('statDendaBulan'), {
                    type: 'bar',
                    data: {
                        labels: stats.pengembalian_per_bulan.labels,
                        datasets: [{
                            label: 'Buku Dikembalikan',
                            data: stats.pengembalian_per_bulan.data,
                            backgroundColor: '#D9A441',
                            borderRadius: 8,
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
                });

                // 6. Garis: Member Baru per Bulan (Admin)
                const elMember = document.getElementById('statMemberBulan');
                if (elMember) {
                    new window.Chart(elMember, {
                        type: 'line',
                        data: {
                            labels: stats.member_baru_per_bulan.labels,
                            datasets: [{
                                label: 'Member Baru',
                                data: stats.member_baru_per_bulan.data,
                                borderColor: '#6B7C70',
                                backgroundColor: 'rgba(107, 124, 112, 0.15)',
                                fill: true,
                                tension: 0.3,
                            }]
                        },
                        options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
                    });
                }
            });
        </script>
    @endpush
</x-layouts.dashboard>

