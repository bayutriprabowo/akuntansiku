<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Akun extends Model
{
    use HasFactory;

    protected $table = 'akuns'; // Nama tabel (opsional, defaultnya 'akuns')

    protected $fillable = [
        'kode',
        'nama_akun',
        'tipe_debit_kredit',
        'tipe_klasifikasi',
        'kode_akun_kontra',
    ];

    // Jika Anda menggunakan timestamps (created_at, updated_at)
    public $timestamps = true;
}
