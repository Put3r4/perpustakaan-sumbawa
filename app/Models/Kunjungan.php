<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $IdKunjungan
 * @property string $TipePengunjung
 * @property string|null $IdentitasID
 * @property string $NamaPengunjung
 * @property Carbon $WaktuMasuk
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'TipePengunjung',
    'IdentitasID',
    'NamaPengunjung',
    'WaktuMasuk',
])]
class Kunjungan extends Model
{
    use HasFactory;

    protected $table = 'kunjungan';

    protected $primaryKey = 'IdKunjungan';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'WaktuMasuk' => 'datetime',
        ];
    }
}
