<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bendungan extends Model
{
    use SoftDeletes;

    protected $table = 'tabel_bendungan'; // Mapping ke tabel SQL

    protected $fillable = [
        'kode_bendungan',
        'nama_bendungan',
        'lokasi',
        'koordinat_latitude',
        'koordinat_longitude',
        'rumus_debit',
        'parameter_rumus',
        'status_aktif',
    ];

    protected $casts = [
        'parameter_rumus' => 'array', // Otomatis convert JSON ke Array
        'status_aktif' => 'boolean',
        'koordinat_latitude' => 'decimal:7',
        'koordinat_longitude' => 'decimal:7',
    ];
}