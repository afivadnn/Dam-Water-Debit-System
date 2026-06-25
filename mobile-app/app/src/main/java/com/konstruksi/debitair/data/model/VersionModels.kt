package com.konstruksi.debitair.data.model
import com.google.gson.annotations.SerializedName

data class VersionResponse(
    @SerializedName("data") val data: VersionData
)

data class VersionData(
    @SerializedName("min_version_code") val minVersionCode: Int,
    @SerializedName("download_url") val downloadUrl: String
)