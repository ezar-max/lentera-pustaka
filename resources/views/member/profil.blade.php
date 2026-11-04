<x-layouts.app title="Profil Saya">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="pb-6 border-b border-sage/20">
            <p class="text-xs font-semibold uppercase tracking-wider text-sage">Pengaturan Akun</p>
            <h1 class="mt-1 font-sans text-2xl sm:text-3xl font-semibold text-forest">Profil Keanggotaan Saya</h1>
        </div>

        <div class="mt-8 grid grid-cols-1 lg:grid-cols-12 gap-8">
            {{-- Form Data Diri --}}
            <div class="lg:col-span-7">
                <h2 class="text-base font-semibold text-forest mb-4">Informasi Data Diri</h2>
                <form action="{{ route('member.profil.update') }}" method="POST" class="bg-ivory-paper border border-sage/25 rounded-card p-6 space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-sage mb-1">Username (Tetap)</label>
                        <input type="text" value="{{ $user->username }}" disabled class="w-full rounded-card border border-sage/20 bg-ivory-dark px-4 py-2.5 text-sm text-sage cursor-not-allowed">
                    </div>

                    <x-input label="Nama Lengkap" name="nama_lengkap" :value="$user->nama_lengkap" required />
                    <x-input label="Alamat Email" name="email" type="email" :value="$user->email" required />
                    <x-input label="Nomor Telepon / HP" name="no_hp" :value="$user->no_hp" required />

                    <div>
                        <label for="alamat" class="block text-xs font-semibold uppercase tracking-wider text-charcoal mb-1.5">
                            Alamat Lengkap <span class="text-status-ditolak-text">*</span>
                        </label>
                        <textarea id="alamat" name="alamat" rows="3" required class="w-full rounded-card border border-sage/35 bg-ivory-paper px-4 py-2.5 text-sm text-charcoal focus:border-forest focus:outline-none">{{ old('alamat', $user->alamat) }}</textarea>
                    </div>

                    <div class="pt-2">
                        <x-button type="submit" variant="primary">Simpan Perubahan Profil</x-button>
                    </div>
                </form>
            </div>

            {{-- Form Ganti Password --}}
            <div class="lg:col-span-5">
                <h2 class="text-base font-semibold text-forest mb-4">Ubah Kata Sandi</h2>
                <form action="{{ route('member.profil.password') }}" method="POST" class="bg-ivory-paper border border-sage/25 rounded-card p-6 space-y-4">
                    @csrf
                    @method('PUT')

                    <x-input label="Kata Sandi Saat Ini" name="password_lama" type="password" required />
                    <x-input label="Kata Sandi Baru" name="password_baru" type="password" required />
                    <x-input label="Konfirmasi Kata Sandi Baru" name="password_baru_confirmation" type="password" required />

                    <div class="pt-2">
                        <x-button type="submit" variant="secondary" class="w-full">Perbarui Kata Sandi</x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>

