package com.konstruksi.debitair.data.model

import com.google.gson.annotations.SerializedName

data class LoginRequest(
    @SerializedName("nik") val nik: String,
    @SerializedName("password") val kataSandi: String
)

data class LoginResponse(
    @SerializedName("message") val message: String,
    @SerializedName("token") val token: String,
    @SerializedName("user") val user: UserData
)

data class UserData(
    @SerializedName("id") val id: Int,
    @SerializedName("nama") val nama: String,
    @SerializedName("id_bendungan") val idBendungan: Int
)