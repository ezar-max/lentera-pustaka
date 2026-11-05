<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Peminjaman extends Model
{
    use HasFactory;

    protected $table = 'peminjaman';

    public const STATUS_AKTIF = [
        'diajukan',
        'disetujui',
        'dipinjam',
        'pengajuan_kembali',
        'kembali_disetujui',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'buku_id',
        'petugas_id',
        'status',
        'tanggal_pengajuan',
        'tanggal_disetujui',
        'tanggal_pinjam',
        'tanggal_jatuh_tempo',
        'tanggal_pengajuan_kembali',
        'tanggal_kembali',
        'alasan_penolakan',
        'kondisi_buku',
        'ketepatan_waktu',
        'hari_terlambat',
        'catatan_petugas',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_pengajuan' => 'date',
            'tanggal_disetujui' => 'date',
            'tanggal_pinjam' => 'date',
            'tanggal_jatuh_tempo' => 'date',
            'tanggal_pengajuan_kembali' => 'date',
            'tanggal_kembali' => 'date',
            'hari_terlambat' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function buku(): BelongsTo
    {
        return $this->belongsTo(Buku::class, 'buku_id');
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->whereIn('status', self::STATUS_AKTIF);
    }

    public function getBatasPengambilanAttribute(): ?Carbon
    {
        if (! $this->tanggal_disetujui) {
            return null;
        }

        return $this->tanggal_disetujui->copy()->addDays((int) config('perpustakaan.batas_ambil_hari', 2));
    }

    public function isTerlambatSaatIni(): bool
    {
        if (! in_array($this->status, ['dipinjam', 'pengajuan_kembali', 'kembali_disetujui'], true)) {
            return false;
        }

        if (! $this->tanggal_jatuh_tempo) {
            return false;
        }

        return Carbon::today()->greaterThan($this->tanggal_jatuh_tempo->copy()->startOfDay());
    }

    public function hitungHariTerlambatSaatIni(): int
    {
        if (! $this->tanggal_jatuh_tempo) {
            return 0;
        }

        $jatuhTempo = $this->tanggal_jatuh_tempo->copy()->startOfDay();
        $hariIni = Carbon::today();

        if ($hariIni->lessThanOrEqualTo($jatuhTempo)) {
            return 0;
        }

        return (int) $jatuhTempo->diffInDays($hariIni);
    }
}
