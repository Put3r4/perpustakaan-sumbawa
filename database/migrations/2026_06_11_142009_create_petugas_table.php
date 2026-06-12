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
        Schema::create('petugas', function (Blueprint $table) {
            $table->string('KodePetugas')->primary();
            $table->string('NamaPetugas');
            $table->string('Jabatan');
            $table->string('HakAkses');
            $table->string('Password');
            $table->string('Email')->unique();
            $table->text('two_factor_secret')->nullable();
            $table->rememberToken();
            $table->timestamps();

            // Optimization indexes to prevent slow queries
            $table->index('Jabatan');
            $table->index('HakAkses');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('petugas');
    }
};
