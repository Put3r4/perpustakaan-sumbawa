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
 * @property string $NoAnggotaP
 * @property string $NIM_NIS
 * @property string $NamaAnggotaP
 * @property string $AsalSekolah
 * @property string $TTL
 * @property string $Alamat
 * @property string $KodePos
 * @property string $NoTelp1
 * @property string|null $NoTelp2
 * @property Carbon $TglDaftar
 * @property string $NamaOrtu
 * @property string $AlamatOrtu
 * @property string $NoTelpOrtu
 * @property string $Email
 * @property string $Password
 * @property string|null $two_factor_secret
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'NoAnggotaP',
    'NIM_NIS',
    'NamaAnggotaP',
    'AsalSekolah',
    'TTL',
    'Alamat',
    'KodePos',
    'NoTelp1',
    'NoTelp2',
    'TglDaftar',
    'NamaOrtu',
    'AlamatOrtu',
    'NoTelpOrtu',
    'Email',
    'Password',
])]
#[Hidden(['Password', 'two_factor_secret', 'remember_token'])]
class AnggotaPelajar extends Authenticatable
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    protected $table = 'anggota_pelajar';

    protected $primaryKey = 'NoAnggotaP';

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
            'TglDaftar' => 'date',
            'Password' => 'hashed',
        ];
    }

    /**
     * Relationship to TransaksiPelajar.
     *
     * @return HasMany<TransaksiPelajar, $this>
     */
    public function transaksiPelajar()
    {
        return $this->hasMany(TransaksiPelajar::class, 'NoAnggotaP', 'NoAnggotaP');
    }
}
