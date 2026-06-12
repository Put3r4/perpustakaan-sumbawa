<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $KodeBuku
 * @property string $NoUdc
 * @property string $NoReg
 * @property string $Judul
 * @property string $Penerbit
 * @property string $Pengarang
 * @property int $TahunTerbit
 * @property string $KotaTerbit
 * @property string $Bahasa
 * @property string|null $Edisi
 * @property string|null $Deskripsi
 * @property string $Isbn
 * @property int $JumEksemplar
 * @property string $SubjekUtama
 * @property string|null $SubjekTambahan
 * @property int $views_count
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'KodeBuku',
    'NoUdc',
    'NoReg',
    'Judul',
    'Penerbit',
    'Pengarang',
    'TahunTerbit',
    'KotaTerbit',
    'Bahasa',
    'Edisi',
    'Deskripsi',
    'Isbn',
    'JumEksemplar',
    'SubjekUtama',
    'SubjekTambahan',
    'views_count',
])]
class Buku extends Model
{
    use HasFactory;

    protected $table = 'buku';

    protected $primaryKey = 'KodeBuku';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * Relationship to TransaksiPelajar.
     *
     * @return HasMany<TransaksiPelajar, $this>
     */
    public function transaksiPelajar()
    {
        return $this->hasMany(TransaksiPelajar::class, 'KodeBuku', 'KodeBuku');
    }

    /**
     * Relationship to TransaksiNonPelajar.
     *
     * @return HasMany<TransaksiNonPelajar, $this>
     */
    public function transaksiNonPelajar()
    {
        return $this->hasMany(TransaksiNonPelajar::class, 'KodeBuku', 'KodeBuku');
    }
}
