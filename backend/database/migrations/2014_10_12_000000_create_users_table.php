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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Dari newEntry.name dan entry.name
            $table->string('username')->unique(); // Dari newEntry.username dan entry.username, dibuat unik
            $table->string('email')->unique(); // Dari newEntry.email dan entry.email
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password'); // Dari newEntry.password
            $table->string('role'); // Dari newEntry.role dan entry.role (misal: 'user', 'admin', 'direktur')
            $table->string('status')->default('aktif'); // Dari newEntry.status dan entry.status (misal: 'aktif', 'nonaktif')
            $table->rememberToken()->nullable(); // Token untuk "remember me" pada login
            $table->nullableTimestamps(); // Tanggal pembuatan dan pembaruan data
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
