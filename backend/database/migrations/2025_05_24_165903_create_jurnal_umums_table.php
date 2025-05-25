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
        Schema::create('jurnal_umums', function (Blueprint $table) {
            $table->id();
            $table->string('kode'); // Kode akun
            $table->string('nama_akun'); // Nama akun
            $table->decimal('debit', 15, 2)->default(0); // Debit
            $table->decimal('kredit', 15, 2)->default(0); // Kredit
            $table->date('tanggal'); // Tanggal transaksi
            $table->timestamps(); // created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jurnal_umums');
    }
};
