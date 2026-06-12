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
        Schema::create('kunjungan', function (Blueprint $table) {
            $table->id('IdKunjungan');
            $table->string('TipePengunjung');
            $table->string('IdentitasID')->nullable();
            $table->string('NamaPengunjung');
            $table->timestamp('WaktuMasuk')->useCurrent();
            $table->timestamps();

            // Optimization indexes
            $table->index('WaktuMasuk');
            $table->index('TipePengunjung');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kunjungan');
    }
};
