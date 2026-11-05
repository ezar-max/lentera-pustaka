<x-layouts.dashboard title="Kelola Pengguna" header="Manajemen Akun Pengguna & Petugas" subheader="Buat akun petugas baru, ubah role atau status pengguna, nonaktifkan akun, dan reset kata sandi.">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-6 border-b border-sage/20">
        <form action="{{ route('admin.pengguna.index') }}" method="GET" class="flex flex-wrap items-center gap-2.5 flex-1">
            <input
                type="text"
                name="q"
                value="{{ request('q') }}"
                placeholder="Cari nama, username, email..."
                class="w-full sm:w-64 rounded-card border border-sage/35 bg-ivory-paper px-3.5 py-2 text-xs text-charcoal focus:border-forest focus:outline-none"
            >
            <select name="role" class="rounded-card border border-sage/35 bg-ivory-paper px-3.5 py-2 text-xs text-charcoal">
                <option value="">Semua Role</option>
                <option value="admin" @selected(request('role') === 'admin')>Administrator</option>
                <option value="petugas" @selected(request('role') === 'petugas')>Petugas</option>
                <option value="peminjam" @selected(request('role') === 'peminjam')>Peminjam (Member)</option>
            </select>
            <select name="status" class="rounded-card border border-sage/35 bg-ivory-paper px-3.5 py-2 text-xs text-charcoal">
                <option value="">Semua Status</option>
                <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
                <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                <option value="ditolak" @selected(request('status') === 'ditolak')>Ditolak / Nonaktif</option>
            </select>

            <x-button type="submit" variant="secondary" size="sm">Filter</x-button>
            @if (request()->hasAny(['q', 'role', 'status']))
                <x-button href="{{ route('admin.pengguna.index') }}" variant="secondary" size="sm">Reset</x-button>
            @endif
        </form>

        <x-button href="{{ route('admin.pengguna.create') }}" variant="primary">
            + Buat Akun Petugas / Pengguna
        </x-button>
    </div>

    <div class="mt-6">
        <x-table>
            <x-slot:head>
                <tr>
                    <th class="px-4 py-3.5">Nama & Username</th>
                    <th class="px-4 py-3.5">Kontak</th>
                    <th class="px-4 py-3.5">Role</th>
                    <th class="px-4 py-3.5">Status</th>
                    <th class="px-4 py-3.5 text-right">Aksi Manajemen</th>
                </tr>
            </x-slot:head>

            @foreach ($penggunaList as $u)
                <tr>
                    <td class="px-4 py-4">
                        <p class="font-semibold text-charcoal">{{ $u->nama_lengkap }}</p>
                        <p class="text-xs text-sage">{{ '@' . $u->username }}</p>
                    </td>
                    <td class="px-4 py-4 text-xs">
                        <p class="text-charcoal">{{ $u->email }}</p>
                        <p class="text-sage">{{ $u->no_hp }}</p>
                    </td>
                    <td class="px-4 py-4">
                        <span class="px-2.5 py-1 rounded-card text-xs font-semibold uppercase tracking-wider {{ $u->role === 'admin' ? 'bg-gold/25 text-charcoal' : ($u->role === 'petugas' ? 'bg-forest/15 text-forest' : 'bg-ivory-dark text-sage') }}">
                            {{ $u->role }}
                        </span>
                    </td>
                    <td class="px-4 py-4">
                        <x-badge-status :status="$u->status" />
                    </td>
                    <td class="px-4 py-4 text-right whitespace-nowrap">
                        <div class="inline-flex items-center gap-1.5">
                            <x-button href="{{ route('admin.pengguna.edit', $u) }}" variant="secondary" size="sm">
                                Ubah
                            </x-button>

                            <form action="{{ route('admin.pengguna.reset-password', $u) }}" method="POST"
                                  data-confirm="Reset kata sandi akun '{{ $u->nama_lengkap }}' menjadi 'password123'?"
                                  data-confirm-title="Reset Kata Sandi"
                                  data-confirm-button="Ya, Reset Sandi">
                                @csrf
                                <x-button type="submit" variant="secondary" size="sm">
                                    Reset Sandi
                                </x-button>
                            </form>

                            @if ($u->id !== auth()->id())
                                @if ($u->status === 'aktif')
                                    <form action="{{ route('admin.pengguna.nonaktifkan', $u) }}" method="POST"
                                          data-reason="Masukkan alasan penonaktifan akun {{ $u->nama_lengkap }}."
                                          data-reason-title="Nonaktifkan Akun Pengguna"
                                          data-reason-placeholder="Contoh: Masa tugas berakhir atau pelanggaran aturan...">
                                        @csrf
                                        <x-button type="submit" variant="danger" size="sm">
                                            Nonaktifkan
                                        </x-button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.pengguna.aktifkan', $u) }}" method="POST"
                                          data-confirm="Aktifkan kembali akun '{{ $u->nama_lengkap }}'?"
                                          data-confirm-title="Aktifkan Akun"
                                          data-confirm-button="Ya, Aktifkan">
                                        @csrf
                                        <x-button type="submit" variant="primary" size="sm">
                                            Aktifkan
                                        </x-button>
                                    </form>
                                @endif
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-table>

        <div class="mt-6">
            <x-pagination :paginator="$penggunaList" />
        </div>
    </div>
</x-layouts.dashboard>

