<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Percakapan extends Model
{
    use HasFactory;

    protected $table = 'percakapan';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'staf_id',
        'tipe',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function staf(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staf_id');
    }

    public function pesan(): HasMany
    {
        return $this->hasMany(Pesan::class, 'percakapan_id');
    }

    public function pesanTerakhir(): HasOne
    {
        return $this->hasOne(Pesan::class, 'percakapan_id')->latestOfMany();
    }
}
