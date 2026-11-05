<x-layouts.dashboard title="Pengaturan Aturan" header="Pengaturan Parameter Sirkulasi" subheader="Konfigurasi durasi pinjam, kuota buku aktif, dan batas waktu pengambilan buku.">
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

            <div class="pt-4 border-t border-sage/20 flex justify-end">
                <x-button type="submit" variant="primary">
                    Simpan Parameter Aturan
                </x-button>
            </div>
        </form>
    </div>
</x-layouts.dashboard>

