<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Realisasi extends Model
{
    use HasFactory;

    protected $table = 'realisasi';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'indikator_id',
        'tanggal',
        'output',
        'keterangan',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    /**
     * @return BelongsTo<IndikatorKinerja, $this>
     */
    public function indikator(): BelongsTo
    {
        return $this->belongsTo(IndikatorKinerja::class, 'indikator_id');
    }

    /**
     * @return HasMany<BuktiFoto, $this>
     */
    public function buktiFoto(): HasMany
    {
        return $this->hasMany(BuktiFoto::class);
    }
}
