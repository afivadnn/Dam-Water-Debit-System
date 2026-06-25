package com.konstruksi.debitair

import android.content.Intent
import android.os.Bundle
import android.widget.Button
import android.widget.EditText
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import com.konstruksi.debitair.data.api.ApiClient
import com.konstruksi.debitair.data.model.LoginRequest
import com.konstruksi.debitair.data.model.LoginResponse
import com.konstruksi.debitair.utils.SessionManager
import retrofit2.Call
import retrofit2.Callback
import com.konstruksi.debitair.BuildConfig
import retrofit2.Response
import com.konstruksi.debitair.data.model.VersionResponse // Import Model
import android.app.AlertDialog

class MainActivity : AppCompatActivity() {

    private lateinit var sessionManager: SessionManager

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)

        setContentView(R.layout.activity_login)

        sessionManager = SessionManager(this)

        // Cek apakah user sudah login sebelumnya
        if (sessionManager.getToken() != null) {
            goToDashboard()
        }

        checkAppVersion()

        val etNik = findViewById<EditText>(R.id.etNik)
        val etPassword = findViewById<EditText>(R.id.etPassword)
        val btnLogin = findViewById<Button>(R.id.btnLogin)

        btnLogin.setOnClickListener {
            val nik = etNik.text.toString().trim()
            val pass = etPassword.text.toString().trim()

            if (nik.isNotEmpty() && pass.isNotEmpty()) {
                doLogin(nik, pass)
            } else {
                Toast.makeText(this, "Isi NIK dan Password!", Toast.LENGTH_SHORT).show()
            }
        }
    }

    private fun doLogin(nik: String, pass: String) {
        // Panggil API Login
        ApiClient.instance.login(LoginRequest(nik, pass))
            .enqueue(object : Callback<LoginResponse> {
                override fun onResponse(call: Call<LoginResponse>, response: Response<LoginResponse>) {
                    if (response.isSuccessful) {
                        val body = response.body()
                        if (body != null) {

                            sessionManager.saveSession(body.token, body.user.id, body.user.nama)

                            Toast.makeText(applicationContext, "Login Berhasil!", Toast.LENGTH_SHORT).show()
                            goToDashboard()
                        }else {
                            Toast.makeText(applicationContext, "Token tidak valid", Toast.LENGTH_SHORT).show()
                        }
                    } else {
                        Toast.makeText(applicationContext, "Login Gagal. Cek NIK/Password", Toast.LENGTH_SHORT).show()
                    }
                }

                override fun onFailure(call: Call<LoginResponse>, t: Throwable) {
                    Toast.makeText(applicationContext, "Error Koneksi: ${t.message}", Toast.LENGTH_LONG).show()
                }
            })
    }

    private fun checkAppVersion() {
        ApiClient.instance.checkVersion().enqueue(object : Callback<VersionResponse> {
            override fun onResponse(call: Call<VersionResponse>, response: Response<VersionResponse>) {
                if (response.isSuccessful) {
                    val serverData = response.body()?.data
                    if (serverData != null) {
                        // AMBIL VERSI APLIKASI SAAT INI
                        val currentVersionCode = BuildConfig.VERSION_CODE
                        val minVersionRequired = serverData.minVersionCode

                        // LOGIKA KILL SWITCH
                        if (currentVersionCode < minVersionRequired) {
                            showForceUpdateDialog()
                        }
                    }
                }
            }

            override fun onFailure(call: Call<VersionResponse>, t: Throwable) {
                // Opsional: Jika gagal cek versi (internet mati),
                // mau dibiarkan lewat atau diblokir?
                // Biasanya dibiarkan lewat (Soft Fail).
            }
        })
    }

    private fun showForceUpdateDialog() {
        val dialog = AlertDialog.Builder(this)
            .setTitle("Pembaruan Diperlukan")
            .setMessage("Versi aplikasi Anda sudah usang. Mohon update ke versi terbaru untuk melanjutkan.")
            .setCancelable(false) // PENTING: Dialog tidak bisa ditutup/di-back
            .setPositiveButton("Tutup Aplikasi") { _, _ ->
                finishAffinity() // Matikan aplikasi total
            }
            // Opsional: Tambah tombol "Download" jika punya link APK
            // .setNeutralButton("Download") { _, _ -> bukaLinkDownload() }
            .create()

        dialog.show()
    }


    private fun goToDashboard() {
        val intent = Intent(this, DashboardActivity::class.java)
        startActivity(intent)
        finish()
    }
}