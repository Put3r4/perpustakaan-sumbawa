<?php

namespace App\Auth;

use App\Models\AnggotaNonPelajar;
use App\Models\AnggotaPelajar;
use App\Models\Petugas;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use SensitiveParameter;

class MultiUserProvider implements UserProvider
{
    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed  $identifier
     */
    public function retrieveById($identifier): ?Authenticatable
    {
        // Check Petugas
        if ($user = Petugas::find($identifier)) {
            return $user;
        }

        // Check AnggotaPelajar
        if ($user = AnggotaPelajar::find($identifier)) {
            return $user;
        }

        // Check AnggotaNonPelajar
        if ($user = AnggotaNonPelajar::find($identifier)) {
            return $user;
        }

        // Fallback check standard User model
        if ($user = User::find($identifier)) {
            return $user;
        }

        return null;
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *
     * @param  mixed  $identifier
     * @param  string  $token
     */
    public function retrieveByToken($identifier, #[SensitiveParameter] $token): ?Authenticatable
    {
        if ($user = Petugas::where('KodePetugas', $identifier)->where('remember_token', $token)->first()) {
            return $user;
        }

        if ($user = AnggotaPelajar::where('NoAnggotaP', $identifier)->where('remember_token', $token)->first()) {
            return $user;
        }

        if ($user = AnggotaNonPelajar::where('NoAnggotaN', $identifier)->where('remember_token', $token)->first()) {
            return $user;
        }

        if ($user = User::where('id', $identifier)->where('remember_token', $token)->first()) {
            return $user;
        }

        return null;
    }

    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param  string  $token
     */
    public function updateRememberToken(Authenticatable $user, #[SensitiveParameter] $token): void
    {
        $user->setRememberToken($token);
        $user->save();
    }

    /**
     * Retrieve a user by the given credentials.
     */
    public function retrieveByCredentials(#[SensitiveParameter] array $credentials): ?Authenticatable
    {
        if (empty($credentials) || (count($credentials) === 1 && array_key_exists('password', $credentials))) {
            return null;
        }

        $email = $credentials['email'] ?? null;
        if (! $email) {
            return null;
        }

        // Check Petugas
        if ($user = Petugas::where('Email', $email)->first()) {
            return $user;
        }

        // Check AnggotaPelajar
        if ($user = AnggotaPelajar::where('Email', $email)->first()) {
            return $user;
        }

        // Check AnggotaNonPelajar
        if ($user = AnggotaNonPelajar::where('Email', $email)->first()) {
            return $user;
        }

        // Check standard User
        if ($user = User::where('email', $email)->first()) {
            return $user;
        }

        return null;
    }

    /**
     * Validate a user against the given credentials.
     */
    public function validateCredentials(Authenticatable $user, #[SensitiveParameter] array $credentials): bool
    {
        $password = $credentials['password'] ?? '';

        return app('hash')->check($password, $user->getAuthPassword());
    }

    /**
     * Rehash the user's password if required and supported.
     */
    public function rehashPasswordIfRequired(Authenticatable $user, #[SensitiveParameter] array $credentials, bool $force = false): void
    {
        $password = $credentials['password'] ?? '';
        if (app('hash')->needsRehash($user->getAuthPassword())) {
            $user->Password = app('hash')->make($password);
            $user->save();
        }
    }
}
