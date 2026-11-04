<x-layouts.dashboard title="Data Poin Denda" header="Pengelolaan Poin Denda Anggota" subheader="Pantau catatan denda akibat keterlambatan, kerusakan, atau kehilangan buku, serta tandai lunas setelah diselesaikan.">
    @php
        $prefix = auth()->user()->isAdmin() && request()->is('admin*') ? 'admin' : 'petugas';
    @endphp

    {{-- Rekap Member dengan Poin Denda Aktif --}}
    @if ($rekapMember->isNotEmpty())
        <div class="mb-8 pb-6 border-b border-sage/20">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-forest mb-3">Anggota dengan Akumulasi Poin Denda Aktif</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach ($rekapMember as $m)
                    <div class="bg-ivory-paper border border-sage/25 rounded-card p-4 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between gap-2">
                                <p class="font-semibold text-sm text-charcoal truncate">{{ $m->nama_lengkap }}</p>
                                <span class="px-2.5 py-0.5 rounded-card text-xs font-bold {{ $m->isBlockedByDenda() ? 'bg-status-ditolak text-status-ditolak-text' : 'bg-status-diajukan text-status-diajukan-text' }}">
                                    {{ $m->total_poin_denda }} Poin
                                </span>
                            </div>
                            <p class="text-xs text-sage mt-0.5">{{ $m->isBlockedByDenda() ? 'Status: Terblokir Pinjam Baru' : 'Status: Masih Diizinkan Pinjam' }}</p>
                        </div>

                        <form action="{{ route($prefix . '.denda.lunasi-user', $m) }}" method="POST" class="mt-3 pt-2.5 border-t border-sage/15"
                              data-confirm="Tandai seluruh denda aktif milik {{ $m->nama_lengkap }} ({{ $m->total_poin_denda }} poin) sebagai LUNAS?"
                              data-confirm-title="Lunasi Seluruh Denda Anggota"
                              data-confirm-button="Ya, Tandai Lunas">
                            @csrf
                            <input type="hidden" name="catatan" value="Dilunasi seluruhnya melalui meja layanan petugas.">
                            <x-button type="submit" variant="secondary" size="sm" class="w-full">
                                Lunasi Semua Poin
                            </x-button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Filter Tabel Denda --}}
    <form action="{{ route($prefix . '.denda.index') }}" method="GET" class="flex flex-wrap items-center gap-3 pb-6 border-b border-sage/20">
        <input
            type="text"
            name="q"
            value="{{ request('q') }}"
            placeholder="Cari nama atau username member..."
            class="w-full sm:w-64 rounded-card border border-sage/35 bg-ivory-paper px-3.5 py-2 text-xs text-charcoal focus:border-forest focus:outline-none"
        >
        <select name="status" class="rounded-card border border-sage/35 bg-ivory-paper px-3.5 py-2 text-xs text-charcoal focus:border-forest focus:outline-none">
            <option value="">Semua Status Denda</option>
            <option value="aktif" @selected(request('status') === 'aktif')>Aktif (Belum Lunas)</option>
            <option value="lunas" @selected(request('status') === 'lunas')>Sudah Lunas</option>
        </select>
        <select name="jenis" class="rounded-card border border-sage/35 bg-ivory-paper px-3.5 py-2 text-xs text-charcoal focus:border-forest focus:outline-none">
            <option value="">Semua Jenis</option>
            <option value="terlambat" @selected(request('jenis') === 'terlambat')>Terlambat</option>
            <option value="rusak" @selected(request('jenis') === 'rusak')>Rusak</option>
            <option value="hilang" @selected(request('jenis') === 'hilang')>Hilang</option>
        </select>

        <x-button type="submit" variant="secondary" size="sm">Filter</x-button>
        @if (request()->hasAny(['q', 'status', 'jenis']))
            <x-button href="{{ route($prefix . '.denda.index') }}" variant="secondary" size="sm">Reset</x-button>
        @endif
    </form>

    <div class="mt-6">
        @if ($dendaList->isEmpty())
            <x-empty-state judul="Tidak Ada Catatan Denda" deskripsi="Belum ada data denda poin yang sesuai dengan filter." />
        @else
            <x-table>
                <x-slot:head>
                    <tr>
                        <th class="px-4 py-3.5">Tanggal</th>
                        <th class="px-4 py-3.5">Anggota</th>
                        <th class="px-4 py-3.5">Buku</th>
                        <th class="px-4 py-3.5">Jenis & Poin</th>
                        <th class="px-4 py-3.5">Keterangan</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </x-slot:head>

                @foreach ($dendaList as $denda)
                    <tr>
                        <td class="px-4 py-4 text-xs text-sage">{{ $denda->created_at?->format('d M Y') }}</td>
                        <td class="px-4 py-4">
                            <p class="font-semibold text-charcoal">{{ $denda->user->nama_lengkap }}</p>
                            <p class="text-xs text-sage">Total aktif: {{ $denda->user->total_poin_denda }} poin</p>
                        </td>
                        <td class="px-4 py-4 text-xs font-medium text-charcoal">
                            {{ $denda->peminjaman?->buku?->judul ?? '-' }}
                        </td>
                        <td class="px-4 py-4">
                            <span class="capitalize text-xs font-semibold text-charcoal block">{{ $denda->jenis }}</span>
                            <span class="text-xs font-bold text-status-ditolak-text">+{{ $denda->poin }} Poin</span>
                        </td>
                        <td class="px-4 py-4 text-xs text-sage max-w-xs">{{ $denda->keterangan }}</td>
                        <td class="px-4 py-4">
                            <x-badge-status :status="$denda->status" />
                        </td>
                        <td class="px-4 py-4 text-right whitespace-nowrap">
                            @if ($denda->status === 'aktif')
                                <form action="{{ route($prefix . '.denda.lunas', $denda) }}" method="POST"
                                      data-confirm="Tandai denda {{ $denda->poin }} poin ({{ $denda->jenis }}) milik {{ $denda->user->nama_lengkap }} sebagai LUNAS?"
                                      data-confirm-title="Konfirmasi Pelunasan Denda"
                                      data-confirm-button="Ya, Tandai Lunas">
                                    @csrf
                                    <input type="hidden" name="catatan" value="Diselesaikan dan diverifikasi oleh petugas.">
                                    <x-button type="submit" variant="primary" size="sm">
                                        Tandai Lunas
                                    </x-button>
                                </form>
                            @else
                                <span class="text-xs text-sage">Selesai</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-table>

            <div class="mt-6">
                <x-pagination :paginator="$dendaList" />
            </div>
        @endif
    </div>
</x-layouts.dashboard>

