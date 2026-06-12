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
        Schema::create('transaksi_non_pelajar', function (Blueprint $table) {
            $table->uuid('NoPinjamN')->primary();
            $table->date('TglPinjam');
            $table->date('TglJatuhTempo');
            $table->date('TglKembali')->nullable();

            $table->string('NoAnggotaN');
            $table->string('KodeBuku');
            $table->string('KodePetugas');
            $table->string('KodePetugasKembali')->nullable();

            $table->decimal('Denda', 10, 2)->default(0.00);
            $table->string('StatusBayarDenda');
            $table->string('StatusTransaksi');
            $table->timestamps();

            // Foreign keys
            $table->foreign('NoAnggotaN')
                ->references('NoAnggotaN')
                ->on('anggota_non_pelajar')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('KodeBuku')
                ->references('KodeBuku')
                ->on('buku')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('KodePetugas')
                ->references('KodePetugas')
                ->on('petugas')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('KodePetugasKembali')
                ->references('KodePetugas')
                ->on('petugas')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            // Optimization indexes
            $table->index('TglPinjam');
            $table->index('TglJatuhTempo');
            $table->index('StatusTransaksi');
            $table->index('StatusBayarDenda');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_non_pelajar');
    }
};
