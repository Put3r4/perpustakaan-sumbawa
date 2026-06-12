<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $IdPiket
 * @property string $KodePetugas
 * @property string $HariPiket
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'KodePetugas',
    'HariPiket',
])]
class JadwalPiket extends Model
{
    use HasFactory;

    protected $table = 'jadwal_piket';

    protected $primaryKey = 'IdPiket';

    /**
     * Relationship to Petugas.
     *
     * @return BelongsTo<Petugas, $this>
     */
    public function petugas()
    {
        return $this->belongsTo(Petugas::class, 'KodePetugas', 'KodePetugas');
    }
}
