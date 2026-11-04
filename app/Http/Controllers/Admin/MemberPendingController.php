<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TolakMemberRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberPendingController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->input('tab', 'pending');

        $statusQuery = match ($tab) {
            'ditolak' => 'ditolak',
            'aktif' => 'aktif',
            default => 'pending',
        };

        $memberList = User::where('role', 'peminjam')
            ->where('status', $statusQuery)
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        $counts = [
            'pending' => User::where('role', 'peminjam')->where('status', 'pending')->count(),
            'ditolak' => User::where('role', 'peminjam')->where('status', 'ditolak')->count(),
            'aktif' => User::where('role', 'peminjam')->where('status', 'aktif')->count(),
        ];

        return view('admin.member-pending', compact('memberList', 'tab', 'counts'));
    }

    public function setujui(User $user): RedirectResponse
    {
        $user->update([
            'status' => 'aktif',
            'alasan_penolakan' => null,
        ]);

        return back()->with('success', 'Pendaftaran anggota "'.$user->nama_lengkap.'" telah disetujui dan kini dapat masuk ke sistem.');
    }

    public function tolak(TolakMemberRequest $request, User $user): RedirectResponse
    {
        $user->update([
            'status' => 'ditolak',
            'alasan_penolakan' => trim($request->validated('alasan_penolakan')),
        ]);

        return back()->with('success', 'Pendaftaran anggota "'.$user->nama_lengkap.'" telah ditolak beserta alasannya.');
    }
}
