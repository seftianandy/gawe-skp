<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuktiFoto extends Model
{
    use HasFactory;

    protected $table = 'bukti_foto';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'realisasi_id',
        'hasil_kerja_id',
        'file_path',
    ];

    /**
     * @return BelongsTo<Realisasi, $this>
     */
    public function realisasi(): BelongsTo
    {
        return $this->belongsTo(Realisasi::class);
    }

    /**
     * @return BelongsTo<HasilKerja, $this>
     */
    public function hasilKerja(): BelongsTo
    {
        return $this->belongsTo(HasilKerja::class, 'hasil_kerja_id');
    }
}
