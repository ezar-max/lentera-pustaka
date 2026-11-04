<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Http\Requests\KategoriRequest;
use App\Models\KategoriBuku;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class KategoriController extends Controller
{
    public function index(): View
    {
        $kategoriList = KategoriBuku::withCount('buku')->orderBy('nama_kategori')->get();

        return view('petugas.kategori', compact('kategoriList'));
    }

    public function store(KategoriRequest $request): RedirectResponse
    {
        $kategori = KategoriBuku::create($request->validated());

        return back()->with('success', 'Kategori "'.$kategori->nama_kategori.'" berhasil ditambahkan.');
    }

    public function update(KategoriRequest $request, KategoriBuku $kategori): RedirectResponse
    {
        $kategori->update($request->validated());

        return back()->with('success', 'Nama kategori berhasil diperbarui menjadi "'.$kategori->nama_kategori.'".');
    }

    public function destroy(KategoriBuku $kategori): RedirectResponse
    {
        $jumlahBuku = $kategori->buku()->count();
        if ($jumlahBuku > 0) {
            return back()->with('error', "Kategori \"{$kategori->nama_kategori}\" tidak dapat dihapus karena masih digunakan oleh {$jumlahBuku} buku.");
        }

        $nama = $kategori->nama_kategori;
        $kategori->delete();

        return back()->with('success', 'Kategori "'.$nama.'" berhasil dihapus.');
    }
}
