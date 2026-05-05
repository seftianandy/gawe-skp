<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LampiranFile extends Model
{
    use HasFactory;

    protected $table = 'lampiran_files';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'hasil_kerja_id',
        'perilaku_kerja_id',
        'nama_file',
        'file_path',
    ];

    /**
     * @return BelongsTo<HasilKerja, $this>
     */
    public function hasilKerja(): BelongsTo
    {
        return $this->belongsTo(HasilKerja::class);
    }

    /**
     * @return BelongsTo<PerilakuKerja, $this>
     */
    public function perilakuKerja(): BelongsTo
    {
        return $this->belongsTo(PerilakuKerja::class);
    }
}
