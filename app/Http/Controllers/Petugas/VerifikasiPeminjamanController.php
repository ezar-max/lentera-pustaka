<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Http\Requests\TolakPeminjamanRequest;
use App\Models\Peminjaman;
use App\Services\PeminjamanService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VerifikasiPeminjamanController extends Controller
{
    public function __construct(public PeminjamanService $peminjamanService) {}

    public function index(Request $request): View
    {
        // Jalankan pengecekan kadaluarsa otomatis agar data selalu akurat
        $this->peminjamanService->batalkanPengajuanKadaluarsa();
        $this->peminjamanService->hitungKeterlambatanHarian();

        $tab = $request->input('tab', 'menunggu');

        $statusQuery = match ($tab) {
            'disetujui' => ['disetujui'],
            'dipinjam' => ['dipinjam'],
            'semua' => ['diajukan', 'disetujui', 'dipinjam', 'ditolak', 'dibatalkan', 'selesai'],
            default => ['diajukan'],
        };

        $query = Peminjaman::with(['user', 'buku', 'petugas'])
            ->whereIn('status', $statusQuery)
            ->latest('id');

        if ($request->filled('q')) {
            $cari = trim((string) $request->input('q'));
            $query->where(function ($sub) use ($cari): void {
                $sub->whereHas('user', fn ($u) => $u->where('nama_lengkap', 'like', "%{$cari}%")->orWhere('username', 'like', "%{$cari}%"))
                    ->orWhereHas('buku', fn ($b) => $b->where('judul', 'like', "%{$cari}%"));
            });
        }

        $peminjamanList = $query->paginate(12)->withQueryString();

        $counts = [
            'menunggu' => Peminjaman::where('status', 'diajukan')->count(),
            'disetujui' => Peminjaman::where('status', 'disetujui')->count(),
            'dipinjam' => Peminjaman::where('status', 'dipinjam')->count(),
        ];

        return view('petugas.peminjaman', compact('peminjamanList', 'tab', 'counts'));
    }

    public function setuju(Request $request, Peminjaman $peminjaman): RedirectResponse
    {
        try {
            $this->peminjamanService->setujuiPeminjaman($peminjaman, $request->user());

            return back()->with('success', 'Pengajuan peminjaman disetujui. Stok tersedia buku telah dikurangi 1.');
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function tolak(TolakPeminjamanRequest $request, Peminjaman $peminjaman): RedirectResponse
    {
        try {
            $this->peminjamanService->tolakPeminjaman(
                $peminjaman,
                $request->user(),
                $request->validated('alasan_penolakan')
            );

            return back()->with('success', 'Pengajuan peminjaman telah ditolak beserta alasannya.');
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function serahkan(Request $request, Peminjaman $peminjaman): RedirectResponse
    {
        try {
            $pinjam = $this->peminjamanService->serahkanBuku($peminjaman, $request->user());

            return back()->with(
                'success',
                'Buku berhasil diserahkan kepada peminjam. Tanggal jatuh tempo: '.$pinjam->tanggal_jatuh_tempo?->format('d M Y').'.'
            );
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
