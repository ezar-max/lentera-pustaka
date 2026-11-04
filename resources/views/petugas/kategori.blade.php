<x-layouts.dashboard title="Master Kategori Buku" header="Master Kategori Koleksi" subheader="Kelola daftar kategori atau genre buku. Kategori yang masih terhubung dengan buku tidak dapat dihapus.">
    @php
        $prefix = auth()->user()->isAdmin() && request()->is('admin*') ? 'admin' : 'petugas';
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8" x-data="{ editId: null, editNama: '' }">
        {{-- Form Tambah Kategori --}}
        <div class="lg:col-span-4">
            <h2 class="text-base font-semibold text-forest mb-4">Tambah Kategori Baru</h2>
            <form action="{{ route($prefix . '.kategori.store') }}" method="POST" class="bg-ivory-paper border border-sage/25 rounded-card p-5 space-y-4">
                @csrf
                <x-input label="Nama Kategori" name="nama_kategori" placeholder="Contoh: Sastra Klasik" required />
                <x-button type="submit" variant="primary" class="w-full">
                    + Simpan Kategori
                </x-button>
            </form>
        </div>

        {{-- Tabel Daftar Kategori --}}
        <div class="lg:col-span-8">
            <h2 class="text-base font-semibold text-forest mb-4">Daftar Kategori ({{ $kategoriList->count() }})</h2>

            <x-table>
                <x-slot:head>
                    <tr>
                        <th class="px-4 py-3.5">Nama Kategori</th>
                        <th class="px-4 py-3.5 text-center">Jumlah Buku Terkait</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </x-slot:head>

                @foreach ($kategoriList as $kat)
                    <tr>
                        <td class="px-4 py-3.5">
                            <template x-if="editId !== {{ $kat->id }}">
                                <span class="font-semibold text-charcoal">{{ $kat->nama_kategori }}</span>
                            </template>

                            <template x-if="editId === {{ $kat->id }}">
                                <form action="{{ route($prefix . '.kategori.update', $kat) }}" method="POST" class="flex items-center gap-2">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="nama_kategori" x-model="editNama" required class="rounded-card border border-forest bg-ivory px-3 py-1.5 text-xs text-charcoal focus:outline-none">
                                    <x-button type="submit" variant="primary" size="sm">Simpan</x-button>
                                    <button type="button" @click="editId = null" class="text-xs text-sage hover:text-charcoal px-2">Batal</button>
                                </form>
                            </template>
                        </td>
                        <td class="px-4 py-3.5 text-center text-xs">
                            <span class="px-2.5 py-1 rounded-card bg-ivory-dark font-semibold text-charcoal">
                                {{ $kat->buku_count }} Judul
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-right whitespace-nowrap">
                            <div class="inline-flex items-center gap-2">
                                <button type="button"
                                        @click="editId = {{ $kat->id }}; editNama = '{{ addslashes($kat->nama_kategori) }}'"
                                        class="px-3 py-1.5 rounded-card text-xs font-medium bg-ivory-dark text-charcoal border border-sage/30 hover:bg-sage/15">
                                    Ubah
                                </button>

                                <form action="{{ route($prefix . '.kategori.destroy', $kat) }}" method="POST"
                                      data-confirm="Hapus kategori '{{ $kat->nama_kategori }}'?"
                                      data-confirm-title="Hapus Kategori"
                                      data-confirm-button="Ya, Hapus">
                                    @csrf
                                    @method('DELETE')
                                    <x-button type="submit" variant="danger" size="sm">
                                        Hapus
                                    </x-button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-table>
        </div>
    </div>
</x-layouts.dashboard>

