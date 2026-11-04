<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Buku;
use App\Models\KoleksiPribadi;
use App\Services\PeminjamanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KoleksiController extends Controller
{
    public function __construct(public PeminjamanService $peminjamanService) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        $koleksiList = KoleksiPribadi::with('buku.kategori')
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $kelayakanMap = [];
        foreach ($koleksiList as $item) {
            if ($item->buku) {
                $kelayakanMap[$item->buku->id] = $this->peminjamanService->cekKelayakanPinjam($user, $item->buku);
            }
        }

        return view('member.koleksi', compact('koleksiList', 'kelayakanMap'));
    }

    public function toggle(Request $request, Buku $buku): RedirectResponse
    {
        $user = $request->user();

        $existing = KoleksiPribadi::where('user_id', $user->id)
            ->where('buku_id', $buku->id)
            ->first();

        if ($existing) {
            $existing->delete();

            return back()->with('success', 'Buku "'.$buku->judul.'" dihapus dari Koleksi Pribadi.');
        }

        KoleksiPribadi::create([
            'user_id' => $user->id,
            'buku_id' => $buku->id,
        ]);

        return back()->with('success', 'Buku "'.$buku->judul.'" ditambahkan ke Koleksi Pribadi.');
    }

    public function destroy(Request $request, KoleksiPribadi $koleksi): RedirectResponse
    {
        if ($koleksi->user_id !== $request->user()->id) {
            abort(403);
        }

        $judul = $koleksi->buku?->judul ?? 'Buku';
        $koleksi->delete();

        return back()->with('success', 'Buku "'.$judul.'" berhasil dihapus dari Koleksi Pribadi.');
    }
}
