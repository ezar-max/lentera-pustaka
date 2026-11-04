<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Denda;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DendaController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $dendaList = Denda::with('peminjaman.buku')
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(12);

        $totalAktif = (int) Denda::where('user_id', $user->id)->where('status', 'aktif')->sum('poin');
        $totalLunas = (int) Denda::where('user_id', $user->id)->where('status', 'lunas')->sum('poin');
        $batasBlokir = (int) config('perpustakaan.batas_poin_blokir', 50);

        return view('member.denda', compact('user', 'dendaList', 'totalAktif', 'totalLunas', 'batasBlokir'));
    }
}
