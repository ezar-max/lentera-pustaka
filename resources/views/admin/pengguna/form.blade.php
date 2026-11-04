<x-layouts.dashboard
    :title="$isEdit ? 'Ubah Akun Pengguna' : 'Buat Akun Pengguna'"
    :header="$isEdit ? 'Sunting Akun Pengguna' : 'Buat Akun Petugas / Pengguna Baru'"
    subheader="Atur informasi akun, hak akses (role), dan status keaktifan."
>
    <div class="max-w-2xl">
        <div class="mb-6">
            <a href="{{ route('admin.pengguna.index') }}" class="text-xs font-semibold uppercase tracking-wider text-sage hover:text-forest">
                &larr; Kembali ke Daftar Pengguna
            </a>
        </div>

        <form action="{{ $isEdit ? route('admin.pengguna.update', $pengguna) : route('admin.pengguna.store') }}"
              method="POST"
              class="bg-ivory-paper border border-sage/25 rounded-card p-6 sm:p-8 space-y-5">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <x-input label="Nama Lengkap" name="nama_lengkap" :value="$pengguna->nama_lengkap" required />
                <x-input label="Username" name="username" :value="$pengguna->username" required />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <x-input label="Alamat Email" name="email" type="email" :value="$pengguna->email" required />
                <x-input label="Nomor Telepon / HP" name="no_hp" :value="$pengguna->no_hp" required />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="role" class="block text-xs font-semibold uppercase tracking-wider text-charcoal mb-1.5">
                        Role Pengguna <span class="text-status-ditolak-text">*</span>
                    </label>
                    <select id="role" name="role" required class="w-full rounded-card border border-sage/35 bg-ivory-paper px-3.5 py-2.5 text-sm text-charcoal">
                        <option value="petugas" @selected(old('role', $pengguna->role) === 'petugas')>Petugas Perpustakaan</option>
                        <option value="peminjam" @selected(old('role', $pengguna->role) === 'peminjam')>Peminjam (Member)</option>
                        <option value="admin" @selected(old('role', $pengguna->role) === 'admin')>Administrator</option>
                    </select>
                </div>

                <div>
                    <label for="status" class="block text-xs font-semibold uppercase tracking-wider text-charcoal mb-1.5">
                        Status Akun <span class="text-status-ditolak-text">*</span>
                    </label>
                    <select id="status" name="status" required class="w-full rounded-card border border-sage/35 bg-ivory-paper px-3.5 py-2.5 text-sm text-charcoal">
                        <option value="aktif" @selected(old('status', $pengguna->status) === 'aktif')>Aktif</option>
                        <option value="pending" @selected(old('status', $pengguna->status) === 'pending')>Pending</option>
                        <option value="ditolak" @selected(old('status', $pengguna->status) === 'ditolak')>Ditolak / Nonaktif</option>
                    </select>
                </div>
            </div>

            <div>
                <label for="alamat" class="block text-xs font-semibold uppercase tracking-wider text-charcoal mb-1.5">
                    Alamat Lengkap <span class="text-status-ditolak-text">*</span>
                </label>
                <textarea id="alamat" name="alamat" rows="2" required class="w-full rounded-card border border-sage/35 bg-ivory-paper px-4 py-2.5 text-sm text-charcoal">{{ old('alamat', $pengguna->alamat) }}</textarea>
            </div>

            <x-input
                :label="$isEdit ? 'Kata Sandi Baru (Kosongkan jika tidak diubah)' : 'Kata Sandi Awal'"
                name="password"
                type="password"
                :required="!$isEdit"
            />

            <x-input
                label="Alasan Penolakan / Penonaktifan (Opsional)"
                name="alasan_penolakan"
                :value="$pengguna->alasan_penolakan"
            />

            <div class="pt-4 border-t border-sage/20 flex items-center justify-end gap-3">
                <x-button href="{{ route('admin.pengguna.index') }}" variant="secondary">Batal</x-button>
                <x-button type="submit" variant="primary">
                    {{ $isEdit ? 'Simpan Perubahan Akun' : 'Buat Akun Sekarang' }}
                </x-button>
            </div>
        </form>
    </div>
</x-layouts.dashboard>

