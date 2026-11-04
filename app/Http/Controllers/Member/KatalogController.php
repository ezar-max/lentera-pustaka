<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Http\Requests\UlasanRequest;
use App\Models\Buku;
use App\Models\KategoriBuku;
use App\Models\KoleksiPribadi;
use App\Models\UlasanBuku;
use App\Services\PeminjamanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KatalogController extends Controller
{
    public function __construct(public PeminjamanService $peminjamanService) {}

    public function index(Request $request): View
    {
        $user = $request->user();

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

        $koleksiIds = KoleksiPribadi::where('user_id', $user->id)->pluck('buku_id')->all();

        $kelayakanMap = [];
        foreach ($bukuList as $buku) {
            $kelayakanMap[$buku->id] = $this->peminjamanService->cekKelayakanPinjam($user, $buku);
        }

        return view('member.katalog', compact('bukuList', 'kategoriList', 'koleksiIds', 'kelayakanMap'));
    }

    public function show(Request $request, Buku $buku): View
    {
        $user = $request->user();
        $buku->load(['kategori', 'ulasan.user']);

        $rataRating = round((float) $buku->ulasan()->avg('rating'), 1);
        $kelayakan = $this->peminjamanService->cekKelayakanPinjam($user, $buku);
        $diKoleksi = KoleksiPribadi::where('user_id', $user->id)->where('buku_id', $buku->id)->exists();
        $ulasanSaya = UlasanBuku::where('user_id', $user->id)->where('buku_id', $buku->id)->first();

        return view('member.detail-buku', compact('buku', 'rataRating', 'kelayakan', 'diKoleksi', 'ulasanSaya'));
    }

    public function simpanUlasan(UlasanRequest $request, Buku $buku): RedirectResponse
    {
        $validated = $request->validated();

        UlasanBuku::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'buku_id' => $buku->id,
            ],
            [
                'rating' => (int) $validated['rating'],
                'ulasan' => trim($validated['ulasan']),
            ]
        );

        return back()->with('success', 'Ulasan dan rating Anda untuk buku ini berhasil disimpan.');
    }
}
