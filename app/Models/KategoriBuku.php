<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriBuku extends Model
{
    use HasFactory;

    protected $table = 'kategori_buku';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nama_kategori',
    ];

    public function buku(): BelongsToMany
    {
        return $this->belongsToMany(Buku::class, 'kategori_buku_relasi', 'kategori_id', 'buku_id')
            ->withTimestamps();
    }

    public function bukuRelasi(): HasMany
    {
        return $this->hasMany(KategoriBukuRelasi::class, 'kategori_id');
    }
}
