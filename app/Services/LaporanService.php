<?php

namespace App\Services;

use App\Models\Buku;
use App\Models\Denda;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class LaporanService
{
    /**
     * Ambil data laporan sesuai jenis dan filter.
     *
     * @param  array<string, mixed>  $filters
     * @return Collection<int, mixed>
     */
    public function ambilDataLaporan(string $jenis, array $filters = []): Collection
    {
        return match ($jenis) {
            'stok' => $this->laporanStokBuku($filters),
            'denda' => $this->laporanDenda($filters),
            'member' => $this->laporanMember($filters),
            default => $this->laporanPeminjaman($filters),
        };
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, Peminjaman>
     */
    public function laporanPeminjaman(array $filters = []): Collection
    {
        $query = Peminjaman::with(['user', 'buku', 'petugas'])->latest('tanggal_pengajuan');

        if (! empty($filters['tanggal_mulai'])) {
            $query->whereDate('tanggal_pengajuan', '>=', $filters['tanggal_mulai']);
        }

        if (! empty($filters['tanggal_selesai'])) {
            $query->whereDate('tanggal_pengajuan', '<=', $filters['tanggal_selesai']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['petugas_id'])) {
            $query->where('petugas_id', $filters['petugas_id']);
        }

        return $query->get();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, Buku>
     */
    public function laporanStokBuku(array $filters = []): Collection
    {
        $query = Buku::with('kategori')->orderBy('judul');

        if (! empty($filters['kategori_id'])) {
            $kategoriId = (int) $filters['kategori_id'];
            $query->whereHas('kategori', fn ($q) => $q->where('kategori_buku.id', $kategoriId));
        }

        if (! empty($filters['stok_menipis'])) {
            $batas = (int) config('perpustakaan.batas_stok_menipis', 2);
            $query->where('stok_tersedia', '<=', $batas);
        }

        return $query->get();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, Denda>
     */
    public function laporanDenda(array $filters = []): Collection
    {
        $query = Denda::with(['user', 'peminjaman.buku'])->latest();

        if (! empty($filters['tanggal_mulai'])) {
            $query->whereDate('created_at', '>=', $filters['tanggal_mulai']);
        }

        if (! empty($filters['tanggal_selesai'])) {
            $query->whereDate('created_at', '<=', $filters['tanggal_selesai']);
        }

        if (! empty($filters['status_denda'])) {
            $query->where('status', $filters['status_denda']);
        }

        return $query->get();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, User>
     */
    public function laporanMember(array $filters = []): Collection
    {
        $query = User::where('role', 'peminjam')->latest();

        if (! empty($filters['status_akun'])) {
            $query->where('status', $filters['status_akun']);
        }

        return $query->get();
    }
}
