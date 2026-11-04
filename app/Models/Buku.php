<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Buku extends Model
{
    use HasFactory;

    protected $table = 'buku';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'judul',
        'penulis',
        'penerbit',
        'tahun_terbit',
        'isbn',
        'sinopsis',
        'cover',
        'stok_total',
        'stok_tersedia',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tahun_terbit' => 'integer',
            'stok_total' => 'integer',
            'stok_tersedia' => 'integer',
        ];
    }

    public function kategori(): BelongsToMany
    {
        return $this->belongsToMany(KategoriBuku::class, 'kategori_buku_relasi', 'buku_id', 'kategori_id')
            ->withTimestamps();
    }

    public function kategoriRelasi(): HasMany
    {
        return $this->hasMany(KategoriBukuRelasi::class, 'buku_id');
    }

    public function peminjaman(): HasMany
    {
        return $this->hasMany(Peminjaman::class, 'buku_id');
    }

    public function ulasan(): HasMany
    {
        return $this->hasMany(UlasanBuku::class, 'buku_id');
    }

    public function koleksiPribadi(): HasMany
    {
        return $this->hasMany(KoleksiPribadi::class, 'buku_id');
    }

    public function getSedangDipinjamCountAttribute(): int
    {
        return max(0, $this->stok_total - $this->stok_tersedia);
    }

    public function isStokMenipis(): bool
    {
        return $this->stok_tersedia <= (int) config('perpustakaan.batas_stok_menipis', 2);
    }
}
