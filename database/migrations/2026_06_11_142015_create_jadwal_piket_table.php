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
        Schema::create('jadwal_piket', function (Blueprint $table) {
            $table->id('IdPiket');
            $table->string('KodePetugas');
            $table->string('HariPiket');
            $table->timestamps();

            // Foreign keys
            $table->foreign('KodePetugas')
                ->references('KodePetugas')
                ->on('petugas')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Optimization indexes
            $table->index('HariPiket');
            $table->index('KodePetugas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_piket');
    }
};
