package com.konstruksi.debitair.data.api

import com.konstruksi.debitair.data.model.LoginRequest
import com.konstruksi.debitair.data.model.LoginResponse
import com.konstruksi.debitair.data.model.PencatatanRequest
import com.konstruksi.debitair.data.model.PencatatanResponse
import com.konstruksi.debitair.data.model.ProfileResponse
import com.konstruksi.debitair.data.model.VersionResponse
import retrofit2.Call
import retrofit2.http.Body
import retrofit2.http.GET
import retrofit2.http.Header
import retrofit2.http.POST

interface ApiService {
    @POST("login")
    fun login(@Body request: LoginRequest): Call<LoginResponse>

    @GET("version")
    fun checkVersion(): Call<VersionResponse>

    @GET("me")
    fun getProfile(
        @Header("Authorization") token: String
    ): Call<ProfileResponse>

    @POST("pencatatan")
    fun kirimPencatatan(
        @Header("Authorization") token: String,
        @Body request: PencatatanRequest
    ): Call<PencatatanResponse>
}