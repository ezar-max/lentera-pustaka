<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Percakapan;
use App\Models\Pesan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ChatInboxController extends Controller
{
    public function index(Request $request): View
    {
        $daftarPercakapan = Percakapan::with(['user', 'staf', 'pesanTerakhir'])
            ->withCount([
                'pesan as belum_dibaca_count' => fn ($q) => $q->where('pengirim', 'user')->where('dibaca', false),
            ])
            ->where('tipe', 'petugas')
            ->orderByDesc('belum_dibaca_count')
            ->orderByDesc('updated_at')
            ->get();

        $aktifId = $request->input('percakapan_id') ?? $daftarPercakapan->first()?->id;
        $percakapanAktif = null;

        if ($aktifId) {
            $percakapanAktif = Percakapan::with(['user', 'staf', 'pesan'])
                ->where('tipe', 'petugas')
                ->find($aktifId);

            if ($percakapanAktif) {
                Pesan::where('percakapan_id', $percakapanAktif->id)
                    ->where('pengirim', 'user')
                    ->where('dibaca', false)
                    ->update(['dibaca' => true]);
            }
        }

        return view('petugas.chat', compact('daftarPercakapan', 'percakapanAktif'));
    }

    public function balas(Request $request, Percakapan $percakapan): JsonResponse|RedirectResponse
    {
        Gate::authorize('update', $percakapan);

        $validated = $request->validate([
            'isi' => ['required', 'string', 'max:1000'],
        ], [
            'isi.required' => 'Pesan balasan tidak boleh kosong.',
        ]);

        $staf = $request->user();

        if ($percakapan->status === 'selesai') {
            $percakapan->update(['status' => 'aktif']);
        }

        if (! $percakapan->staf_id) {
            $percakapan->update(['staf_id' => $staf->id]);
        }

        $pesan = Pesan::create([
            'percakapan_id' => $percakapan->id,
            'pengirim' => 'staf',
            'pengirim_id' => $staf->id,
            'isi' => trim($validated['isi']),
            'dibaca' => false,
        ]);

        $percakapan->touch();

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'ok',
                'pesan' => [
                    'id' => $pesan->id,
                    'pengirim' => $pesan->pengirim,
                    'isi' => $pesan->isi,
                    'waktu' => $pesan->created_at?->format('H:i'),
                ],
            ]);
        }

        $prefix = $staf->isAdmin() && $request->is('admin/*') ? 'admin' : 'petugas';

        return redirect()->route($prefix.'.chat.index', ['percakapan_id' => $percakapan->id]);
    }

    public function ambilAlih(Request $request, Percakapan $percakapan): RedirectResponse
    {
        Gate::authorize('update', $percakapan);

        $percakapan->update([
            'staf_id' => $request->user()->id,
            'status' => 'aktif',
        ]);

        return back()->with('success', 'Percakapan dengan '.$percakapan->user->nama_lengkap.' kini ditangani oleh Anda.');
    }

    public function tutup(Request $request, Percakapan $percakapan): RedirectResponse
    {
        Gate::authorize('update', $percakapan);

        $percakapan->update([
            'status' => 'selesai',
        ]);

        return back()->with('success', 'Sesi percakapan telah ditandai selesai.');
    }

    public function pollingPesan(Percakapan $percakapan): JsonResponse
    {
        Gate::authorize('view', $percakapan);

        Pesan::where('percakapan_id', $percakapan->id)
            ->where('pengirim', 'user')
            ->where('dibaca', false)
            ->update(['dibaca' => true]);

        $pesan = $percakapan->pesan()
            ->orderBy('id')
            ->get()
            ->map(fn (Pesan $p) => [
                'id' => $p->id,
                'pengirim' => $p->pengirim,
                'isi' => $p->isi,
                'waktu' => $p->created_at?->format('H:i'),
            ]);

        return response()->json([
            'status_percakapan' => $percakapan->status,
            'pesan' => $pesan,
        ]);
    }
}
