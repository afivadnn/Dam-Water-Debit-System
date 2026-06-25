package com.konstruksi.debitair.data.model

import com.google.gson.annotations.SerializedName

// Yang kita kirim ke server
data class PencatatanRequest(
    @SerializedName("tanggal") val tanggal: String,       // Format: 2025-12-05
    @SerializedName("waktu_input") val waktuInput: String, // pagi, siang, atau sore
    @SerializedName("tinggi_air") val tinggiAir: Double,
    @SerializedName("jam_aktual") val jamAktual: String   // Format: 2025-12-05 07:30:00
)

// Yang server balas (Hasil Debit)
data class PencatatanResponse(
    @SerializedName("status") val status: String,
    @SerializedName("message") val message: String,
    @SerializedName("hasil_debit") val debit: Double
)