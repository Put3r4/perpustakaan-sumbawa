<?php

namespace App\Http\Requests\Admin;

use App\Models\AnggotaNonPelajar;
use App\Models\AnggotaPelajar;
use App\Models\Buku;
use App\Models\Petugas;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PeminjamanStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Only authenticated petugas can access this.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user() instanceof Petugas;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('buku_pilihan')) {
            $this->merge([
                'buku_pilihan' => array_values(array_filter($this->input('buku_pilihan'))),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Kategori anggota harus 'pelajar' atau 'non_pelajar'
            'kategori_anggota' => [
                'required',
                'string',
                Rule::in(['pelajar', 'non_pelajar']),
            ],

            // ID Anggota - conditional based on kategori
            'id_anggota' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $kategori = $this->input('kategori_anggota');

                    if ($kategori === 'pelajar') {
                        // Validate NoAnggotaP exists in anggota_pelajar table
                        if (! AnggotaPelajar::where('NoAnggotaP', $value)->exists()) {
                            $fail('ID Anggota Pelajar tidak valid atau tidak ditemukan.');
                        }
                    } elseif ($kategori === 'non_pelajar') {
                        // Validate NoAnggotaN exists in anggota_non_pelajar table
                        if (! AnggotaNonPelajar::where('NoAnggotaN', $value)->exists()) {
                            $fail('ID Anggota Non-Pelajar tidak valid atau tidak ditemukan.');
                        }
                    }
                },
            ],

            // Array buku pilihan - maksimal 2 buku
            'buku_pilihan' => [
                'required',
                'array',
                'min:1',
                'max:2',
            ],

            // Setiap KodeBuku dalam array harus valid dan tersedia
            'buku_pilihan.*' => [
                'required',
                'string',
                'distinct', // Tidak boleh duplikat dalam array
                function ($attribute, $value, $fail) {
                    // Check if book exists
                    $buku = Buku::find($value);

                    if (! $buku) {
                        $fail("Buku dengan kode {$value} tidak ditemukan.");

                        return;
                    }

                    // Check if book is available (stock > 0)
                    if ($buku->JumEksemplar <= 0) {
                        $fail("Buku \"{$buku->Judul}\" sedang tidak tersedia (stok habis).");
                    }
                },
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'kategori_anggota.required' => 'Kategori anggota wajib dipilih.',
            'kategori_anggota.in' => 'Kategori anggota harus berupa "pelajar" atau "non_pelajar".',
            'id_anggota.required' => 'ID Anggota wajib diisi.',
            'id_anggota.string' => 'ID Anggota harus berupa teks.',
            'buku_pilihan.required' => 'Minimal harus memilih 1 buku untuk dipinjam.',
            'buku_pilihan.array' => 'Data buku pilihan harus berupa array.',
            'buku_pilihan.min' => 'Minimal harus memilih 1 buku.',
            'buku_pilihan.max' => 'Maksimal hanya dapat meminjam 2 buku sekaligus.',
            'buku_pilihan.*.required' => 'Kode buku tidak boleh kosong.',
            'buku_pilihan.*.string' => 'Kode buku harus berupa teks.',
            'buku_pilihan.*.distinct' => 'Tidak boleh memilih buku yang sama lebih dari sekali.',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'kategori_anggota' => 'Kategori Anggota',
            'id_anggota' => 'ID Anggota',
            'buku_pilihan' => 'Buku Pilihan',
            'buku_pilihan.*' => 'Kode Buku',
        ];
    }
}
