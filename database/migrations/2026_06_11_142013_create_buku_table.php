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
        Schema::create('buku', function (Blueprint $table) {
            $table->string('KodeBuku')->primary();
            $table->string('NoUdc');
            $table->string('NoReg');
            $table->string('Judul');
            $table->string('Penerbit');
            $table->string('Pengarang');
            $table->integer('TahunTerbit');
            $table->string('KotaTerbit');
            $table->string('Bahasa');
            $table->string('Edisi')->nullable();
            $table->text('Deskripsi')->nullable();
            $table->string('Isbn');
            $table->integer('JumEksemplar');
            $table->string('SubjekUtama');
            $table->string('SubjekTambahan')->nullable();
            $table->integer('views_count')->default(0);
            $table->timestamps();

            // Optimization indexes to prevent slow queries on book searches/sorting
            $table->index('Judul');
            $table->index('Pengarang');
            $table->index('Penerbit');
            $table->index('views_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buku');
    }
};
