<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Percakapan;
use App\Models\Pesan;
use App\Services\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function __construct(public ChatbotService $chatbotService) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $tab = $request->input('tab', 'ai');

        $percakapanAi = Percakapan::firstOrCreate([
            'user_id' => $user->id,
            'tipe' => 'ai',
            'status' => 'aktif',
        ]);

        $percakapanAi->load('pesan');

        $percakapanPetugas = Percakapan::with(['staf', 'pesan'])
            ->where('user_id', $user->id)
            ->where('tipe', 'petugas')
            ->latest('id')
            ->first();

        if ($percakapanPetugas) {
            Pesan::where('percakapan_id', $percakapanPetugas->id)
                ->where('pengirim', 'staf')
                ->where('dibaca', false)
                ->update(['dibaca' => true]);
        }

        $belumDibacaPetugas = $percakapanPetugas
            ? Pesan::where('percakapan_id', $percakapanPetugas->id)->where('pengirim', 'staf')->where('dibaca', false)->count()
            : 0;

        return view('member.chat', compact('tab', 'percakapanAi', 'percakapanPetugas', 'belumDibacaPetugas'));
    }

    public function kirimAi(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'isi' => ['required', 'string', 'max:1000'],
        ], [
            'isi.required' => 'Pesan pertanyaan tidak boleh kosong.',
        ]);

        $balasan = $this->chatbotService->kirimPesanKeAi($request->user(), $validated['isi']);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'ok',
                'balasan' => [
                    'id' => $balasan->id,
                    'pengirim' => $balasan->pengirim,
                    'isi' => $balasan->isi,
                    'waktu' => $balasan->created_at?->format('H:i'),
                ],
            ]);
        }

        return redirect()->route('member.chat', ['tab' => 'ai']);
    }

    public function hubungiPetugas(Request $request): RedirectResponse
    {
        $user = $request->user();

        $percakapan = Percakapan::firstOrCreate(
            [
                'user_id' => $user->id,
                'tipe' => 'petugas',
                'status' => 'aktif',
            ]
        );

        if ($percakapan->pesan()->count() === 0) {
            Pesan::create([
                'percakapan_id' => $percakapan->id,
                'pengirim' => 'user',
                'pengirim_id' => $user->id,
                'isi' => 'Halo Petugas Lentera Pustaka, saya membutuhkan bantuan terkait layanan perpustakaan.',
                'dibaca' => false,
            ]);
        }

        return redirect()
            ->route('member.chat', ['tab' => 'petugas'])
            ->with('success', 'Sesi percakapan dengan petugas perpustakaan telah dibuka.');
    }

    public function kirimPetugas(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'isi' => ['required', 'string', 'max:1000'],
        ], [
            'isi.required' => 'Pesan tidak boleh kosong.',
        ]);

        $user = $request->user();

        $percakapan = Percakapan::firstOrCreate([
            'user_id' => $user->id,
            'tipe' => 'petugas',
            'status' => 'aktif',
        ]);

        Gate::authorize('update', $percakapan);

        $pesan = Pesan::create([
            'percakapan_id' => $percakapan->id,
            'pengirim' => 'user',
            'pengirim_id' => $user->id,
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

        return redirect()->route('member.chat', ['tab' => 'petugas']);
    }

    public function pollingPesanPetugas(Request $request): JsonResponse
    {
        $user = $request->user();
        $percakapan = Percakapan::with('staf')
            ->where('user_id', $user->id)
            ->where('tipe', 'petugas')
            ->latest('id')
            ->first();

        if (! $percakapan) {
            return response()->json(['pesan' => [], 'status_percakapan' => null]);
        }

        Gate::authorize('view', $percakapan);

        Pesan::where('percakapan_id', $percakapan->id)
            ->where('pengirim', 'staf')
            ->where('dibaca', false)
            ->update(['dibaca' => true]);

        $daftarPesan = $percakapan->pesan()
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
            'nama_staf' => $percakapan->staf?->nama_lengkap ?? 'Petugas Perpustakaan',
            'pesan' => $daftarPesan,
        ]);
    }
}
