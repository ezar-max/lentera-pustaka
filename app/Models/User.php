<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nama_lengkap',
        'username',
        'email',
        'password',
        'alamat',
        'no_hp',
        'role',
        'status',
        'alasan_penolakan',
        'total_poin_denda',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'total_poin_denda' => 'integer',
        ];
    }

    public function peminjaman(): HasMany
    {
        return $this->hasMany(Peminjaman::class, 'user_id');
    }

    public function peminjamanDiproses(): HasMany
    {
        return $this->hasMany(Peminjaman::class, 'petugas_id');
    }

    public function denda(): HasMany
    {
        return $this->hasMany(Denda::class, 'user_id');
    }

    public function dendaAktif(): HasMany
    {
        return $this->hasMany(Denda::class, 'user_id')->where('status', 'aktif');
    }

    public function ulasanBuku(): HasMany
    {
        return $this->hasMany(UlasanBuku::class, 'user_id');
    }

    public function koleksiPribadi(): HasMany
    {
        return $this->hasMany(KoleksiPribadi::class, 'user_id');
    }

    public function percakapan(): HasMany
    {
        return $this->hasMany(Percakapan::class, 'user_id');
    }

    public function percakapanDitangani(): HasMany
    {
        return $this->hasMany(Percakapan::class, 'staf_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPetugas(): bool
    {
        return $this->role === 'petugas';
    }

    public function isPeminjam(): bool
    {
        return $this->role === 'peminjam';
    }

    public function isStaf(): bool
    {
        return in_array($this->role, ['admin', 'petugas'], true);
    }

    public function isBlockedByDenda(): bool
    {
        return $this->total_poin_denda >= (int) config('perpustakaan.batas_poin_blokir', 50);
    }
}
