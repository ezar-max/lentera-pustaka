<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Services\PeminjamanService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PeminjamanController extends Controller
{
    public function __construct(public PeminjamanService $peminjamanService) {}

    public function index(Request $request): View
    {
        $query = Peminjaman::with(['buku.kategori', 'petugas', 'denda'])
            ->where('user_id', $request->user()->id)
            ->latest('id');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $peminjamanList = $query->paginate(10)->withQueryString();

        return view('member.peminjaman', compact('peminjamanList'));
    }

    public function show(Peminjaman $peminjaman): View
    {
        Gate::authorize('view', $peminjaman);

        $peminjaman->load(['buku.kategori', 'petugas', 'denda']);

        return view('member.detail-peminjaman', compact('peminjaman'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'buku_id' => ['required', 'exists:buku,id'],
        ]);

        $buku = Buku::findOrFail($request->input('buku_id'));

        try {
            $peminjaman = $this->peminjamanService->ajukanPeminjaman($request->user(), $buku);

            return redirect()
                ->route('member.peminjaman.show', $peminjaman)
                ->with('success', 'Pengajuan peminjaman buku "'.$buku->judul.'" berhasil dikirim dan menunggu verifikasi petugas.');
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function batalkan(Peminjaman $peminjaman): RedirectResponse
    {
        Gate::authorize('update', $peminjaman);

        try {
            $this->peminjamanService->batalkanOlehMember($peminjaman);

            return back()->with('success', 'Pengajuan peminjaman berhasil dibatalkan.');
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function ajukanKembali(Peminjaman $peminjaman): RedirectResponse
    {
        Gate::authorize('update', $peminjaman);

        try {
            $this->peminjamanService->ajukanPengembalian($peminjaman);

            return back()->with('success', 'Pengajuan pengembalian buku berhasil dikirim. Tunggu persetujuan petugas sebelum menyerahkan buku.');
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
