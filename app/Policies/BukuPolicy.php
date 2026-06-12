<?php

namespace App\Policies;

use App\Models\AnggotaNonPelajar;
use App\Models\AnggotaPelajar;
use App\Models\Buku;
use App\Models\Petugas;

class BukuPolicy
{
    /**
     * Determine whether anyone can view any books (public access).
     * Allowed for: Anonymous, Pelajar, Non-Pelajar, Petugas.
     */
    public function viewAny(AnggotaPelajar|AnggotaNonPelajar|Petugas|null $user): bool
    {
        // Allow all users including guests to view book list
        return true;
    }

    /**
     * Determine whether anyone can view a single book detail (public access).
     * Allowed for: Anonymous, Pelajar, Non-Pelajar, Petugas.
     */
    public function view(AnggotaPelajar|AnggotaNonPelajar|Petugas|null $user, Buku $buku): bool
    {
        // Allow all users including guests to view book details
        return true;
    }

    /**
     * Determine whether the user can create books.
     * Only SuperAdmin and Petugas from petugas guard.
     */
    public function create(AnggotaPelajar|AnggotaNonPelajar|Petugas|null $user): bool
    {
        return $user instanceof Petugas && in_array($user->HakAkses, ['SuperAdmin', 'Petugas']);
    }

    /**
     * Determine whether the user can update the book.
     * Only SuperAdmin and Petugas from petugas guard.
     */
    public function update(AnggotaPelajar|AnggotaNonPelajar|Petugas|null $user, Buku $buku): bool
    {
        return $user instanceof Petugas && in_array($user->HakAkses, ['SuperAdmin', 'Petugas']);
    }

    /**
     * Determine whether the user can delete the book.
     * Only SuperAdmin and Petugas from petugas guard.
     */
    public function delete(AnggotaPelajar|AnggotaNonPelajar|Petugas|null $user, Buku $buku): bool
    {
        return $user instanceof Petugas && in_array($user->HakAkses, ['SuperAdmin', 'Petugas']);
    }

    /**
     * Determine whether the user can process book circulation (borrow/return).
     * Only SuperAdmin and Petugas from petugas guard.
     */
    public function processSirkulasi(AnggotaPelajar|AnggotaNonPelajar|Petugas|null $user): bool
    {
        return $user instanceof Petugas && in_array($user->HakAkses, ['SuperAdmin', 'Petugas']);
    }

    /**
     * Determine whether the user can restore the book.
     * Only SuperAdmin from petugas guard.
     */
    public function restore(AnggotaPelajar|AnggotaNonPelajar|Petugas|null $user, Buku $buku): bool
    {
        return $user instanceof Petugas && $user->HakAkses === 'SuperAdmin';
    }

    /**
     * Determine whether the user can permanently delete the book.
     * Only SuperAdmin from petugas guard.
     */
    public function forceDelete(AnggotaPelajar|AnggotaNonPelajar|Petugas|null $user, Buku $buku): bool
    {
        return $user instanceof Petugas && $user->HakAkses === 'SuperAdmin';
    }
}
