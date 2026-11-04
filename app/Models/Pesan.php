<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pesan extends Model
{
    use HasFactory;

    protected $table = 'pesan';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'percakapan_id',
        'pengirim',
        'pengirim_id',
        'isi',
        'dibaca',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'dibaca' => 'boolean',
            'pengirim_id' => 'integer',
        ];
    }

    public function percakapan(): BelongsTo
    {
        return $this->belongsTo(Percakapan::class, 'percakapan_id');
    }

    public function userPengirim(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengirim_id');
    }
}
