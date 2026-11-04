<x-layouts.app title="Masuk ke Sistem">
    <section class="max-w-md mx-auto px-4 sm:px-6 py-14 sm:py-20">
        <div class="text-center mb-8">
            <span class="inline-block px-3 py-1 rounded-card text-xs font-semibold uppercase tracking-wider bg-forest/10 text-forest mb-3">
                Akses Keanggotaan & Staf
            </span>
            <h1 class="font-sans text-2xl sm:text-3xl font-semibold text-forest">Masuk ke Lentera Pustaka</h1>
            <p class="mt-2 text-sm text-sage">
                Gunakan username atau alamat email terdaftar untuk mengakses layanan sirkulasi.
            </p>
        </div>

        <form action="{{ route('login.submit') }}" method="POST" class="bg-ivory-paper border border-sage/25 rounded-card p-6 sm:p-8 space-y-5">
            @csrf

            <x-input
                label="Username atau Email"
                name="identitas"
                placeholder="Masukkan username atau email"
                required
                autofocus
            />

            <x-input
                label="Kata Sandi"
                name="password"
                type="password"
                placeholder="Masukkan kata sandi"
                required
            />

            <div class="flex items-center justify-between text-xs">
                <label class="inline-flex items-center gap-2 text-charcoal cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded border-sage/40 text-forest focus:ring-forest">
                    <span>Ingat sesi masuk saya</span>
                </label>
            </div>

            <div class="pt-1">
                <x-button type="submit" variant="primary" class="w-full py-3">
                    Masuk Sekarang
                </x-button>
            </div>

            <div class="pt-4 border-t border-sage/20 text-center text-xs text-sage">
                Belum memiliki akun anggota?
                <a href="{{ route('register') }}" class="font-semibold text-forest hover:underline ml-1">
                    Daftar Keanggotaan Baru
                </a>
            </div>
        </form>

        {{-- Info Akun Admin --}}
        <div class="mt-8 pt-6 border-t border-sage/20 text-xs text-sage space-y-1 text-center">
            <p class="font-semibold text-charcoal uppercase tracking-wider">Akun Administrator</p>
            <p>Email: <code class="text-forest font-mono">admin@gmail.com</code> · Sandi: <code class="text-forest font-mono">admin123</code></p>
        </div>
    </section>
</x-layouts.app>

