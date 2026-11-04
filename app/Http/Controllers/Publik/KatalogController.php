<?php

namespace App\Http\Controllers\Publik;

use App\Http\Controllers\Controller;
use App\Models\Buku;
use App\Models\KategoriBuku;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KatalogController extends Controller
{
    public function index(Request $request): View
    {
        $query = Buku::with('kategori')
            ->withCount('peminjaman')
            ->withAvg('ulasan', 'rating');

        if ($request->filled('q')) {
            $cari = trim((string) $request->input('q'));
            $query->where(function ($sub) use ($cari): void {
                $sub->where('judul', 'like', "%{$cari}%")
                    ->orWhere('penulis', 'like', "%{$cari}%")
                    ->orWhere('penerbit', 'like', "%{$cari}%")
                    ->orWhere('isbn', 'like', "%{$cari}%");
            });
        }

        if ($request->filled('kategori')) {
            $kategoriId = (int) $request->input('kategori');
            $query->whereHas('kategori', fn ($q) => $q->where('kategori_buku.id', $kategoriId));
        }

        $urut = $request->input('urut', 'terbaru');
        match ($urut) {
            'terpopuler' => $query->orderByDesc('peminjaman_count')->orderBy('judul'),
            'judul_asc' => $query->orderBy('judul'),
            'tersedia' => $query->orderByDesc('stok_tersedia')->orderBy('judul'),
            default => $query->latest(),
        };

        $bukuList = $query->paginate(12)->withQueryString();
        $kategoriList = KategoriBuku::withCount('buku')->orderBy('nama_kategori')->get();

        return view('publik.katalog', compact('bukuList', 'kategoriList'));
    }

    public function show(Buku $buku): View
    {
        $buku->load(['kategori', 'ulasan.user']);
        $rataRating = round((float) $buku->ulasan()->avg('rating'), 1);

        $bukuTerkait = Buku::with('kategori')
            ->where('id', '!=', $buku->id)
            ->whereHas('kategori', function ($q) use ($buku): void {
                $q->whereIn('kategori_buku.id', $buku->kategori->pluck('id'));
            })
            ->limit(4)
            ->get();

        return view('publik.detail-buku', compact('buku', 'rataRating', 'bukuTerkait'));
    }
}
