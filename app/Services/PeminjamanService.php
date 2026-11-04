<?php

namespace App\Services;

use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\User;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;

class PeminjamanService
{
    public function __construct(public DendaService $dendaService) {}

    /**
     * Mengecek apakah user memenuhi seluruh aturan untuk meminjam buku tertentu.
     *
     * @return array{bisa: bool, alasan: ?string}
     */
    public function cekKelayakanPinjam(User $user, Buku $buku): array
    {
        if ($user->status !== 'aktif') {
            return [
                'bisa' => false,
                'alasan' => 'Akun Anda belum aktif.',
            ];
        }

        $batasPoin = (int) config('perpustakaan.batas_poin_blokir', 50);
        if ($user->total_poin_denda >= $batasPoin) {
            return [
                'bisa' => false,
                'alasan' => "Poin denda aktif Anda ({$user->total_poin_denda} poin) telah mencapai batas blokir ({$batasPoin} poin). Silakan selesaikan di meja petugas.",
            ];
        }

        $sedangDipinjamSama = Peminjaman::where('user_id', $user->id)
            ->where('buku_id', $buku->id)
            ->aktif()
            ->exists();

        if ($sedangDipinjamSama) {
            return [
                'bisa' => false,
                'alasan' => 'Anda sedang memiliki pengajuan atau pinjaman aktif untuk buku ini.',
            ];
        }

        if ($buku->stok_tersedia <= 0) {
            return [
                'bisa' => false,
                'alasan' => 'Stok buku saat ini sedang tidak tersedia (habis dipinjam).',
            ];
        }

        $kuotaMaksimal = (int) config('perpustakaan.kuota_pinjam_aktif', 3);
        $jumlahPinjamanAktif = Peminjaman::where('user_id', $user->id)
            ->aktif()
            ->count();

        if ($jumlahPinjamanAktif >= $kuotaMaksimal) {
            return [
                'bisa' => false,
                'alasan' => "Kuota peminjaman aktif Anda sudah penuh ({$jumlahPinjamanAktif}/{$kuotaMaksimal} buku).",
            ];
        }

        return [
            'bisa' => true,
            'alasan' => null,
        ];
    }

    /**
     * 1. Member mengajukan peminjaman buku.
     */
    public function ajukanPeminjaman(User $user, Buku $buku): Peminjaman
    {
        return DB::transaction(function () use ($user, $buku): Peminjaman {
            $bukuLocked = Buku::lockForUpdate()->findOrFail($buku->id);
            $userFresh = User::lockForUpdate()->findOrFail($user->id);

            $kelayakan = $this->cekKelayakanPinjam($userFresh, $bukuLocked);
            if (! $kelayakan['bisa']) {
                throw new DomainException($kelayakan['alasan'] ?? 'Tidak dapat mengajukan peminjaman.');
            }

            return Peminjaman::create([
                'user_id' => $userFresh->id,
                'buku_id' => $bukuLocked->id,
                'status' => 'diajukan',
                'tanggal_pengajuan' => Carbon::today(),
                'hari_terlambat' => 0,
            ]);
        });
    }

    /**
     * Member membatalkan sendiri pengajuan selagi masih berstatus 'diajukan'.
     */
    public function batalkanOlehMember(Peminjaman $peminjaman): Peminjaman
    {
        return DB::transaction(function () use ($peminjaman): Peminjaman {
            $pinjam = Peminjaman::lockForUpdate()->findOrFail($peminjaman->id);

            if ($pinjam->status !== 'diajukan') {
                throw new DomainException('Hanya pengajuan yang masih berstatus menunggu persetujuan yang dapat dibatalkan.');
            }

            $pinjam->update([
                'status' => 'dibatalkan',
                'catatan_petugas' => 'Dibatalkan oleh peminjam sebelum disetujui petugas.',
            ]);

            return $pinjam->fresh();
        });
    }

