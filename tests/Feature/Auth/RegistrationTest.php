<?php

use App\Models\AnggotaNonPelajar;
use App\Models\AnggotaPelajar;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
    $response->assertViewIs('auth.register');
});

test('pelajar can register with valid data', function () {
    $response = $this->post(route('register'), [
        'kategori_pendaftaran' => 'pelajar',
        'nisn' => '1234567890',
        'nama' => 'Test Pelajar',
        'nama_sekolah' => 'SMA Test',
        'kelas' => 'XII IPA 1',
        'tempat_lahir' => 'Jakarta',
        'tanggal_lahir' => '2005-01-15',
        'alamat' => 'Jl. Test No. 123',
        'kode_pos' => '12345',
        'nomor_telepon' => '08123456789',
        'nama_orang_tua' => 'Orang Tua Test',
        'alamat_orang_tua' => 'Jl. Ortu No. 456',
        'nomor_telepon_orang_tua' => '08198765432',
        'email' => 'pelajar@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    if ($response->isRedirect()) {
        $this->assertAuthenticated();
        $response->assertRedirect('/rak-buku');
        $response->assertSessionHas('registrasi_sukses', true);

        $this->assertDatabaseHas('anggota_pelajar', [
            'NIM_NIS' => '1234567890',
            'NamaAnggotaP' => 'Test Pelajar',
            'AsalSekolah' => 'SMA Test',
            'Email' => 'pelajar@example.com',
        ]);
    } else {
        // Debug validation errors
        dump($response->json());
        $this->fail('Registration failed with validation errors');
    }
});

test('non pelajar can register with valid data', function () {
    $response = $this->post(route('register'), [
        'kategori_pendaftaran' => 'non_pelajar',
        'nik' => '3201234567890123',
        'nama' => 'Test Non Pelajar',
        'pekerjaan' => 'Karyawan Swasta',
        'tempat_lahir' => 'Bandung',
        'tanggal_lahir' => '1990-05-20',
        'alamat_instansi' => 'Jl. Kantor No. 789',
        'kode_pos' => '54321',
        'nomor_telepon' => '08567891234',
        'email' => 'nonpelajar@example.com',
        'password' => 'password456',
        'password_confirmation' => 'password456',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect('/rak-buku');
    $response->assertSessionHas('registrasi_sukses', true);

    $this->assertDatabaseHas('anggota_non_pelajar', [
        'NIK' => '3201234567890123',
        'NamaAnggotaN' => 'Test Non Pelajar',
        'Pekerjaan' => 'Karyawan Swasta',
        'Email' => 'nonpelajar@example.com',
    ]);
});

test('registration fails with invalid pelajar data', function () {
    $response = $this->post(route('register'), [
        'kategori_pendaftaran' => 'pelajar',
        'nama' => 'Test Incomplete',
        'email' => 'invalid-email',
        'password' => 'pass',
        'password_confirmation' => 'different',
    ]);

    $response->assertSessionHasErrors();
    $this->assertGuest();
});

test('registration blocks admin parameters', function () {
    $response = $this->post(route('register'), [
        'kategori_pendaftaran' => 'pelajar',
        'HakAkses' => 'admin',
        'KodePetugas' => 'ADMIN001',
        'nisn' => '1234567890',
        'nama' => 'Hacker Test',
        'email' => 'hacker@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertForbidden();
    $this->assertGuest();
});

test('member number is generated correctly for pelajar', function () {
    // First pelajar
    $this->post(route('register'), [
        'kategori_pendaftaran' => 'pelajar',
        'nisn' => '1111111111',
        'nama' => 'First Pelajar',
        'nama_sekolah' => 'SMA 1',
        'tempat_lahir' => 'Jakarta',
        'tanggal_lahir' => '2005-01-01',
        'alamat' => 'Jl. Test 1',
        'kode_pos' => '12345',
        'nomor_telepon' => '081111111111',
        'nama_orang_tua' => 'Ortu 1',
        'alamat_orang_tua' => 'Jl. Ortu 1',
        'nomor_telepon_orang_tua' => '081111111112',
        'email' => 'first@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $member = AnggotaPelajar::where('Email', 'first@example.com')->first();
    expect($member->NoAnggotaP)->toMatch('/^AP\d{3}$/');
});

test('member number is generated correctly for non pelajar', function () {
    $this->post(route('register'), [
        'kategori_pendaftaran' => 'non_pelajar',
        'nik' => '3201111111111111',
        'nama' => 'First Non Pelajar',
        'pekerjaan' => 'Pegawai',
        'tempat_lahir' => 'Bandung',
        'tanggal_lahir' => '1990-01-01',
        'alamat_instansi' => 'Jl. Test 1',
        'kode_pos' => '54321',
        'nomor_telepon' => '082222222222',
        'email' => 'firstnon@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $member = AnggotaNonPelajar::where('Email', 'firstnon@example.com')->first();
    expect($member->NoAnggotaN)->toMatch('/^AN\d{3}$/');
});
