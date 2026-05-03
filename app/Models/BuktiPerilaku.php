<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuktiPerilaku extends Model
{
    use HasFactory;

    protected $table = 'bukti_perilaku';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'perilaku_id',
        'file_path',
    ];

    /**
     * @return BelongsTo<PerilakuKerja, $this>
     */
    public function perilaku(): BelongsTo
    {
        return $this->belongsTo(PerilakuKerja::class, 'perilaku_id');
    }
}
