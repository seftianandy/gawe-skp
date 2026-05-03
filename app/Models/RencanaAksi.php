<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RencanaAksi extends Model
{
    use HasFactory;

    protected $table = 'rencana_aksi';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'indikator_id',
        'hasil_kerja_id',
        'deskripsi',
    ];

    /**
     * @return BelongsTo<IndikatorKinerja, $this>
     */
    public function indikator(): BelongsTo
    {
        return $this->belongsTo(IndikatorKinerja::class, 'indikator_id');
    }

    /**
     * @return BelongsTo<HasilKerja, $this>
     */
    public function hasilKerja(): BelongsTo
    {
        return $this->belongsTo(HasilKerja::class, 'hasil_kerja_id');
    }
}
