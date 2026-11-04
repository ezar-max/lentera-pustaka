<x-layouts.app title="Dashboard Anggota">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        {{-- Sapaan & Peringatan Denda Dini --}}
        <div class="pb-8 border-b border-sage/20 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-sage">Ruang Anggota</p>
                <h1 class="mt-1 font-sans text-2xl sm:text-3xl font-semibold text-forest">
                    Selamat Datang, {{ $user->nama_lengkap }}
                </h1>
            </div>
            <div class="flex items-center gap-3">
                <x-button href="{{ route('member.katalog') }}" variant="primary">
                    Cari & Pinjam Buku
                </x-button>
                <x-button href="{{ route('member.chat') }}" variant="secondary">
                    Tanya Asisten AI
                </x-button>
            </div>
        </div>

        {{-- Peringatan Denda jika Mendekati / Melewati Batas --}}
        @if ($user->total_poin_denda >= $aturan['batas_poin_blokir'])
            <div class="mt-6 p-4 rounded-card bg-status-ditolak border border-status-ditolak-text/25 text-status-ditolak-text text-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <strong class="font-semibold block">Batas Poin Denda Tercapai ({{ $user->total_poin_denda }} / {{ $aturan['batas_poin_blokir'] }} Poin)</strong>
                    <span>Pengajuan peminjaman baru dinonaktifkan sementara hingga poin denda aktif Anda diselesaikan bersama petugas perpustakaan.</span>
                </div>
                <x-button href="{{ route('member.denda.index') }}" variant="danger" size="sm">
                    Lihat Rincian Denda
                </x-button>
            </div>
        @elseif ($user->total_poin_denda >= $aturan['batas_poin_peringatan'])
            <div class="mt-6 p-4 rounded-card bg-status-diajukan border border-status-diajukan-text/25 text-status-diajukan-text text-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <strong class="font-semibold block">Peringatan Dini Poin Denda ({{ $user->total_poin_denda }} Poin)</strong>
                    <span>Poin denda aktif Anda mendekati batas blokir {{ $aturan['batas_poin_blokir'] }} poin. Segera kembalikan buku tepat waktu atau hubungi petugas.</span>
                </div>
                <x-button href="{{ route('member.denda.index') }}" variant="secondary" size="sm">
                    Cek Poin Denda
                </x-button>
            </div>
        @endif

        {{-- Kartu Ringkasan --}}
        <div class="mt-8 grid grid-cols-1 sm:grid-cols-3 gap-5">
            <x-stat-card
                label="Pinjaman Sedang Berjalan"
                :value="$pinjamanAktifCount . ' / ' . $aturan['kuota_pinjam_aktif']"
                subtitle="Maksimal {{ $aturan['kuota_pinjam_aktif'] }} buku aktif bersamaan"
            />
            <x-stat-card
                label="Pengajuan Menunggu / Disetujui"
                :value="$menungguPersetujuanCount"
                subtitle="Segera ambil buku maksimal {{ $aturan['batas_ambil_hari'] }} hari setelah disetujui"
            />
            <x-stat-card
                label="Total Poin Denda Aktif"
                :value="$user->total_poin_denda . ' Poin'"
                subtitle="Batas blokir pengajuan baru: {{ $aturan['batas_poin_blokir'] }} poin"
                :accent="$user->total_poin_denda >= $aturan['batas_poin_blokir']"
            />
        </div>

        {{-- Dua Kolom: Pinjaman Aktif & Notifikasi Status --}}
        <div class="mt-10 grid grid-cols-1 lg:grid-cols-12 gap-8 pb-10 border-b border-sage/20">
            {{-- Daftar Pinjaman & Tenggat --}}
            <div class="lg:col-span-7">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-semibold text-forest">Peminjaman Aktif & Pengingat Jatuh Tempo</h2>
                    <a href="{{ route('member.peminjaman.index') }}" class="text-xs font-semibold text-forest hover:underline">
                        Lihat Semua &rarr;
                    </a>
                </div>

                @if ($daftarPinjamanAktif->isEmpty())
                    <x-empty-state
                        judul="Belum Ada Pinjaman Aktif"
                        deskripsi="Anda sedang tidak meminjam atau mengajukan buku apa pun saat ini."
                    />
                @else
                    <div class="divide-y divide-sage/20 border-y border-sage/20">
                        @foreach ($daftarPinjamanAktif as $pinjam)
                            <div class="py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <x-badge-status :status="$pinjam->status" />
                                        @if ($pinjam->isTerlambatSaatIni())
                                            <span class="px-2.5 py-0.5 rounded-card text-xs font-semibold bg-status-ditolak text-status-ditolak-text">
                                                Terlambat {{ $pinjam->hitungHariTerlambatSaatIni() }} Hari
                                            </span>
                                        @endif
                                    </div>
                                    <h3 class="mt-1.5 font-semibold text-sm text-charcoal">
                                        <a href="{{ route('member.peminjaman.show', $pinjam) }}" class="hover:text-forest">
                                            {{ $pinjam->buku->judul }}
                                        </a>
                                    </h3>
                                    <p class="text-xs text-sage mt-0.5">
                                        @if ($pinjam->status === 'disetujui')
                                            Batas Pengambilan: <strong class="text-charcoal">{{ $pinjam->batas_pengambilan?->format('d M Y') }}</strong>
                                        @elseif ($pinjam->tanggal_jatuh_tempo)
                                            Tenggat Pengembalian: <strong class="{{ $pinjam->isTerlambatSaatIni() ? 'text-status-ditolak-text' : 'text-charcoal' }}">{{ $pinjam->tanggal_jatuh_tempo->format('d M Y') }}</strong>
                                        @else
                                            Diajukan pada {{ $pinjam->tanggal_pengajuan->format('d M Y') }}
                                        @endif
                                    </p>
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    @if ($pinjam->status === 'dipinjam')
                                        <form action="{{ route('member.peminjaman.ajukan-kembali', $pinjam) }}" method="POST"
                                              data-confirm="Ajukan pengembalian untuk buku {{ $pinjam->buku->judul }}?"
                                              data-confirm-title="Ajukan Pengembalian Buku"
                                              data-confirm-button="Ya, Ajukan Kembali">
                                            @csrf
                                            <x-button type="submit" variant="gold" size="sm">
                                                Ajukan Kembali
                                            </x-button>
                                        </form>
                                    @endif
                                    <x-button href="{{ route('member.peminjaman.show', $pinjam) }}" variant="secondary" size="sm">
                                        Detail Alur
                                    </x-button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Notifikasi Status Terbaru --}}
            <div class="lg:col-span-5">
                <h2 class="text-base font-semibold text-forest mb-4">Pembaruan Status Terakhir</h2>

                @if ($notifikasiTerbaru->isEmpty())
                    <p class="text-sm text-sage py-6 border-y border-sage/20">Belum ada notifikasi keputusan terbaru dari petugas.</p>
                @else
                    <div class="divide-y divide-sage/20 border-y border-sage/20">
                        @foreach ($notifikasiTerbaru as $notif)
                            <div class="py-3.5 text-xs space-y-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold text-charcoal">{{ $notif->buku->judul }}</span>
                                    <x-badge-status :status="$notif->status" />
                                </div>
                                @if ($notif->status === 'disetujui')
                                    <p class="text-sage">Disetujui petugas. Silakan ambil buku fisik sebelum <strong class="text-charcoal">{{ $notif->batas_pengambilan?->format('d M Y') }}</strong>.</p>
                                @elseif ($notif->status === 'ditolak')
                                    <p class="text-status-ditolak-text">Ditolak: "{{ $notif->alasan_penolakan }}"</p>
                                @elseif ($notif->status === 'kembali_disetujui')
                                    <p class="text-forest">Pengajuan kembali disetujui. Silakan serahkan buku ke meja petugas.</p>
                                @elseif ($notif->status === 'dibatalkan')
                                    <p class="text-sage">{{ $notif->catatan_petugas ?: 'Pengajuan telah dibatalkan.' }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Kartu Aturan Peminjaman (7 Langkah Alur + Parameter Resmi) --}}
        <div class="mt-10 bg-ivory-paper border border-sage/25 rounded-card p-6 sm:p-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-5 border-b border-sage/20">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-gold">Panduan Resmi Sirkulasi</span>
                    <h2 class="font-sans text-xl font-semibold text-forest mt-0.5">Aturan & 7 Langkah Alur Peminjaman Lentera Pustaka</h2>
                </div>
                <span class="text-xs text-sage">Berlaku untuk seluruh anggota aktif</span>
            </div>

            {{-- 7 Langkah Alur --}}
            <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-7 gap-4 text-xs">
                <div class="p-3.5 rounded-card bg-ivory border border-sage/20">
                    <span class="font-bold text-forest block">1. Ajukan Pinjam</span>
                    <p class="mt-1 text-sage">Pilih buku tersedia di katalog dan kirim pengajuan online.</p>
                </div>
                <div class="p-3.5 rounded-card bg-ivory border border-sage/20">
                    <span class="font-bold text-forest block">2. Verifikasi Petugas</span>
                    <p class="mt-1 text-sage">Petugas menyetujui (stok dikurangi 1) atau menolak dengan alasan.</p>
                </div>
                <div class="p-3.5 rounded-card bg-ivory border border-sage/20">
                    <span class="font-bold text-forest block">3. Ambil Buku Fisik</span>
                    <p class="mt-1 text-sage">Ambil di perpustakaan maks. {{ $aturan['batas_ambil_hari'] }} hari sebelum batal otomatis.</p>
                </div>
                <div class="p-3.5 rounded-card bg-ivory border border-sage/20">
                    <span class="font-bold text-forest block">4. Masa Pinjam</span>
                    <p class="mt-1 text-sage">Durasi baca {{ $aturan['durasi_pinjam_hari'] }} hari sejak buku diserahkan oleh petugas.</p>
                </div>
                <div class="p-3.5 rounded-card bg-ivory border border-sage/20">
                    <span class="font-bold text-forest block">5. Ajukan Kembali</span>
                    <p class="mt-1 text-sage">Klik tombol Ajukan Pengembalian pada menu Peminjaman Saya.</p>
                </div>
                <div class="p-3.5 rounded-card bg-ivory border border-sage/20">
                    <span class="font-bold text-forest block">6. Persetujuan Kembali</span>
                    <p class="mt-1 text-sage">Petugas menyetujui pengajuan kembali, silakan bawa buku ke lokasi.</p>
                </div>
                <div class="p-3.5 rounded-card bg-ivory border border-sage/20">
                    <span class="font-bold text-forest block">7. Konfirmasi & Selesai</span>
                    <p class="mt-1 text-sage">Petugas memeriksa kondisi buku & waktu kembali, stok diperbarui.</p>
                </div>
            </div>

            {{-- Parameter Ketentuan Kuota & Denda --}}
            <div class="mt-6 pt-5 border-t border-sage/20 grid grid-cols-2 sm:grid-cols-5 gap-4 text-xs">
                <div>
                    <span class="text-sage block">Kuota Pinjam Aktif</span>
                    <strong class="text-charcoal text-sm">Maksimal {{ $aturan['kuota_pinjam_aktif'] }} Buku</strong>
                </div>
                <div>
                    <span class="text-sage block">Durasi Peminjaman</span>
                    <strong class="text-charcoal text-sm">{{ $aturan['durasi_pinjam_hari'] }} Hari Kalender</strong>
                </div>
                <div>
                    <span class="text-sage block">Denda Keterlambatan</span>
                    <strong class="text-charcoal text-sm">{{ $aturan['denda_terlambat'] }} Poin / Hari</strong>
                </div>
                <div>
                    <span class="text-sage block">Denda Rusak / Hilang</span>
                    <strong class="text-charcoal text-sm">{{ $aturan['denda_rusak'] }} / {{ $aturan['denda_hilang'] }} Poin</strong>
                </div>
                <div>
                    <span class="text-sage block">Batas Blokir Pinjam</span>
                    <strong class="text-status-ditolak-text text-sm">{{ $aturan['batas_poin_blokir'] }} Poin Aktif</strong>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

