<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PerilakuKerja extends Model
{
    use HasFactory;

    protected $table = 'perilaku_kerja';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'laporan_id',
        'nama',
        'deskripsi',
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
     * @return HasMany<BuktiPerilaku, $this>
     */
    public function buktiPerilaku(): HasMany
    {
        return $this->hasMany(BuktiPerilaku::class, 'perilaku_id');
    }

    /**
     * @return HasMany<LampiranFile, $this>
     */
    public function lampiranFiles(): HasMany
    {
        return $this->hasMany(LampiranFile::class);
    }
}
