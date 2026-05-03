<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IndikatorKinerjaMaster extends Model
{
    use HasFactory;

    protected $table = 'indikator_kinerja_masters';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'nama_indikator',
        'satuan',
        'target',
        'kategori',
    ];

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
        return $this->hasMany(HasilKerja::class, 'indikator_kinerja_master_id');
    }
}
