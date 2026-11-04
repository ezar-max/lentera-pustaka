<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Denda;
use App\Models\User;
use App\Services\DendaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DendaController extends Controller
{
    public function __construct(public DendaService $dendaService) {}

    public function index(Request $request): View
    {
        $query = Denda::with(['user', 'peminjaman.buku'])->latest('id');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('jenis')) {
            $query->where('jenis', $request->input('jenis'));
        }

        if ($request->filled('q')) {
            $cari = trim((string) $request->input('q'));
            $query->whereHas('user', fn ($u) => $u->where('nama_lengkap', 'like', "%{$cari}%")->orWhere('username', 'like', "%{$cari}%"));
        }

        $dendaList = $query->paginate(12)->withQueryString();

        $rekapMember = User::where('role', 'peminjam')
            ->where('total_poin_denda', '>', 0)
            ->orderByDesc('total_poin_denda')
            ->limit(8)
            ->get();

        return view('petugas.denda', compact('dendaList', 'rekapMember'));
    }

    public function tandaiLunas(Request $request, Denda $denda): RedirectResponse
    {
        $catatan = $request->input('catatan');
        $this->dendaService->tandaiLunas($denda, is_string($catatan) ? trim($catatan) : null);

        return back()->with('success', 'Catatan denda berhasil ditandai lunas dan total poin denda aktif member telah diperbarui.');
    }

    public function lunasiUser(Request $request, User $user): RedirectResponse
    {
        $catatan = $request->input('catatan');
        $this->dendaService->lunasiSemuaDendaUser($user, is_string($catatan) ? trim($catatan) : null);

        return back()->with('success', 'Seluruh denda aktif milik '.$user->nama_lengkap.' berhasil ditandai lunas.');
    }
}
