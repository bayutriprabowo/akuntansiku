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
        Schema::create('akuns', function (Blueprint $table) {
            $table->id(); // Kolom ID otomatis (auto-increment)
            $table->string('kode')->unique(); // Kode akun (unik)
            $table->string('nama_akun'); // Nama akun
            $table->enum('tipe_debit_kredit', ['debit', 'kredit']); // Tipe debit/kredit (enum)
            $table->string('tipe_klasifikasi'); // Tipe klasifikasi akun
            $table->string('kode_akun_kontra')->nullable(); // Kode akun kontra (nullable)
            $table->timestamps(); // created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('akuns');
    }
};
