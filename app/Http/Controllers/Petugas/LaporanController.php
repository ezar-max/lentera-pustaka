<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\KategoriBuku;
use App\Models\User;
use App\Services\LaporanService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class LaporanController extends Controller
{
    public function __construct(public LaporanService $laporanService) {}

    public function index(Request $request): View
    {
        $jenis = $request->input('jenis', 'peminjaman');
        if ($jenis === 'member' && ! $request->user()->isAdmin()) {
            $jenis = 'peminjaman';
        }

        $filters = $request->only([
            'tanggal_mulai',
            'tanggal_selesai',
            'status',
            'petugas_id',
            'kategori_id',
            'stok_menipis',
            'status_akun',
        ]);

        $data = $this->laporanService->ambilDataLaporan($jenis, $filters);
        $kategoriList = KategoriBuku::orderBy('nama_kategori')->get();
        $petugasList = User::whereIn('role', ['admin', 'petugas'])->orderBy('nama_lengkap')->get();

        return view('petugas.laporan', compact('jenis', 'filters', 'data', 'kategoriList', 'petugasList'));
    }

    public function cetakPdf(Request $request): Response
    {
        $jenis = $request->input('jenis', 'peminjaman');
        if ($jenis === 'member' && ! $request->user()->isAdmin()) {
            abort(403, 'Laporan data member hanya dapat dicetak oleh Administrator.');
        }

        $filters = $request->only([
            'tanggal_mulai',
            'tanggal_selesai',
            'status',
            'petugas_id',
            'kategori_id',
            'stok_menipis',
            'status_akun',
        ]);

        $data = $this->laporanService->ambilDataLaporan($jenis, $filters);

        $judulLaporan = match ($jenis) {
            'stok' => 'Laporan Stok & Koleksi Buku',
            'member' => 'Laporan Keanggotaan Perpustakaan',
            default => 'Laporan Sirkulasi Peminjaman & Pengembalian',
        };

        $pdf = Pdf::loadView('petugas.laporan-pdf', [
            'jenis' => $jenis,
            'judulLaporan' => $judulLaporan,
            'filters' => $filters,
            'data' => $data,
            'pencetak' => $request->user(),
        ])->setPaper('a4', $jenis === 'peminjaman' ? 'landscape' : 'portrait');

        $namaFile = 'laporan-'.$jenis.'-lentera-pustaka-'.date('Ymd-His').'.pdf';

        return $pdf->download($namaFile);
    }
}
