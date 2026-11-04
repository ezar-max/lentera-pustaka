<?php

namespace App\Services;

use App\Models\Denda;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DendaService
{
    /**
     * Proses pencatatan denda saat konfirmasi pengembalian buku.
     */
    public function prosesDendaPengembalian(Peminjaman $peminjaman): void
    {
        DB::transaction(function () use ($peminjaman): void {
            $hariTerlambat = (int) $peminjaman->hari_terlambat;
            $kondisi = $peminjaman->kondisi_buku;

            if ($hariTerlambat > 0) {
                $poinPerHari = (int) config('perpustakaan.denda.terlambat_per_hari', 2);
                $totalPoinTerlambat = $hariTerlambat * $poinPerHari;

                Denda::create([
                    'peminjaman_id' => $peminjaman->id,
                    'user_id' => $peminjaman->user_id,
                    'jenis' => 'terlambat',
                    'poin' => $totalPoinTerlambat,
                    'keterangan' => "Terlambat {$hariTerlambat} hari ({$poinPerHari} poin/hari).",
                    'status' => 'aktif',
                ]);
            }

            if ($kondisi === 'rusak') {
                $poinRusak = (int) config('perpustakaan.denda.rusak', 25);

                Denda::create([
                    'peminjaman_id' => $peminjaman->id,
                    'user_id' => $peminjaman->user_id,
                    'jenis' => 'rusak',
                    'poin' => $poinRusak,
                    'keterangan' => 'Buku dikembalikan dalam kondisi rusak.',
                    'status' => 'aktif',
                ]);
            } elseif ($kondisi === 'hilang') {
                $poinHilang = (int) config('perpustakaan.denda.hilang', 100);

                Denda::create([
                    'peminjaman_id' => $peminjaman->id,
                    'user_id' => $peminjaman->user_id,
                    'jenis' => 'hilang',
                    'poin' => $poinHilang,
                    'keterangan' => 'Buku dinyatakan hilang oleh peminjam.',
                    'status' => 'aktif',
                ]);
            }

            $this->sinkronkanTotalPoinUser($peminjaman->user_id);
        });
    }

    /**
     * Tandai satu catatan denda sebagai lunas dan perbarui total poin user.
     */
    public function tandaiLunas(Denda $denda, ?string $catatan = null): Denda
    {
        return DB::transaction(function () use ($denda, $catatan): Denda {
            $keteranganBaru = $denda->keterangan;
            if ($catatan) {
                $keteranganBaru = trim(($keteranganBaru ? $keteranganBaru.' | ' : '').'Penyelesaian: '.$catatan);
            }

            $denda->update([
                'status' => 'lunas',
                'keterangan' => $keteranganBaru,
            ]);

            $this->sinkronkanTotalPoinUser($denda->user_id);

            return $denda->fresh();
        });
    }

    /**
     * Tandai seluruh denda aktif milik seorang member sebagai lunas.
     */
    public function lunasiSemuaDendaUser(User $user, ?string $catatan = null): void
    {
        DB::transaction(function () use ($user, $catatan): void {
            $dendaAktif = Denda::where('user_id', $user->id)->where('status', 'aktif')->get();

            foreach ($dendaAktif as $denda) {
                $keteranganBaru = $denda->keterangan;
                if ($catatan) {
                    $keteranganBaru = trim(($keteranganBaru ? $keteranganBaru.' | ' : '').'Penyelesaian: '.$catatan);
                }

                $denda->update([
                    'status' => 'lunas',
                    'keterangan' => $keteranganBaru,
                ]);
            }

            $this->sinkronkanTotalPoinUser($user->id);
        });
    }

    /**
     * Sinkronkan kolom users.total_poin_denda dengan jumlah denda berstatus aktif.
     */
    public function sinkronkanTotalPoinUser(int $userId): int
    {
        $totalAktif = (int) Denda::where('user_id', $userId)
            ->where('status', 'aktif')
            ->sum('poin');

        User::where('id', $userId)->update([
            'total_poin_denda' => $totalAktif,
        ]);

        return $totalAktif;
    }
}
