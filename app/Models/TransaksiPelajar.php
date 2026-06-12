<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string $NoPinjamP
 * @property Carbon $TglPinjam
 * @property Carbon $TglJatuhTempo
 * @property Carbon|null $TglKembali
 * @property string $NoAnggotaP
 * @property string $KodeBuku
 * @property string $KodePetugas
 * @property string|null $KodePetugasKembali
 * @property float $Denda
 * @property string $StatusBayarDenda
 * @property string $StatusTransaksi
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'NoPinjamP',
    'TglPinjam',
    'TglJatuhTempo',
    'TglKembali',
    'NoAnggotaP',
    'KodeBuku',
    'KodePetugas',
    'KodePetugasKembali',
    'Denda',
    'StatusBayarDenda',
    'StatusTransaksi',
])]
class TransaksiPelajar extends Model
{
    use HasFactory;

    protected $table = 'transaksi_pelajar';

    protected $primaryKey = 'NoPinjamP';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'TglPinjam' => 'date',
            'TglJatuhTempo' => 'date',
            'TglKembali' => 'date',
            'Denda' => 'decimal:2',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->NoPinjamP)) {
                $model->NoPinjamP = (string) Str::uuid();
            }
        });
    }

    /**
     * Relationship to AnggotaPelajar.
     *
     * @return BelongsTo<AnggotaPelajar, $this>
     */
    public function anggotaPelajar()
    {
        return $this->belongsTo(AnggotaPelajar::class, 'NoAnggotaP', 'NoAnggotaP');
    }

    /**
     * Relationship to Buku.
     *
     * @return BelongsTo<Buku, $this>
     */
    public function buku()
    {
        return $this->belongsTo(Buku::class, 'KodeBuku', 'KodeBuku');
    }

    /**
     * Relationship to Petugas (as issuing officer).
     *
     * @return BelongsTo<Petugas, $this>
     */
    public function petugas()
    {
        return $this->belongsTo(Petugas::class, 'KodePetugas', 'KodePetugas');
    }

    /**
     * Relationship to Petugas (as return officer).
     *
     * @return BelongsTo<Petugas, $this>
     */
    public function petugasKembali()
    {
        return $this->belongsTo(Petugas::class, 'KodePetugasKembali', 'KodePetugas');
    }
}
