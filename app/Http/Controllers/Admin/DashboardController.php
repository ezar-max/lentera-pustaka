<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\User;
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
            'member_pending' => User::where('role', 'peminjam')->where('status', 'pending')->count(),
            'member_baru_bulan_ini' => User::where('role', 'peminjam')
                ->whereYear('created_at', Carbon::today()->year)
                ->whereMonth('created_at', Carbon::today()->month)
                ->count(),
            'total_pengguna' => User::count(),
        ];

        $memberPendingTerbaru = User::where('role', 'peminjam')
            ->where('status', 'pending')
            ->latest('id')
            ->limit(5)
            ->get();

        $pengajuanTerbaru = Peminjaman::with(['user', 'buku'])
            ->where('status', 'diajukan')
            ->latest('id')
            ->limit(5)
            ->get();

        $statistik = $this->statistikService->ambilSemuaStatistik();

        return view('admin.dashboard', compact('ringkasan', 'memberPendingTerbaru', 'pengajuanTerbaru', 'statistik'));
    }
}
