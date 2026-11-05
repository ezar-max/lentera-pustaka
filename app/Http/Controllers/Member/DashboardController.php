<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Peminjaman;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $pinjamanAktifCount = Peminjaman::where('user_id', $user->id)
            ->whereIn('status', ['dipinjam', 'pengajuan_kembali', 'kembali_disetujui'])
            ->count();

        $menungguPersetujuanCount = Peminjaman::where('user_id', $user->id)
            ->whereIn('status', ['diajukan', 'disetujui'])
            ->count();

        $riwayatSelesaiCount = Peminjaman::where('user_id', $user->id)
            ->where('status', 'selesai')
            ->count();

        $daftarPinjamanAktif = Peminjaman::with('buku')
            ->where('user_id', $user->id)
            ->aktif()
            ->latest('tanggal_pengajuan')
            ->get();

        $notifikasiTerbaru = Peminjaman::with('buku')
            ->where('user_id', $user->id)
            ->whereIn('status', ['disetujui', 'ditolak', 'kembali_disetujui', 'dibatalkan'])
            ->latest('updated_at')
            ->limit(5)
            ->get();

        $aturan = [
            'kuota_pinjam_aktif' => config('perpustakaan.kuota_pinjam_aktif', 3),
            'durasi_pinjam_hari' => config('perpustakaan.durasi_pinjam_hari', 7),
            'batas_ambil_hari' => config('perpustakaan.batas_ambil_hari', 2),
        ];

        return view('member.dashboard', compact(
            'user',
            'pinjamanAktifCount',
            'menungguPersetujuanCount',
            'riwayatSelesaiCount',
            'daftarPinjamanAktif',
            'notifikasiTerbaru',
            'aturan'
        ));
    }
}
