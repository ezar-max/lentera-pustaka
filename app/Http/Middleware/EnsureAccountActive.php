<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountActive
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->status !== 'aktif') {
            $status = $user->status;
            $alasan = $user->alasan_penolakan;

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $pesan = $status === 'pending'
                ? 'Akun Anda sedang menunggu persetujuan administrator.'
                : 'Pendaftaran akun Anda ditolak. Alasan: '.($alasan ?: 'Tidak memenuhi syarat keanggotaan.');

            return redirect()->route('login')->with('error', $pesan);
        }

        return $next($request);
    }
}
