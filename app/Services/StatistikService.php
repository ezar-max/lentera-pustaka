<?php

namespace App\Services;

use App\Models\Buku;
use App\Models\KategoriBuku;
use App\Models\Peminjaman;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StatistikService
{
    /**
     * Data lengkap untuk halaman Statistik & Dashboard Petugas/Admin.
     *
     * @return array<string, mixed>
     */
    public function ambilSemuaStatistik(): array
    {
        return [
            'peminjaman_per_bulan' => $this->peminjamanPerBulan(),
            'buku_terpopuler' => $this->sepuluhBukuTerpopuler(),
            'kategori_terbanyak' => $this->kategoriTerbanyakDipinjam(),
            'komposisi_status' => $this->komposisiStatusPeminjaman(),
            'pengembalian_per_bulan' => $this->pengembalianPerBulan(),
            'member_baru_per_bulan' => $this->memberBaruPerBulan(),
        ];
    }

    /**
     * 1. Grafik Peminjaman per Bulan (6 bulan terakhir)
     *
     * @return array{labels: list<string>, data: list<int>}
     */
    public function peminjamanPerBulan(): array
    {
        $labels = [];
        $data = [];
        $namaBulan = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        for ($i = 5; $i >= 0; $i--) {
            $bulan = Carbon::today()->startOfMonth()->subMonths($i);
            $labels[] = $namaBulan[$bulan->month].' '.$bulan->year;

            $jumlah = Peminjaman::whereYear('tanggal_pengajuan', $bulan->year)
                ->whereMonth('tanggal_pengajuan', $bulan->month)
                ->count();

            $data[] = $jumlah;
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * 2. Grafik 10 Buku Terpopuler (Batang Horizontal)
     *
     * @return array{labels: list<string>, data: list<int>}
     */
    public function sepuluhBukuTerpopuler(): array
    {
        $bukuPopuler = Buku::withCount('peminjaman')
            ->orderByDesc('peminjaman_count')
            ->limit(10)
            ->get();

        return [
            'labels' => $bukuPopuler->pluck('judul')->map(fn ($j) => mb_strimwidth($j, 0, 32, '...'))->all(),
            'data' => $bukuPopuler->pluck('peminjaman_count')->map(fn ($c) => (int) $c)->all(),
        ];
    }

    /**
     * 3. Grafik Kategori Terbanyak Dipinjam (Donat/Pie)
     *
     * @return array{labels: list<string>, data: list<int>}
     */
    public function kategoriTerbanyakDipinjam(): array
    {
        $rows = KategoriBuku::select('kategori_buku.nama_kategori', DB::raw('COUNT(peminjaman.id) as total_pinjam'))
            ->leftJoin('kategori_buku_relasi', 'kategori_buku.id', '=', 'kategori_buku_relasi.kategori_id')
            ->leftJoin('peminjaman', 'kategori_buku_relasi.buku_id', '=', 'peminjaman.buku_id')
            ->groupBy('kategori_buku.id', 'kategori_buku.nama_kategori')
            ->orderByDesc('total_pinjam')
            ->get();

        return [
            'labels' => $rows->pluck('nama_kategori')->all(),
            'data' => $rows->pluck('total_pinjam')->map(fn ($c) => (int) $c)->all(),
        ];
    }

    /**
     * 4. Grafik Komposisi Status Peminjaman (Donat)
     *
     * @return array{labels: list<string>, data: list<int>}
     */
    public function komposisiStatusPeminjaman(): array
    {
        $statusMap = [
            'diajukan' => 'Diajukan',
            'disetujui' => 'Disetujui',
            'dipinjam' => 'Dipinjam',
            'pengajuan_kembali' => 'Pengajuan Kembali',
            'kembali_disetujui' => 'Kembali Disetujui',
            'selesai' => 'Selesai',
            'ditolak' => 'Ditolak',
            'dibatalkan' => 'Dibatalkan',
        ];

        $counts = Peminjaman::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $labels = [];
        $data = [];
        foreach ($statusMap as $key => $label) {
            $labels[] = $label;
            $data[] = (int) ($counts[$key] ?? 0);
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * 5. Grafik Buku Dikembalikan per Bulan (Batang)
     *
     * @return array{labels: list<string>, data: list<int>}
     */
    public function pengembalianPerBulan(): array
    {
        $labels = [];
        $data = [];
        $namaBulan = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        for ($i = 5; $i >= 0; $i--) {
            $bulan = Carbon::today()->startOfMonth()->subMonths($i);
            $labels[] = $namaBulan[$bulan->month].' '.$bulan->year;

            $jumlah = Peminjaman::where('status', 'selesai')
                ->whereYear('tanggal_kembali', $bulan->year)
                ->whereMonth('tanggal_kembali', $bulan->month)
                ->count();

            $data[] = (int) $jumlah;
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * 6. Grafik Member Baru per Bulan (Khusus Admin)
     *
     * @return array{labels: list<string>, data: list<int>}
     */
    public function memberBaruPerBulan(): array
    {
        $labels = [];
        $data = [];
        $namaBulan = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        for ($i = 5; $i >= 0; $i--) {
            $bulan = Carbon::today()->startOfMonth()->subMonths($i);
            $labels[] = $namaBulan[$bulan->month].' '.$bulan->year;

            $jumlah = User::where('role', 'peminjam')
                ->whereYear('created_at', $bulan->year)
                ->whereMonth('created_at', $bulan->month)
                ->count();

            $data[] = $jumlah;
        }

        return ['labels' => $labels, 'data' => $data];
    }
}
