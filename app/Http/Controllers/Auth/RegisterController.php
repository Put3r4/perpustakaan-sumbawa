<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\AnggotaNonPelajar;
use App\Models\AnggotaPelajar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RegisterController extends Controller
{
    /**
     * Display the registration form.
     */
    public function showRegistrationForm(): View
    {
        return view('auth.register');
    }

    /**
     * Handle registration request.
     */
    public function register(RegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $kategori = $validated['kategori_pendaftaran'];

        // Generate unique member number
        $memberNumber = $this->generateMemberNumber($kategori);

        // Hash password
        $password = bcrypt($validated['password']);

        if ($kategori === 'pelajar') {
            // Map request inputs to AnggotaPelajar columns
            $member = AnggotaPelajar::create([
                'NoAnggotaP' => $memberNumber,
                'NIM_NIS' => $validated['nisn'],
                'NamaAnggotaP' => $validated['nama'],
                'AsalSekolah' => $validated['nama_sekolah'],
                'TTL' => $validated['tempat_lahir'].', '.$validated['tanggal_lahir'],
                'Alamat' => $validated['alamat'],
                'KodePos' => $validated['kode_pos'],
                'NoTelp1' => $validated['nomor_telepon'],
                'NoTelp2' => $validated['nomor_telepon_2'] ?? null,
                'TglDaftar' => now(),
                'NamaOrtu' => $validated['nama_orang_tua'],
                'AlamatOrtu' => $validated['alamat_orang_tua'],
                'NoTelpOrtu' => $validated['nomor_telepon_orang_tua'],
                'Email' => $validated['email'],
                'Password' => $password,
            ]);
        } else {
            // Map request inputs to AnggotaNonPelajar columns
            $member = AnggotaNonPelajar::create([
                'NoAnggotaN' => $memberNumber,
                'NIK' => $validated['nik'],
                'NamaAnggotaN' => $validated['nama'],
                'Pekerjaan' => $validated['pekerjaan'],
                'TTL' => $validated['tempat_lahir'].', '.$validated['tanggal_lahir'],
                'Alamat' => $validated['alamat_instansi'],
                'KodePos' => $validated['kode_pos'],
                'NoTelp1' => $validated['nomor_telepon'],
                'NoTelp2' => $validated['nomor_telepon_2'] ?? null,
                'TglDaftar' => now(),
                'Email' => $validated['email'],
                'Password' => $password,
            ]);
        }

        // Login the user automatically
        Auth::login($member);

        // Set success flash message
        session()->flash('registrasi_sukses', true);

        // Redirect to bookshelf
        return redirect('/rak-buku');
    }

    /**
     * Generate unique formatted member number.
     */
    private function generateMemberNumber(string $kategori): string
    {
        $prefix = $kategori === 'pelajar' ? 'AP' : 'AN';
        $table = $kategori === 'pelajar' ? 'anggota_pelajar' : 'anggota_non_pelajar';
        $column = $kategori === 'pelajar' ? 'NoAnggotaP' : 'NoAnggotaN';

        // Get the latest number
        $lastMember = DB::table($table)
            ->where($column, 'like', $prefix.'%')
            ->orderBy($column, 'desc')
            ->first();

        if ($lastMember) {
            $lastNumber = (int) substr($lastMember->$column, 2);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix.str_pad((string) $newNumber, 3, '0', STR_PAD_LEFT);
    }
}
