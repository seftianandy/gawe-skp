<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HasilKerja extends Model
{
    use HasFactory;

    protected $table = 'hasil_kerja';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'laporan_id',
        'indikator_kinerja_master_id',
        'isi_ai',
    ];

    /**
     * @return BelongsTo<Laporan, $this>
     */
    public function laporan(): BelongsTo
    {
        return $this->belongsTo(Laporan::class);
    }

    /**
     * @return BelongsTo<IndikatorKinerjaMaster, $this>
     */
    public function indikatorKinerjaMaster(): BelongsTo
    {
        return $this->belongsTo(IndikatorKinerjaMaster::class);
    }

    /**
     * @return HasMany<IndikatorKinerja, $this>
     */
    public function indikatorKinerja(): HasMany
    {
        return $this->hasMany(IndikatorKinerja::class);
    }

    /**
     * @return HasMany<RencanaAksi, $this>
     */
    public function rencanaAksiHasilKerja(): HasMany
    {
        return $this->hasMany(RencanaAksi::class, 'hasil_kerja_id');
    }

    /**
     * @return HasMany<BuktiFoto, $this>
     */
    public function buktiFotoHasilKerja(): HasMany
    {
        return $this->hasMany(BuktiFoto::class, 'hasil_kerja_id');
    }

    /**
     * @return HasMany<LampiranFile, $this>
     */
    public function lampiranFiles(): HasMany
    {
        return $this->hasMany(LampiranFile::class);
    }
}
