<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerhitunganDebit extends Model
{
    protected $table = 'tabel_perhitungan_debit'; // Nama tabel custom kamu
    protected $guarded = ['id'];

    // 1. Relasi ke Bendungan (PENTING: Ini yang menyebabkan error)
    public function bendungan(): BelongsTo
    {
        return $this->belongsTo(Bendungan::class, 'id_bendungan');
    }

    // 2. Relasi ke Pencatatan Induk (Opsional tapi berguna)
    public function pencatatan(): BelongsTo
    {
        return $this->belongsTo(Pencatatan::class, 'id_pencatatan');
    }
}