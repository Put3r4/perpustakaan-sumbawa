<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property string $KodePetugas
 * @property string $NamaPetugas
 * @property string $Jabatan
 * @property string $HakAkses
 * @property string $Password
 * @property string $Email
 * @property string|null $two_factor_secret
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['KodePetugas', 'NamaPetugas', 'Jabatan', 'HakAkses', 'Password', 'Email'])]
#[Hidden(['Password', 'two_factor_secret', 'remember_token'])]
class Petugas extends Authenticatable
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    protected $table = 'petugas';

    protected $primaryKey = 'KodePetugas';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * Get the password for authentication.
     */
    public function getAuthPassword(): string
    {
        return $this->Password;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'Password' => 'hashed',
        ];
    }

    /**
     * Relationship to JadwalPiket.
     *
     * @return HasMany<JadwalPiket, $this>
     */
    public function jadwalPiket()
    {
        return $this->hasMany(JadwalPiket::class, 'KodePetugas', 'KodePetugas');
    }

    /**
     * Relationship to TransaksiPelajar (as issuer/petugas pinjam).
     *
     * @return HasMany<TransaksiPelajar, $this>
     */
    public function transaksiPelajar()
    {
        return $this->hasMany(TransaksiPelajar::class, 'KodePetugas', 'KodePetugas');
    }

    /**
     * Relationship to TransaksiPelajar (as return officer).
     *
     * @return HasMany<TransaksiPelajar, $this>
     */
    public function transaksiPelajarKembali()
    {
        return $this->hasMany(TransaksiPelajar::class, 'KodePetugasKembali', 'KodePetugas');
    }

    /**
     * Relationship to TransaksiNonPelajar (as issuer/petugas pinjam).
     *
     * @return HasMany<TransaksiNonPelajar, $this>
     */
    public function transaksiNonPelajar()
    {
        return $this->hasMany(TransaksiNonPelajar::class, 'KodePetugas', 'KodePetugas');
    }

    /**
     * Relationship to TransaksiNonPelajar (as return officer).
     *
     * @return HasMany<TransaksiNonPelajar, $this>
     */
    public function transaksiNonPelajarKembali()
    {
        return $this->hasMany(TransaksiNonPelajar::class, 'KodePetugasKembali', 'KodePetugas');
    }
}
