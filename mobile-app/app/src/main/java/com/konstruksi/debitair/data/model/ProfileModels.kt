package com.konstruksi.debitair.data.model

import com.google.gson.annotations.SerializedName

data class ProfileResponse(
    @SerializedName("status") val status: String,
    @SerializedName("data") val data: ProfileData
)

data class ProfileData(
    @SerializedName("petugas") val petugas: PetugasData,
    @SerializedName("bendungan") val bendungan: BendunganData,
    @SerializedName("status_harian") val statusHarian: StatusHarian // <-- BARU
)

data class StatusHarian(
    @SerializedName("pagi") val isPagiDone: Boolean,
    @SerializedName("siang") val isSiangDone: Boolean,
    @SerializedName("sore") val isSoreDone: Boolean
)

data class PetugasData(
    @SerializedName("nama_lengkap") val nama: String,
    @SerializedName("nik") val nik: String
)

data class BendunganData(
    @SerializedName("id") val id: Int,
    @SerializedName("nama_bendungan") val namaBendungan: String,
    @SerializedName("lokasi") val lokasi: String,
    @SerializedName("rumus_debit") val rumus: String
)