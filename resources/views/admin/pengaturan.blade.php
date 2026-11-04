<x-layouts.dashboard title="Pengaturan Aturan" header="Pengaturan Parameter Sirkulasi & Denda" subheader="Konfigurasi durasi pinjam, kuota buku aktif, batas waktu pengambilan, dan besaran poin denda.">
    <div class="max-w-2xl">
        <form action="{{ route('admin.pengaturan.update') }}" method="POST" class="bg-ivory-paper border border-sage/25 rounded-card p-6 sm:p-8 space-y-6">
            @csrf
            @method('PUT')

            <div>
                <h2 class="text-sm font-semibold uppercase tracking-wider text-forest pb-2 border-b border-sage/20">
                    Parameter Kuota & Waktu Sirkulasi
                </h2>
                <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <x-input
                        label="Kuota Pinjam Aktif (Buku)"
                        name="kuota_pinjam_aktif"
                        type="number"
                        :value="$aturan['kuota_pinjam_aktif']"
                        required
                    />
                    <x-input
                        label="Durasi Peminjaman (Hari)"
                        name="durasi_pinjam_hari"
                        type="number"
                        :value="$aturan['durasi_pinjam_hari']"
                        required
                    />
                    <x-input
                        label="Batas Ambil Buku (Hari)"
                        name="batas_ambil_hari"
                        type="number"
                        :value="$aturan['batas_ambil_hari']"
                        required
                    />
                </div>
            </div>

            <div>
                <h2 class="text-sm font-semibold uppercase tracking-wider text-forest pb-2 border-b border-sage/20">
                    Parameter Poin Denda & Ambang Blokir
                </h2>
                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-input
                        label="Denda Terlambat (Poin / Hari)"
                        name="denda_terlambat"
                        type="number"
                        :value="$aturan['denda_terlambat']"
                        required
                    />
                    <x-input
                        label="Denda Buku Rusak (Poin)"
                        name="denda_rusak"
                        type="number"
                        :value="$aturan['denda_rusak']"
                        required
                    />
                    <x-input
                        label="Denda Buku Hilang (Poin)"
                        name="denda_hilang"
                        type="number"
                        :value="$aturan['denda_hilang']"
                        required
                    />
                    <x-input
                        label="Batas Poin Blokir Pinjaman"
                        name="batas_poin_blokir"
                        type="number"
                        :value="$aturan['batas_poin_blokir']"
                        required
                    />
                </div>
            </div>

            <div class="pt-4 border-t border-sage/20 flex justify-end">
                <x-button type="submit" variant="primary">
                    Simpan Parameter Aturan
                </x-button>
            </div>
        </form>
    </div>
</x-layouts.dashboard>