    /**
     * 2a. Petugas menyetujui pengajuan peminjaman (stok_tersedia - 1).
     */
    public function setujuiPeminjaman(Peminjaman $peminjaman, User $petugas): Peminjaman
    {
        return DB::transaction(function () use ($peminjaman, $petugas): Peminjaman {
            $pinjam = Peminjaman::lockForUpdate()->findOrFail($peminjaman->id);

            if ($pinjam->status !== 'diajukan') {
                throw new DomainException('Peminjaman ini tidak berada dalam status diajukan.');
            }

            $buku = Buku::lockForUpdate()->findOrFail($pinjam->buku_id);
            if ($buku->stok_tersedia <= 0) {
                throw new DomainException('Stok buku tersedia sudah habis, tidak dapat menyetujui pengajuan.');
            }

            $buku->decrement('stok_tersedia');

            $pinjam->update([
                'status' => 'disetujui',
                'petugas_id' => $petugas->id,
                'tanggal_disetujui' => Carbon::today(),
                'alasan_penolakan' => null,
            ]);

            return $pinjam->fresh();
        });
    }

    /**
     * 2b. Petugas menolak pengajuan peminjaman (alasan wajib).
     */
    public function tolakPeminjaman(Peminjaman $peminjaman, User $petugas, string $alasan): Peminjaman
    {
        return DB::transaction(function () use ($peminjaman, $petugas, $alasan): Peminjaman {
            $pinjam = Peminjaman::lockForUpdate()->findOrFail($peminjaman->id);

            if ($pinjam->status !== 'diajukan') {
                throw new DomainException('Peminjaman ini tidak berada dalam status diajukan.');
            }

            $pinjam->update([
                'status' => 'ditolak',
                'petugas_id' => $petugas->id,
                'alasan_penolakan' => trim($alasan),
            ]);

            return $pinjam->fresh();
        });
    }

    /**
     * 3. Petugas menyerahkan buku fisik saat member datang mengambil.
     */
    public function serahkanBuku(Peminjaman $peminjaman, User $petugas): Peminjaman
    {
        return DB::transaction(function () use ($peminjaman, $petugas): Peminjaman {
            $pinjam = Peminjaman::lockForUpdate()->findOrFail($peminjaman->id);

            if ($pinjam->status !== 'disetujui') {
                throw new DomainException('Hanya peminjaman berstatus disetujui yang dapat diserahkan bukunya.');
            }

            $durasi = (int) config('perpustakaan.durasi_pinjam_hari', 7);
            $hariIni = Carbon::today();

            $pinjam->update([
                'status' => 'dipinjam',
                'petugas_id' => $petugas->id,
                'tanggal_pinjam' => $hariIni,
                'tanggal_jatuh_tempo' => $hariIni->copy()->addDays($durasi),
            ]);

            return $pinjam->fresh();
        });
    }

    /**
     * 4. Member mengajukan pengembalian buku secara online.
     */
    public function ajukanPengembalian(Peminjaman $peminjaman): Peminjaman
    {
        return DB::transaction(function () use ($peminjaman): Peminjaman {
            $pinjam = Peminjaman::lockForUpdate()->findOrFail($peminjaman->id);

            if ($pinjam->status !== 'dipinjam') {
                throw new DomainException('Hanya buku yang sedang dipinjam yang dapat diajukan pengembaliannya.');
            }

            $pinjam->update([
                'status' => 'pengajuan_kembali',
                'tanggal_pengajuan_kembali' => Carbon::today(),
            ]);

            return $pinjam->fresh();
        });
    }

    /**
     * 5. Petugas menyetujui pengajuan pengembalian (member silakan membawa buku ke perpustakaan).
     */
    public function setujuiPengembalian(Peminjaman $peminjaman, User $petugas): Peminjaman
    {
        return DB::transaction(function () use ($peminjaman, $petugas): Peminjaman {
            $pinjam = Peminjaman::lockForUpdate()->findOrFail($peminjaman->id);

            if ($pinjam->status !== 'pengajuan_kembali') {
                throw new DomainException('Peminjaman ini tidak sedang dalam tahap pengajuan kembali.');
            }

            $pinjam->update([
                'status' => 'kembali_disetujui',
                'petugas_id' => $petugas->id,
            ]);

            return $pinjam->fresh();
        });
    }

