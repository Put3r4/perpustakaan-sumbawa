<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('anggota_non_pelajar', function (Blueprint $table) {
            $table->string('NoAnggotaN')->primary();
            $table->string('NIK');
            $table->string('NamaAnggotaN');
            $table->string('Pekerjaan');
            $table->string('TTL');
            $table->text('Alamat');
            $table->string('KodePos');
            $table->string('NoTelp1');
            $table->string('NoTelp2')->nullable();
            $table->date('TglDaftar');
            $table->string('Email')->unique();
            $table->string('Password');
            $table->text('two_factor_secret')->nullable();
            $table->rememberToken();
            $table->timestamps();

            // Optimization indexes to prevent slow queries
            $table->index('NIK');
            $table->index('TglDaftar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anggota_non_pelajar');
    }
};
