<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IndikatorKinerja extends Model
{
    use HasFactory;

    protected $table = 'indikator_kinerja';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'hasil_kerja_id',
        'deskripsi',
        'satuan',
        'target',
        'kategori',
    ];

    /**
     * @return BelongsTo<HasilKerja, $this>
     */
    public function hasilKerja(): BelongsTo
    {
        return $this->belongsTo(HasilKerja::class);
    }

    /**
     * @return HasMany<RencanaAksi, $this>
     */
    public function rencanaAksi(): HasMany
    {
        return $this->hasMany(RencanaAksi::class, 'indikator_id');
    }

    /**
     * @return HasMany<Realisasi, $this>
     */
    public function realisasi(): HasMany
    {
        return $this->hasMany(Realisasi::class, 'indikator_id');
    }
}
