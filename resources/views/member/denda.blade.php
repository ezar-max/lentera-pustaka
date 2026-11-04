<x-layouts.app title="Poin Denda Saya">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="pb-6 border-b border-sage/20">
            <p class="text-xs font-semibold uppercase tracking-wider text-sage">Transparansi Sirkulasi</p>
            <h1 class="mt-1 font-sans text-2xl sm:text-3xl font-semibold text-forest">Rincian Poin Denda Anggota</h1>
        </div>

        {{-- Ringkasan Poin & Cara Penyelesaian --}}
        <div class="mt-8 grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <div class="lg:col-span-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-stat-card
                    label="Poin Denda Aktif"
                    :value="$totalAktif . ' Poin'"
                    subtitle="Batas blokir pinjaman: {{ $batasBlokir }} poin"
                    :accent="$totalAktif >= $batasBlokir"
                />
                <x-stat-card
                    label="Riwayat Denda Lunas"
                    :value="$totalLunas . ' Poin'"
                    subtitle="Telah diselesaikan bersama petugas"
                />
            </div>

            <div class="lg:col-span-7 bg-ivory-paper border border-sage/25 rounded-card p-5 text-xs space-y-2">
                <h2 class="text-sm font-semibold text-forest">Ketentuan & Cara Menyelesaikan Poin Denda</h2>
                <p class="text-charcoal-light leading-relaxed">
                    Sistem Lentera Pustaka menerapkan denda berbasis poin: <strong>Terlambat ({{ config('perpustakaan.denda.terlambat_per_hari', 2) }} poin/hari)</strong>,
                    <strong>Buku Rusak ({{ config('perpustakaan.denda.rusak', 25) }} poin)</strong>, dan
                    <strong>Buku Hilang ({{ config('perpustakaan.denda.hilang', 100) }} poin)</strong>.
                    Apabila total poin aktif mencapai <strong>{{ $batasBlokir }} poin</strong>, Anda tidak dapat mengajukan peminjaman baru.
                </p>
                <p class="text-sage">
                    Untuk melunasi poin denda aktif, silakan kunjungi meja layanan petugas perpustakaan atau hubungi staf melalui menu Chat untuk verifikasi penyelesaian administrasi/penggantian buku.
                </p>
            </div>
        </div>

        {{-- Tabel Riwayat Denda --}}
        <div class="mt-8">
            @if ($dendaList->isEmpty())
                <x-empty-state
                    judul="Tidak Ada Catatan Denda"
                    deskripsi="Terima kasih telah menjaga ketertiban waktu pengembalian dan merawat koleksi buku Lentera Pustaka."
                />
            @else
                <x-table>
                    <x-slot:head>
                        <tr>
                            <th class="px-4 py-3.5">Tanggal</th>
                            <th class="px-4 py-3.5">Buku Terkait</th>
                            <th class="px-4 py-3.5">Jenis Pelanggaran</th>
                            <th class="px-4 py-3.5">Besar Poin</th>
                            <th class="px-4 py-3.5">Keterangan</th>
                            <th class="px-4 py-3.5">Status</th>
                        </tr>
                    </x-slot:head>

                    @foreach ($dendaList as $denda)
                        <tr>
                            <td class="px-4 py-4 text-xs text-sage">
                                {{ $denda->created_at?->format('d M Y') }}
                            </td>
                            <td class="px-4 py-4 font-medium text-charcoal">
                                {{ $denda->peminjaman?->buku?->judul ?? '-' }}
                            </td>
                            <td class="px-4 py-4 capitalize text-xs font-semibold text-charcoal">
                                {{ $denda->jenis }}
                            </td>
                            <td class="px-4 py-4 font-semibold text-status-ditolak-text">
                                +{{ $denda->poin }} Poin
                            </td>
                            <td class="px-4 py-4 text-xs text-sage">
                                {{ $denda->keterangan ?: '-' }}
                            </td>
                            <td class="px-4 py-4">
                                <x-badge-status :status="$denda->status" />
                            </td>
                        </tr>
                    @endforeach
                </x-table>

                <div class="mt-6">
                    <x-pagination :paginator="$dendaList" />
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>

