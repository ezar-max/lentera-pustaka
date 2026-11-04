<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PenggunaRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PenggunaController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::latest('id');

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('q')) {
            $cari = trim((string) $request->input('q'));
            $query->where(function ($sub) use ($cari): void {
                $sub->where('nama_lengkap', 'like', "%{$cari}%")
                    ->orWhere('username', 'like', "%{$cari}%")
                    ->orWhere('email', 'like', "%{$cari}%");
            });
        }

        $penggunaList = $query->paginate(12)->withQueryString();

        return view('admin.pengguna.index', compact('penggunaList'));
    }

    public function create(): View
    {
        return view('admin.pengguna.form', [
            'pengguna' => new User(['role' => 'petugas', 'status' => 'aktif']),
            'isEdit' => false,
        ]);
    }

    public function store(PenggunaRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $pengguna = User::create([
            'nama_lengkap' => $validated['nama_lengkap'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'alamat' => $validated['alamat'],
            'no_hp' => $validated['no_hp'],
            'role' => $validated['role'],
            'status' => $validated['status'],
            'alasan_penolakan' => $validated['alasan_penolakan'] ?? null,
            'total_poin_denda' => 0,
        ]);

        return redirect()
            ->route('admin.pengguna.index')
            ->with('success', 'Akun pengguna "'.$pengguna->nama_lengkap.'" berhasil ditambahkan.');
    }

    public function edit(User $pengguna): View
    {
        return view('admin.pengguna.form', [
            'pengguna' => $pengguna,
            'isEdit' => true,
        ]);
    }

    public function update(PenggunaRequest $request, User $pengguna): RedirectResponse
    {
        $validated = $request->validated();

        $dataUpdate = [
            'nama_lengkap' => $validated['nama_lengkap'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'alamat' => $validated['alamat'],
            'no_hp' => $validated['no_hp'],
            'role' => $validated['role'],
            'status' => $validated['status'],
            'alasan_penolakan' => $validated['status'] === 'ditolak' ? ($validated['alasan_penolakan'] ?? 'Akun dinonaktifkan oleh Administrator.') : null,
        ];

        if (! empty($validated['password'])) {
            $dataUpdate['password'] = Hash::make($validated['password']);
        }

        $pengguna->update($dataUpdate);

        return redirect()
            ->route('admin.pengguna.index')
            ->with('success', 'Data akun "'.$pengguna->nama_lengkap.'" berhasil diperbarui.');
    }

    public function nonaktifkan(Request $request, User $pengguna): RedirectResponse
    {
        if ($pengguna->id === $request->user()->id) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $pengguna->update([
            'status' => 'ditolak',
            'alasan_penolakan' => $request->input('alasan_penolakan', 'Akun dinonaktifkan oleh Administrator.'),
        ]);

        return back()->with('success', 'Akun "'.$pengguna->nama_lengkap.'" telah dinonaktifkan.');
    }

    public function aktifkan(User $pengguna): RedirectResponse
    {
        $pengguna->update([
            'status' => 'aktif',
            'alasan_penolakan' => null,
        ]);

        return back()->with('success', 'Akun "'.$pengguna->nama_lengkap.'" telah diaktifkan kembali.');
    }

    public function resetPassword(Request $request, User $pengguna): RedirectResponse
    {
        $passwordBaru = $request->input('password_baru', 'password123');

        $pengguna->update([
            'password' => Hash::make($passwordBaru),
        ]);

        return back()->with('success', 'Kata sandi untuk "'.$pengguna->nama_lengkap.'" telah direset menjadi "'.$passwordBaru.'".');
    }

    public function destroy(Request $request, User $pengguna): RedirectResponse
    {
        if ($pengguna->id === $request->user()->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $nama = $pengguna->nama_lengkap;
        $pengguna->delete();

        return back()->with('success', 'Akun pengguna "'.$nama.'" berhasil dihapus.');
    }
}
