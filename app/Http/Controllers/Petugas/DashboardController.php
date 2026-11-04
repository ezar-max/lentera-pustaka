<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Services\StatistikService;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(public StatistikService $statistikService) {}

    public function index(): View
    {
        $batasStok = (int) config('perpustakaan.batas_stok_menipis', 2);

        $ringkasan = [
            'pinjam_menunggu' => Peminjaman::where('status', 'diajukan')->count(),
            'kembali_menunggu' => Peminjaman::whereIn('status', ['pengajuan_kembali', 'kembali_disetujui'])->count(),
            'buku_terlambat' => Peminjaman::whereIn('status', ['dipinjam', 'pengajuan_kembali', 'kembali_disetujui'])
                ->whereDate('tanggal_jatuh_tempo', '<', Carbon::today())
                ->count(),
            'stok_menipis' => Buku::where('stok_tersedia', '<=', $batasStok)->count(),
        ];

        $pengajuanTerbaru = Peminjaman::with(['user', 'buku'])
            ->where('status', 'diajukan')
            ->latest('id')
            ->limit(6)
            ->get();

        $bukuStokMenipis = Buku::with('kategori')
            ->where('stok_tersedia', '<=', $batasStok)
            ->orderBy('stok_tersedia')
            ->limit(6)
            ->get();

        $grafikRingkas = [
            'peminjaman_per_bulan' => $this->statistikService->peminjamanPerBulan(),
            'komposisi_status' => $this->statistikService->komposisiStatusPeminjaman(),
        ];

        return view('petugas.dashboard', compact('ringkasan', 'pengajuanTerbaru', 'bukuStokMenipis', 'grafikRingkas'));
    }
}
