<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Http\Requests\KonfirmasiPengembalianRequest;
use App\Models\Peminjaman;
use App\Services\PeminjamanService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VerifikasiPengembalianController extends Controller
{
    public function __construct(public PeminjamanService $peminjamanService) {}

    public function index(Request $request): View
    {
        $this->peminjamanService->hitungKeterlambatanHarian();

        $tab = $request->input('tab', 'pengajuan');

        $statusQuery = match ($tab) {
            'menunggu_buku' => ['kembali_disetujui'],
            'selesai' => ['selesai'],
            default => ['pengajuan_kembali'],
        };

        $peminjamanList = Peminjaman::with(['user', 'buku', 'petugas', 'denda'])
            ->whereIn('status', $statusQuery)
            ->latest('updated_at')
            ->paginate(12)
            ->withQueryString();

        $counts = [
            'pengajuan' => Peminjaman::where('status', 'pengajuan_kembali')->count(),
            'menunggu_buku' => Peminjaman::where('status', 'kembali_disetujui')->count(),
            'selesai' => Peminjaman::where('status', 'selesai')->count(),
        ];

        return view('petugas.pengembalian', compact('peminjamanList', 'tab', 'counts'));
    }

    public function setujui(Request $request, Peminjaman $peminjaman): RedirectResponse
    {
        try {
            $this->peminjamanService->setujuiPengembalian($peminjaman, $request->user());

            return back()->with('success', 'Pengajuan pengembalian disetujui. Silakan konfirmasi saat member menyerahkan fisik buku.');
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function konfirmasi(KonfirmasiPengembalianRequest $request, Peminjaman $peminjaman): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $hasil = $this->peminjamanService->konfirmasiPengembalian(
                $peminjaman,
                $request->user(),
                $validated['kondisi_buku'],
                $validated['catatan_petugas'] ?? null
            );

            $infoTambahan = $hasil->hari_terlambat > 0
                ? " Terlambat {$hasil->hari_terlambat} hari dan poin denda otomatis dicatat."
                : ' Tepat waktu.';

            return back()->with('success', 'Pengembalian buku berhasil dikonfirmasi dan stok telah diperbarui.'.$infoTambahan);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
