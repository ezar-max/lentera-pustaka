<?php

namespace App\Http\Controllers\Publik;

use App\Http\Controllers\Controller;
use App\Models\Buku;
use App\Models\KategoriBuku;
use App\Models\User;
use Illuminate\View\View;

class BerandaController extends Controller
{
    public function index(): View
    {
        $bukuTerbaru = Buku::with('kategori')
            ->withAvg('ulasan', 'rating')
            ->latest()
            ->limit(8)
            ->get();

        $bukuPopuler = Buku::with('kategori')
            ->withCount('peminjaman')
            ->withAvg('ulasan', 'rating')
            ->orderByDesc('peminjaman_count')
            ->limit(4)
            ->get();

        $statistik = [
            'total_judul' => Buku::count(),
            'total_eksemplar' => (int) Buku::sum('stok_total'),
            'total_kategori' => KategoriBuku::count(),
            'total_member' => User::where('role', 'peminjam')->where('status', 'aktif')->count(),
        ];

        return view('publik.beranda', compact('bukuTerbaru', 'bukuPopuler', 'statistik'));
    }
}