    /**
     * 6. Petugas mengonfirmasi pengembalian buku fisik, kondisi, ketepatan waktu, denda, dan stok.
     */
    public function konfirmasiPengembalian(
        Peminjaman $peminjaman,
        User $petugas,
        string $kondisiBuku,
        ?string $catatanPetugas = null
    ): Peminjaman {
        return DB::transaction(function () use ($peminjaman, $petugas, $kondisiBuku, $catatanPetugas): Peminjaman {
            $pinjam = Peminjaman::lockForUpdate()->findOrFail($peminjaman->id);

            if (! in_array($pinjam->status, ['kembali_disetujui', 'pengajuan_kembali', 'dipinjam'], true)) {
                throw new DomainException('Status peminjaman tidak valid untuk konfirmasi pengembalian.');
            }

            $buku = Buku::lockForUpdate()->findOrFail($pinjam->buku_id);
            $tanggalKembali = Carbon::today();

            $hariTerlambat = 0;
            if ($pinjam->tanggal_jatuh_tempo) {
                $jatuhTempo = $pinjam->tanggal_jatuh_tempo->copy()->startOfDay();
                if ($tanggalKembali->greaterThan($jatuhTempo)) {
                    $hariTerlambat = (int) $jatuhTempo->diffInDays($tanggalKembali);
                }
            }

            $ketepatanWaktu = $hariTerlambat > 0 ? 'terlambat' : 'tepat_waktu';

            $pinjam->update([
                'status' => 'selesai',
                'petugas_id' => $petugas->id,
                'tanggal_kembali' => $tanggalKembali,
                'kondisi_buku' => $kondisiBuku,
                'ketepatan_waktu' => $ketepatanWaktu,
                'hari_terlambat' => $hariTerlambat,
                'catatan_petugas' => $catatanPetugas,
            ]);

            // Update stok sesuai aturan bisnis:
            // Jika baik atau rusak -> stok_tersedia +1
            // Jika hilang -> buku sudah -1 di stok_tersedia saat disetujui, maka stok_total dikurangi 1 (sehingga stok_total dan stok_tersedia berkurang permanen)
            if (in_array($kondisiBuku, ['baik', 'rusak'], true)) {
                $buku->update([
                    'stok_tersedia' => min($buku->stok_total, $buku->stok_tersedia + 1),
                ]);
            } elseif ($kondisiBuku === 'hilang') {
                $buku->update([
                    'stok_total' => max(0, $buku->stok_total - 1),
                ]);
            }

            $this->dendaService->prosesDendaPengembalian($pinjam);

            return $pinjam->fresh();
        });
    }

    /**
     * Batalkan otomatis semua pengajuan berstatus 'disetujui' yang tidak diambil lebih dari batas_ambil_hari.
     */
    public function batalkanPengajuanKadaluarsa(): int
    {
        $batasHari = (int) config('perpustakaan.batas_ambil_hari', 2);
        $batasTanggal = Carbon::today()->subDays($batasHari);

        $daftarKadaluarsa = Peminjaman::where('status', 'disetujui')
            ->whereDate('tanggal_disetujui', '<', $batasTanggal)
            ->get();

        $jumlah = 0;
        foreach ($daftarKadaluarsa as $peminjaman) {
            DB::transaction(function () use ($peminjaman, &$jumlah, $batasHari): void {
                $pinjam = Peminjaman::lockForUpdate()->find($peminjaman->id);
                if (! $pinjam || $pinjam->status !== 'disetujui') {
                    return;
                }

                $buku = Buku::lockForUpdate()->find($pinjam->buku_id);
                if ($buku) {
                    $buku->update([
                        'stok_tersedia' => min($buku->stok_total, $buku->stok_tersedia + 1),
                    ]);
                }

                $pinjam->update([
                    'status' => 'dibatalkan',
                    'catatan_petugas' => "Dibatalkan otomatis oleh sistem karena tidak diambil melewati batas {$batasHari} hari.",
                ]);

                $jumlah++;
            });
        }

        return $jumlah;
    }

    /**
     * Hitung keterlambatan harian pada seluruh buku yang masih dipinjam/pengajuan kembali.
     */
    public function hitungKeterlambatanHarian(): int
    {
        $pinjamanBerjalan = Peminjaman::whereIn('status', ['dipinjam', 'pengajuan_kembali', 'kembali_disetujui'])
            ->whereNotNull('tanggal_jatuh_tempo')
            ->get();

        $diperbarui = 0;
        foreach ($pinjamanBerjalan as $pinjam) {
            $hariTerlambat = $pinjam->hitungHariTerlambatSaatIni();
            if ($pinjam->hari_terlambat !== $hariTerlambat) {
                $pinjam->update(['hari_terlambat' => $hariTerlambat]);
                $diperbarui++;
            }
        }

        return $diperbarui;
    }
}
