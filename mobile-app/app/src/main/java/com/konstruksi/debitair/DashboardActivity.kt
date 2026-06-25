package com.konstruksi.debitair

import android.Manifest
import android.app.AlertDialog
import android.content.Intent
import android.content.pm.PackageManager
import android.os.Build
import android.os.Bundle
import android.text.InputType
import android.widget.EditText
import android.widget.TextView
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.core.app.ActivityCompat
import androidx.core.content.ContextCompat
import androidx.work.Constraints
import androidx.work.NetworkType
import androidx.work.OneTimeWorkRequest
import androidx.work.WorkManager
import com.google.android.material.button.MaterialButton
import com.konstruksi.debitair.data.api.ApiClient
import com.konstruksi.debitair.data.local.AppDatabase
import com.konstruksi.debitair.data.local.PencatatanEntity
import com.konstruksi.debitair.data.model.ProfileResponse
import com.konstruksi.debitair.utils.SessionManager
import com.konstruksi.debitair.workers.UploadWorker
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response
import java.text.SimpleDateFormat
import java.util.Date
import java.util.Locale

class DashboardActivity : AppCompatActivity() {

    private var currentBendunganId: Int = 0
    private lateinit var sessionManager: SessionManager
    private lateinit var tvWelcome: TextView
    private lateinit var tvBendungan: TextView

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_dashboard)

        sessionManager = SessionManager(this)
        tvWelcome = findViewById(R.id.tvWelcome)
        tvBendungan = findViewById(R.id.tvBendungan)

        // Logout
        findViewById<MaterialButton>(R.id.btnLogout).setOnClickListener {
            sessionManager.clearSession()
            startActivity(Intent(this, MainActivity::class.java))
            finish()
        }

        // Load Data
        loadProfileData()

        // Listener Tombol Input
        findViewById<MaterialButton>(R.id.btnInputPagi).setOnClickListener {
            showInputDialog("pagi", "07.00 WIB")
        }

        findViewById<MaterialButton>(R.id.btnInputSiang).setOnClickListener {
            showInputDialog("siang", "12.00 WIB")
        }

        findViewById<MaterialButton>(R.id.btnInputSore).setOnClickListener {
            showInputDialog("sore", "17.00 WIB")
        }

        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
            if (ContextCompat.checkSelfPermission(this, Manifest.permission.POST_NOTIFICATIONS) !=
                PackageManager.PERMISSION_GRANTED) {
                ActivityCompat.requestPermissions(
                    this,
                    arrayOf(Manifest.permission.POST_NOTIFICATIONS),
                    101
                )
            }
        }
    }

    override fun onResume() {
        super.onResume()
        // Cek status setiap kali layar aktif (untuk update realtime status)
        checkStatusGabungan()
    }

    private fun loadProfileData() {
        val token = sessionManager.getToken()
        if (token == null) {
            startActivity(Intent(this, MainActivity::class.java))
            finish()
            return
        }

        ApiClient.instance.getProfile("Bearer $token")
            .enqueue(object : Callback<ProfileResponse> {
                override fun onResponse(call: Call<ProfileResponse>, response: Response<ProfileResponse>) {
                    if (response.isSuccessful) {
                        val data = response.body()?.data
                        if (data != null) {
                            tvWelcome.text = "Halo, ${data.petugas.nama}"
                            tvBendungan.text = data.bendungan.namaBendungan
                            currentBendunganId = data.bendungan.id
                            sessionManager.saveServerStatus(
                                data.statusHarian.isPagiDone,
                                data.statusHarian.isSiangDone,
                                data.statusHarian.isSoreDone
                            )

                            checkStatusGabungan()
                        }
                    }
                }

                override fun onFailure(call: Call<ProfileResponse>, t: Throwable) {
                    Toast.makeText(applicationContext, "Gagal load profil: ${t.message}", Toast.LENGTH_SHORT).show()
                }
            })
    }

    private fun showInputDialog(waktuDb: String, labelJam: String) {
        val builder = AlertDialog.Builder(this)
        builder.setTitle("Input Tinggi Air ($labelJam)")
        builder.setMessage("Masukkan tinggi muka air (meter):")

        val input = EditText(this)
        input.inputType = InputType.TYPE_CLASS_NUMBER or InputType.TYPE_NUMBER_FLAG_DECIMAL
        input.hint = "Contoh: 12.5"
        builder.setView(input)

        builder.setPositiveButton("KIRIM") { _, _ ->
            val nilaiStr = input.text.toString()
            if (nilaiStr.isNotEmpty()) {
                val tinggiAir = nilaiStr.toDouble()
                kirimDataKeServer(waktuDb, tinggiAir)
            } else {
                Toast.makeText(this, "Data tidak boleh kosong", Toast.LENGTH_SHORT).show()
            }
        }
        builder.setNegativeButton("Batal") { dialog, _ -> dialog.cancel() }
        builder.show()
    }

    private fun kirimDataKeServer(waktu: String, tinggiAir: Double) {
        val userId = sessionManager.getUserId()
        val sdfDate = SimpleDateFormat("yyyy-MM-dd", Locale.getDefault())
        val sdfDateTime = SimpleDateFormat("yyyy-MM-dd HH:mm:ss", Locale.getDefault())
        val now = Date()
        if (currentBendunganId == 0) {
            Toast.makeText(this, "Data Bendungan belum dimuat", Toast.LENGTH_SHORT).show()
            return
        }
        val dataLokal = PencatatanEntity(
            tanggal = sdfDate.format(now),
            waktuInput = waktu,
            tinggiAir = tinggiAir,
            jamAktual = sdfDateTime.format(now),
            isSynced = false,
            idPetugas = userId,
            idBendungan = currentBendunganId
        )

        CoroutineScope(Dispatchers.IO).launch {
            val db = AppDatabase.getDatabase(applicationContext)

            // A. Simpan ke SQLite
            db.pencatatanDao().insert(dataLokal)

            // B. Panggil Robot Sync
            triggerUploadWorker()

            // C. UPDATE UI
            withContext(Dispatchers.Main) {
                checkStatusGabungan()
                Toast.makeText(this@DashboardActivity, "Data Disimpan (Menunggu Upload)", Toast.LENGTH_SHORT).show()
            }
        }
    }

    private fun triggerUploadWorker() {
        val constraints = Constraints.Builder()
            .setRequiredNetworkType(NetworkType.CONNECTED)
            .build()

        val uploadWork = OneTimeWorkRequest.Builder(UploadWorker::class.java)
            .setConstraints(constraints)
            .build()

        WorkManager.getInstance(this).enqueue(uploadWork)
    }

    private fun checkStatusGabungan() {
        val sdfDate = SimpleDateFormat("yyyy-MM-dd", Locale.getDefault())
        val today = sdfDate.format(Date())
        val userId = sessionManager.getUserId()

        CoroutineScope(Dispatchers.IO).launch {
            // 1. AMBIL STATUS SERVER
            val (serverPagi, serverSiang, serverSore) = sessionManager.getServerStatus()

            // 2. AMBIL STATUS LOKAL (Filter by User ID)
            val db = AppDatabase.getDatabase(applicationContext)
            val localData = if (currentBendunganId != 0) {
                db.pencatatanDao().getDataByDate(today, userId, currentBendunganId)
            } else {
                emptyList()
            }

            var localPagi = false
            var localSiang = false
            var localSore = false

            for (item in localData) {
                if (item.waktuInput == "pagi") localPagi = true
                if (item.waktuInput == "siang") localSiang = true
                if (item.waktuInput == "sore") localSore = true
            }

            // 3. LOGIKA GABUNGAN
            val isPagiLocked = serverPagi || localPagi
            val isSiangLocked = serverSiang || localSiang
            val isSoreLocked = serverSore || localSore

            // 4. UPDATE UI (Cast ke MaterialButton agar aman)
            withContext(Dispatchers.Main) {
                resetButtonState()

                if (isPagiLocked) lockButton(findViewById(R.id.btnInputPagi), "Pagi (Selesai)")
                if (isSiangLocked) lockButton(findViewById(R.id.btnInputSiang), "Siang (Selesai)")
                if (isSoreLocked) lockButton(findViewById(R.id.btnInputSore), "Sore (Selesai)")
            }
        }
    }

    private fun lockButton(button: MaterialButton, textBaru: String) {
        button.isEnabled = false
        button.text = textBaru
        button.setBackgroundColor(ContextCompat.getColor(this, android.R.color.darker_gray))
        button.setTextColor(ContextCompat.getColor(this, android.R.color.white))
        button.icon = ContextCompat.getDrawable(this, R.drawable.ic_lock)
    }

    private fun resetButtonState() {
        // Cast ke MaterialButton agar sesuai tipe
        val btnPagi = findViewById<MaterialButton>(R.id.btnInputPagi)
        val btnSiang = findViewById<MaterialButton>(R.id.btnInputSiang)
        val btnSore = findViewById<MaterialButton>(R.id.btnInputSore)

        btnPagi.isEnabled = true
        btnPagi.text = "Pencatatan Pagi\n07.00 WIB"
        // Set warna asli (sesuaikan dengan colors.xml jika perlu)
        btnPagi.setBackgroundColor(ContextCompat.getColor(this, R.color.pagiColor))
        btnPagi.setTextColor(ContextCompat.getColor(this, R.color.pagiText))
        btnPagi.icon = ContextCompat.getDrawable(this, R.drawable.ic_water)

        btnSiang.isEnabled = true
        btnSiang.text = "Pencatatan Siang\n12.00 WIB"
        btnSiang.setBackgroundColor(ContextCompat.getColor(this, R.color.siangColor))
        btnSiang.setTextColor(ContextCompat.getColor(this, R.color.siangText))
        btnSiang.icon = ContextCompat.getDrawable(this, R.drawable.ic_water)

        btnSore.isEnabled = true
        btnSore.text = "Pencatatan Sore\n17.00 WIB"
        btnSore.setBackgroundColor(ContextCompat.getColor(this, R.color.soreColor))
        btnSore.setTextColor(ContextCompat.getColor(this, R.color.soreText))
        btnSore.icon = ContextCompat.getDrawable(this, R.drawable.ic_water)
    }
}