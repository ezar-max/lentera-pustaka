<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectSesuaiRole(Auth::user());
        }

        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $identitas = trim((string) $request->input('identitas'));
        $password = (string) $request->input('password');
        $remember = $request->boolean('remember');

        $kolom = filter_var($identitas, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $user = User::where($kolom, $identitas)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            return back()
                ->withInput($request->only('identitas'))
                ->with('error', 'Kombinasi username/email dan kata sandi tidak sesuai.');
        }

        if ($user->status === 'pending') {
            return back()
                ->withInput($request->only('identitas'))
                ->with('warning', 'Akun Anda masih berstatus menunggu persetujuan (pending) dari Administrator. Silakan coba kembali setelah disetujui.');
        }

        if ($user->status === 'ditolak') {
            $alasan = $user->alasan_penolakan ?: 'Tidak memenuhi ketentuan verifikasi keanggotaan.';

            return back()
                ->withInput($request->only('identitas'))
                ->with('error', 'Pendaftaran akun Anda ditolak oleh Administrator. Alasan: '.$alasan);
        }

        Auth::login($user, $remember);
        $request->session()->regenerate();

        return $this->redirectSesuaiRole($user)
            ->with('success', 'Selamat datang kembali, '.$user->nama_lengkap.'.');
    }

    public function showRegister(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectSesuaiRole(Auth::user());
        }

        return view('auth.register');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        User::create([
            'nama_lengkap' => $validated['nama_lengkap'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'alamat' => $validated['alamat'],
            'no_hp' => $validated['no_hp'],
            'role' => 'peminjam',
            'status' => 'pending',
            'total_poin_denda' => 0,
        ]);

        return redirect()
            ->route('login')
            ->with('success', 'Pendaftaran keanggotaan berhasil dikirim dan sedang menunggu persetujuan Administrator.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('beranda')
            ->with('success', 'Anda telah keluar dari sesi Lentera Pustaka.');
    }

    protected function redirectSesuaiRole(User $user): RedirectResponse
    {
        return match ($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'petugas' => redirect()->route('petugas.dashboard'),
            default => redirect()->route('member.dashboard'),
        };
    }
}
