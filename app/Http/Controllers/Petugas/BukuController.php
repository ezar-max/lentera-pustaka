<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Http\Requests\BukuRequest;
use App\Models\Buku;
use App\Models\KategoriBuku;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BukuController extends Controller
{
    public function index(Request $request): View
    {
        $query = Buku::with('kategori')->latest('id');

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

        if ($request->boolean('menipis')) {
            $batas = (int) config('perpustakaan.batas_stok_menipis', 2);
            $query->where('stok_tersedia', '<=', $batas);
        }

        $bukuList = $query->paginate(10)->withQueryString();
        $kategoriList = KategoriBuku::orderBy('nama_kategori')->get();

        return view('petugas.buku.index', compact('bukuList', 'kategoriList'));
    }

    public function create(): View
    {
        $kategoriList = KategoriBuku::orderBy('nama_kategori')->get();

        return view('petugas.buku.form', [
            'buku' => new Buku,
            'kategoriList' => $kategoriList,
            'isEdit' => false,
        ]);
    }

    public function store(BukuRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $coverPath = null;
        if ($request->hasFile('cover')) {
            $coverPath = $request->file('cover')->store('covers', 'public');
        }

        $buku = Buku::create([
            'judul' => $validated['judul'],
            'penulis' => $validated['penulis'],
            'penerbit' => $validated['penerbit'],
            'tahun_terbit' => (int) $validated['tahun_terbit'],
            'isbn' => $validated['isbn'] ?? null,
            'sinopsis' => $validated['sinopsis'] ?? null,
            'cover' => $coverPath,
            'stok_total' => (int) $validated['stok_total'],
            'stok_tersedia' => (int) $validated['stok_total'],
        ]);

        $buku->kategori()->sync($validated['kategori_ids']);

        $prefix = $request->user()->isAdmin() && $request->is('admin/*') ? 'admin' : 'petugas';

        return redirect()
            ->route($prefix.'.buku.index')
            ->with('success', 'Data buku "'.$buku->judul.'" berhasil ditambahkan ke katalog.');
    }

    public function edit(Buku $buku): View
    {
        $buku->load('kategori');
        $kategoriList = KategoriBuku::orderBy('nama_kategori')->get();

        return view('petugas.buku.form', [
            'buku' => $buku,
            'kategoriList' => $kategoriList,
            'isEdit' => true,
        ]);
    }

    public function update(BukuRequest $request, Buku $buku): RedirectResponse
    {
        $validated = $request->validated();

        // Hitung berapa eksemplar yang saat ini sedang disetujui/dipinjam
        $sedangDipinjam = max(0, $buku->stok_total - $buku->stok_tersedia);
        $stokTotalBaru = (int) $validated['stok_total'];

        if ($stokTotalBaru < $sedangDipinjam) {
            return back()
                ->withInput()
                ->with('error', "Stok total tidak boleh kurang dari jumlah eksemplar yang sedang dipinjam ({$sedangDipinjam} buku).");
        }

        $stokTersediaBaru = $stokTotalBaru - $sedangDipinjam;

        $coverPath = $buku->cover;
        if ($request->hasFile('cover')) {
            if ($buku->cover && Storage::disk('public')->exists($buku->cover)) {
                Storage::disk('public')->delete($buku->cover);
            }
            $coverPath = $request->file('cover')->store('covers', 'public');
        }

        $buku->update([
            'judul' => $validated['judul'],
            'penulis' => $validated['penulis'],
            'penerbit' => $validated['penerbit'],
            'tahun_terbit' => (int) $validated['tahun_terbit'],
            'isbn' => $validated['isbn'] ?? null,
            'sinopsis' => $validated['sinopsis'] ?? null,
            'cover' => $coverPath,
            'stok_total' => $stokTotalBaru,
            'stok_tersedia' => $stokTersediaBaru,
        ]);

        $buku->kategori()->sync($validated['kategori_ids']);

        $prefix = $request->user()->isAdmin() && $request->is('admin/*') ? 'admin' : 'petugas';

        return redirect()
            ->route($prefix.'.buku.index')
            ->with('success', 'Data buku "'.$buku->judul.'" berhasil diperbarui.');
    }

    public function destroy(Request $request, Buku $buku): RedirectResponse
    {
        $masihDipinjam = $buku->peminjaman()->aktif()->exists();
        if ($masihDipinjam) {
            return back()->with('error', 'Buku ini tidak dapat dihapus karena masih memiliki transaksi peminjaman aktif.');
        }

        if ($buku->cover && Storage::disk('public')->exists($buku->cover)) {
            Storage::disk('public')->delete($buku->cover);
        }

        $judul = $buku->judul;
        $buku->delete();

        return back()->with('success', 'Data buku "'.$judul.'" berhasil dihapus.');
    }
}
