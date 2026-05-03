<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Laporan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'laporan';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'periode',
        'bulan',
        'tahun',
        'status',
        'file_pdf',
        'isi_laporan',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'periode' => 'date',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<HasilKerja, $this>
     */
    public function hasilKerja(): HasMany
    {
        return $this->hasMany(HasilKerja::class);
    }

    /**
     * @return HasMany<PerilakuKerja, $this>
     */
    public function perilakuKerja(): HasMany
    {
        return $this->hasMany(PerilakuKerja::class);
    }
}
