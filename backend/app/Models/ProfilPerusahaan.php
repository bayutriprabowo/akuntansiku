<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfilPerusahaan extends Model
{
    use HasFactory;

    protected $table = 'profil_perusahaan';

    protected $fillable = [
        'nama',
        'tanggal_berdiri',
        'telepon',
        'alamat',
        'email',
    ];
}
