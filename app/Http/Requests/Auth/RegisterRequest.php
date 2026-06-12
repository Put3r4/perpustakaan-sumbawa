<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Explicitly block any parameter that targets officer privileges or tables
        $petugasFields = ['HakAkses', 'KodePetugas', 'Jabatan', 'NamaPetugas'];
        foreach ($petugasFields as $field) {
            if ($this->has($field)) {
                return false; // Forbidden
            }
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'kategori_pendaftaran' => ['required', 'string', 'in:pelajar,non_pelajar'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:anggota_pelajar,Email',
                'unique:anggota_non_pelajar,Email',
                'unique:petugas,Email',
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'nama' => ['required', 'string', 'max:255'],
            'tempat_lahir' => ['required', 'string', 'max:255'],
            'tanggal_lahir' => ['required', 'date'],
            'kode_pos' => ['required', 'string', 'max:10'],
            'nomor_telepon' => ['required', 'string', 'max:20'],
            'nomor_telepon_2' => ['nullable', 'string', 'max:20'],

            // Conditional rules for Pelajar
            'nisn' => ['required_if:kategori_pendaftaran,pelajar', 'nullable', 'string', 'max:50'],
            'nama_sekolah' => ['required_if:kategori_pendaftaran,pelajar', 'nullable', 'string', 'max:255'],
            'kelas' => ['nullable', 'string', 'max:50'],
            'alamat' => ['required_if:kategori_pendaftaran,pelajar', 'nullable', 'string'],
            'nama_orang_tua' => ['required_if:kategori_pendaftaran,pelajar', 'nullable', 'string', 'max:255'],
            'alamat_orang_tua' => ['required_if:kategori_pendaftaran,pelajar', 'nullable', 'string'],
            'nomor_telepon_orang_tua' => ['required_if:kategori_pendaftaran,pelajar', 'nullable', 'string', 'max:20'],

            // Conditional rules for Non-Pelajar
            'nik' => ['required_if:kategori_pendaftaran,non_pelajar', 'nullable', 'string', 'max:50'],
            'pekerjaan' => ['required_if:kategori_pendaftaran,non_pelajar', 'nullable', 'string', 'max:255'],
            'alamat_instansi' => ['required_if:kategori_pendaftaran,non_pelajar', 'nullable', 'string'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'nisn.required_if' => 'Nomor NISN/NIM wajib diisi jika mendaftar sebagai pelajar.',
            'nama_sekolah.required_if' => 'Nama sekolah asal wajib diisi jika mendaftar sebagai pelajar.',
            'alamat.required_if' => 'Alamat wajib diisi.',
            'nama_orang_tua.required_if' => 'Nama orang tua kandung wajib diisi jika mendaftar sebagai pelajar.',
            'alamat_orang_tua.required_if' => 'Alamat tinggal orang tua wajib diisi jika mendaftar sebagai pelajar.',
            'nomor_telepon_orang_tua.required_if' => 'Nomor kontak/telepon orang tua wajib diisi jika mendaftar sebagai pelajar.',
            'nik.required_if' => 'Nomor NIK KTP wajib diisi jika mendaftar sebagai non-pelajar.',
            'pekerjaan.required_if' => 'Pekerjaan saat ini wajib diisi jika mendaftar sebagai non-pelajar.',
            'alamat_instansi.required_if' => 'Alamat instansi/tempat bekerja wajib diisi jika mendaftar sebagai non-pelajar.',
        ];
    }
}
