<x-layouts.app title="Daftar Keanggotaan">
    <section class="max-w-2xl mx-auto px-4 sm:px-6 py-12 sm:py-16">
        <div class="mb-8">
            <span class="inline-block px-3 py-1 rounded-card text-xs font-semibold uppercase tracking-wider bg-gold/20 text-charcoal mb-3">
                Registrasi Anggota Baru
            </span>
            <h1 class="font-sans text-2xl sm:text-3xl font-semibold text-forest">Formulir Keanggotaan Lentera Pustaka</h1>
            <p class="mt-2 text-sm text-sage leading-relaxed">
                Lengkapi data diri Anda di bawah ini. Sesuai prosedur perpustakaan, akun baru akan berstatus
                <strong class="text-charcoal">menunggu persetujuan (pending)</strong> dan dapat digunakan masuk setelah diverifikasi oleh Administrator.
            </p>
        </div>

        <form action="{{ route('register.submit') }}" method="POST" class="bg-ivory-paper border border-sage/25 rounded-card p-6 sm:p-8 space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <x-input
                    label="Nama Lengkap"
                    name="nama_lengkap"
                    placeholder="Nama lengkap sesuai identitas"
                    required
                />

                <x-input
                    label="Username"
                    name="username"
                    placeholder="Contoh: nadiamaharani"
                    hint="Tanpa spasi, digunakan untuk masuk."
                    required
                />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <x-input
                    label="Alamat Email"
                    name="email"
                    type="email"
                    placeholder="nama@email.com"
                    required
                />

                <x-input
                    label="Nomor Telepon / WhatsApp"
                    name="no_hp"
                    placeholder="0812xxxxxxxx"
                    required
                />
            </div>

            <div>
                <label for="alamat" class="block text-xs font-semibold uppercase tracking-wider text-charcoal mb-1.5">
                    Alamat Domisili Lengkap <span class="text-status-ditolak-text">*</span>
                </label>
                <textarea
                    id="alamat"
                    name="alamat"
                    rows="3"
                    required
                    placeholder="Nama jalan, nomor rumah, kelurahan, kecamatan, dan kota..."
                    class="w-full rounded-card border border-sage/35 bg-ivory-paper px-4 py-2.5 text-sm text-charcoal placeholder:text-sage/70 focus:border-forest focus:outline-none"
                >{{ old('alamat') }}</textarea>
                @error('alamat')
                    <p class="mt-1 text-xs font-medium text-status-ditolak-text">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-2 border-t border-sage/15">
                <x-input
                    label="Kata Sandi"
                    name="password"
                    type="password"
                    placeholder="Minimal 8 karakter"
                    required
                />

                <x-input
                    label="Konfirmasi Kata Sandi"
                    name="password_confirmation"
                    type="password"
                    placeholder="Ulangi kata sandi"
                    required
                />
            </div>

            <div class="pt-3 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-xs text-sage">
                    Sudah terdaftar sebagai anggota?
                    <a href="{{ route('login') }}" class="font-semibold text-forest hover:underline">Masuk di sini</a>
                </p>

                <x-button type="submit" variant="primary" size="lg">
                    Kirim Pendaftaran Anggota
                </x-button>
            </div>
        </form>
    </section>
</x-layouts.app>

