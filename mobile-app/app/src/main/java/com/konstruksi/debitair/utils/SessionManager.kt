package com.konstruksi.debitair.utils

import android.content.Context
import android.content.SharedPreferences
import androidx.core.content.edit

class SessionManager(context: Context) {
    private val prefs: SharedPreferences = context.getSharedPreferences("user_session", Context.MODE_PRIVATE)

    // Simpan Token dengan kunci "auth_token"
    fun saveSession(token: String, userId: Int, nama: String) {
        prefs.edit {
            putString("auth_token", token) // KUNCI PENYIMPANAN
            putInt("user_id", userId)
            putString("user_nama", nama)
        }
    }

    // Ambil Token dengan kunci "auth_token" (HARUS SAMA)
    fun getToken(): String? {
        return prefs.getString("auth_token", null)
    }

    fun getUserId(): Int {
        return prefs.getInt("user_id", 0)
    }

    fun getUserName(): String? {
        return prefs.getString("user_nama", null)
    }

    fun saveServerStatus(pagi: Boolean, siang: Boolean, sore: Boolean) {
        prefs.edit {
            putBoolean("status_server_pagi", pagi)
            putBoolean("status_server_siang", siang)
            putBoolean("status_server_sore", sore)
        }
    }

    fun getServerStatus(): Triple<Boolean, Boolean, Boolean> {
        return Triple(
            prefs.getBoolean("status_server_pagi", false),
            prefs.getBoolean("status_server_siang", false),
            prefs.getBoolean("status_server_sore", false)
        )
    }

    fun clearSession() {
        prefs.edit { clear() }
    }
}