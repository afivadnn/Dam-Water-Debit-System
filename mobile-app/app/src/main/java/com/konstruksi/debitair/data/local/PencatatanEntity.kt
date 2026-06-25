package com.konstruksi.debitair.data.local

import androidx.room.Entity
import androidx.room.PrimaryKey

@Entity(tableName = "tabel_pencatatan_lokal")
data class PencatatanEntity(
    @PrimaryKey(autoGenerate = true) val id: Int = 0,
    val tanggal: String,
    val waktuInput: String, // pagi, siang, sore
    val tinggiAir: Double,
    val jamAktual: String,
    val isSynced: Boolean = false,
    val idPetugas: Int,
    val idBendungan: Int
)