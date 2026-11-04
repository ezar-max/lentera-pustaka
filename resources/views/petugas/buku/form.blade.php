<x-layouts.dashboard
    :title="$isEdit ? 'Ubah Data Buku' : 'Tambah Buku Baru'"
    :header="$isEdit ? 'Sunting Koleksi Buku' : 'Tambah Koleksi Buku Baru'"
    subheader="Pastikan informasi bibliografi, jumlah stok total, dan kategori terisi dengan akurat."
>
    @php
        $prefix = auth()->user()->isAdmin() && request()->is('admin*') ? 'admin' : 'petugas';
        $selectedKategori = old('kategori_ids', $isEdit ? $buku->kategori->pluck('id')->all() : []);
    @endphp

    <div class="max-w-3xl">
        <div class="mb-6">
            <a href="{{ route($prefix . '.buku.index') }}" class="text-xs font-semibold uppercase tracking-wider text-sage hover:text-forest">
                &larr; Kembali ke Daftar Buku
            </a>
        </div>

        <form action="{{ $isEdit ? route($prefix . '.buku.update', $buku) : route($prefix . '.buku.store') }}"
              method="POST"
              enctype="multipart/form-data"
              class="bg-ivory-paper border border-sage/25 rounded-card p-6 sm:p-8 space-y-5">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif

            <x-input label="Judul Buku" name="judul" :value="$buku->judul" required />

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <x-input label="Penulis / Pengarang" name="penulis" :value="$buku->penulis" required />
                <x-input label="Penerbit" name="penerbit" :value="$buku->penerbit" required />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <x-input label="Tahun Terbit" name="tahun_terbit" type="number" :value="$buku->tahun_terbit ?: date('Y')" required />
                <x-input label="Kode ISBN (Opsional)" name="isbn" :value="$buku->isbn" />
                <x-input
                    label="Stok Total Eksemplar"
                    name="stok_total"
                    type="number"
                    :value="$buku->stok_total ?? 1"
                    :hint="$isEdit ? 'Stok tersedia saat ini: ' . $buku->stok_tersedia . ' eks.' : 'Stok tersedia otomatis mengikuti stok total awal.'"
                    required
                />
            </div>

            {{-- Pilihan Multi Kategori --}}
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-charcoal mb-2">
                    Kategori Buku (Dapat Memilih Lebih dari Satu) <span class="text-status-ditolak-text">*</span>
                </label>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 p-4 rounded-card bg-ivory border border-sage/25">
                    @foreach ($kategoriList as $kat)
                        <label class="inline-flex items-center gap-2 text-xs text-charcoal cursor-pointer">
                            <input
                                type="checkbox"
                                name="kategori_ids[]"
                                value="{{ $kat->id }}"
                                @checked(in_array($kat->id, $selectedKategori))
                                class="rounded border-sage/40 text-forest focus:ring-forest"
                            >
                            <span>{{ $kat->nama_kategori }}</span>
                        </label>
                    @endforeach
                </div>
                @error('kategori_ids')
                    <p class="mt-1 text-xs font-medium text-status-ditolak-text">{{ $message }}</p>
                @enderror
            </div>

            {{-- Sinopsis --}}
            <div>
                <label for="sinopsis" class="block text-xs font-semibold uppercase tracking-wider text-charcoal mb-1.5">
                    Sinopsis / Ringkasan Isi Buku
                </label>
                <textarea
                    id="sinopsis"
                    name="sinopsis"
                    rows="4"
                    placeholder="Tuliskan deskripsi singkat mengenai isi buku..."
                    class="w-full rounded-card border border-sage/35 bg-ivory-paper px-4 py-2.5 text-sm text-charcoal focus:border-forest focus:outline-none"
                >{{ old('sinopsis', $buku->sinopsis) }}</textarea>
            </div>

            {{-- Upload Cover --}}
            <div>
                <label for="cover" class="block text-xs font-semibold uppercase tracking-wider text-charcoal mb-1.5">
                    Gambar Sampul Buku (JPG/PNG/WebP, Maks. 2MB)
                </label>
                <input
                    id="cover"
                    type="file"
                    name="cover"
                    accept="image/*"
                    class="w-full rounded-card border border-sage/35 bg-ivory-paper px-4 py-2 text-xs text-charcoal file:mr-4 file:py-1.5 file:px-3 file:rounded-card file:border-0 file:text-xs file:font-semibold file:bg-forest file:text-ivory"
                >
                @if ($isEdit && $buku->cover)
                    <p class="mt-1.5 text-xs text-sage">Sampul saat ini sudah tersimpan. Biarkan kosong jika tidak ingin mengganti sampul.</p>
                @endif
                @error('cover')
                    <p class="mt-1 text-xs font-medium text-status-ditolak-text">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-4 border-t border-sage/20 flex items-center justify-end gap-3">
                <x-button href="{{ route($prefix . '.buku.index') }}" variant="secondary">Batal</x-button>
                <x-button type="submit" variant="primary">
                    {{ $isEdit ? 'Simpan Perubahan Buku' : 'Simpan Buku Baru' }}
                </x-button>
            </div>
        </form>
    </div>
</x-layouts.dashboard>

