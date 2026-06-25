<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pencatatan extends Model
{
    protected $table = 'tabel_pencatatan_tinggi_air';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_pencatatan' => 'date',
        'waktu_input_pagi' => 'datetime',
        'waktu_input_siang' => 'datetime',
        'waktu_input_sore' => 'datetime',
    ];

    // Relasi
    public function bendungan()
    {
        return $this->belongsTo(Bendungan::class, 'id_bendungan');
    }

    public function petugas()
    {
        return $this->belongsTo(Petugas::class, 'id_petugas');
    }
}