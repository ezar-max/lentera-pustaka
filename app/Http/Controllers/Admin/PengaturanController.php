<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class PengaturanController extends Controller
{
    public function index(): View
    {
        $aturan = [
            'kuota_pinjam_aktif' => config('perpustakaan.kuota_pinjam_aktif', 3),
            'durasi_pinjam_hari' => config('perpustakaan.durasi_pinjam_hari', 7),
            'batas_ambil_hari' => config('perpustakaan.batas_ambil_hari', 2),
            'denda_terlambat' => config('perpustakaan.denda.terlambat_per_hari', 2),
            'denda_rusak' => config('perpustakaan.denda.rusak', 25),
            'denda_hilang' => config('perpustakaan.denda.hilang', 100),
            'batas_poin_blokir' => config('perpustakaan.batas_poin_blokir', 50),
        ];

        return view('admin.pengaturan', compact('aturan'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kuota_pinjam_aktif' => ['required', 'integer', 'min:1', 'max:20'],
            'durasi_pinjam_hari' => ['required', 'integer', 'min:1', 'max:60'],
            'batas_ambil_hari' => ['required', 'integer', 'min:1', 'max:14'],
            'denda_terlambat' => ['required', 'integer', 'min:1', 'max:100'],
            'denda_rusak' => ['required', 'integer', 'min:1', 'max:500'],
            'denda_hilang' => ['required', 'integer', 'min:1', 'max:1000'],
            'batas_poin_blokir' => ['required', 'integer', 'min:10', 'max:1000'],
        ]);

        $envPath = base_path('.env');
        if (file_exists($envPath) && is_writable($envPath)) {
            $envContent = (string) file_get_contents($envPath);

            $pairs = [
                'PERPUSTAKAAN_KUOTA_PINJAM' => $validated['kuota_pinjam_aktif'],
                'PERPUSTAKAAN_DURASI_PINJAM' => $validated['durasi_pinjam_hari'],
                'PERPUSTAKAAN_BATAS_AMBIL' => $validated['batas_ambil_hari'],
                'PERPUSTAKAAN_DENDA_TERLAMBAT' => $validated['denda_terlambat'],
                'PERPUSTAKAAN_DENDA_RUSAK' => $validated['denda_rusak'],
                'PERPUSTAKAAN_DENDA_HILANG' => $validated['denda_hilang'],
                'PERPUSTAKAAN_BATAS_POIN_BLOKIR' => $validated['batas_poin_blokir'],
            ];

            foreach ($pairs as $key => $val) {
                if (preg_match("/^{$key}=.*/m", $envContent)) {
                    $envContent = (string) preg_replace("/^{$key}=.*/m", "{$key}={$val}", $envContent);
                } else {
                    $envContent .= "\n{$key}={$val}";
                }
            }

            file_put_contents($envPath, $envContent);
            Artisan::call('config:clear');
        }

        return back()->with('success', 'Parameter aturan peminjaman dan poin denda Lentera Pustaka berhasil diperbarui.');
    }
}
