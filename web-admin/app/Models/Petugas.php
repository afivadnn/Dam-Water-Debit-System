<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens; // PENTING UNTUK ANDROID LOGIN

class Petugas extends Model
{
    use SoftDeletes, HasApiTokens; // Tambahkan HasApiTokens

    protected $table = 'tabel_petugas';

    protected $fillable = [
        'nik', 'nama_lengkap', 'email', 'nomor_telepon', 
        'id_bendungan', 'password', 'foto_profil', 'status_aktif'
    ];

    protected $hidden = [
        'password', 
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
    ];
    
    // Relasi ke Bendungan
    public function bendungan() {
        return $this->belongsTo(Bendungan::class, 'id_bendungan');
    }
}