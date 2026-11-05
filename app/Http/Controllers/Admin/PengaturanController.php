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
        ];

        return view('admin.pengaturan', compact('aturan'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kuota_pinjam_aktif' => ['required', 'integer', 'min:1', 'max:20'],
            'durasi_pinjam_hari' => ['required', 'integer', 'min:1', 'max:60'],
            'batas_ambil_hari' => ['required', 'integer', 'min:1', 'max:14'],
        ]);

        $envPath = base_path('.env');
        if (file_exists($envPath) && is_writable($envPath)) {
            $envContent = (string) file_get_contents($envPath);

            $pairs = [
                'PERPUSTAKAAN_KUOTA_PINJAM' => $validated['kuota_pinjam_aktif'],
                'PERPUSTAKAAN_DURASI_PINJAM' => $validated['durasi_pinjam_hari'],
                'PERPUSTAKAAN_BATAS_AMBIL' => $validated['batas_ambil_hari'],
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

        return back()->with('success', 'Parameter aturan sirkulasi Lentera Pustaka berhasil diperbarui.');
    }
}
