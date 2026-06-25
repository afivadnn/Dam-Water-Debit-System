package com.konstruksi.debitair.workers

import android.Manifest
import android.app.NotificationChannel
import android.app.NotificationManager
import android.content.Context
import android.content.pm.PackageManager
import android.os.Build
import androidx.core.app.ActivityCompat
import androidx.core.app.NotificationCompat
import androidx.core.app.NotificationManagerCompat
import androidx.work.CoroutineWorker
import androidx.work.WorkerParameters
import com.konstruksi.debitair.R
import com.konstruksi.debitair.data.api.ApiClient
import com.konstruksi.debitair.data.local.AppDatabase
import com.konstruksi.debitair.data.model.PencatatanRequest
import com.konstruksi.debitair.utils.SessionManager

class UploadWorker(context: Context, params: WorkerParameters) : CoroutineWorker(context, params) {

    override suspend fun doWork(): Result {
        android.util.Log.d("UPLOAD_WORKER", "Robot bangun! Memulai pekerjaan...") // <--- LOG 1

        val context = applicationContext
        val db = AppDatabase.getDatabase(context)
        val sessionManager = SessionManager(context)
        val token = sessionManager.getToken()

        if (token == null) {
            android.util.Log.e("UPLOAD_WORKER", "Gagal: Token null") // <--- LOG 2
            return Result.failure()
        }

        val unsyncedData = db.pencatatanDao().getUnsyncedData()
        android.util.Log.d("UPLOAD_WORKER", "Ditemukan ${unsyncedData.size} data antrian") // <--- LOG 3

        if (unsyncedData.isEmpty()) return Result.success()

        try {
            for (item in unsyncedData) {

                // --- BAGIAN INI YANG TADI HILANG ---
                // Kita harus bungkus data dari database lokal menjadi format Request API
                val request = PencatatanRequest(
                    tanggal = item.tanggal,
                    waktuInput = item.waktuInput,
                    tinggiAir = item.tinggiAir,
                    jamAktual = item.jamAktual
                )
                // ------------------------------------

                android.util.Log.d("UPLOAD_WORKER", "Mencoba kirim ID: ${item.id} ke Server...") // <--- LOG 4

                // Sekarang variabel 'request' sudah dikenali
                val response = ApiClient.instance.kirimPencatatan("Bearer $token", request).execute()

                if (response.isSuccessful) {
                    android.util.Log.d("UPLOAD_WORKER", "Sukses Kirim! Hapus antrian lokal.") // <--- LOG 5

                    // Tandai sudah synced agar tidak dikirim ulang
                    db.pencatatanDao().markAsSynced(item.id)

                    // Munculkan Notifikasi
                    showSuccessNotification(item.waktuInput, item.tinggiAir)
                } else {
                    android.util.Log.e("UPLOAD_WORKER", "Gagal Kirim. Kode: ${response.code()}") // <--- LOG 6
                    return Result.retry()
                }
            }
            return Result.success()

        } catch (e: Exception) {
            android.util.Log.e("UPLOAD_WORKER", "Exception/Crash: ${e.message}") // <--- LOG 7
            e.printStackTrace()
            return Result.retry()
        }
    }

    private fun showSuccessNotification(waktu: String, tinggi: Double) {
        val channelId = "upload_channel"
        val notificationId = System.currentTimeMillis().toInt()

        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            val name = "Sinkronisasi Data"
            val descriptionText = "Notifikasi hasil upload data debit air"
            val importance = NotificationManager.IMPORTANCE_DEFAULT
            val channel = NotificationChannel(channelId, name, importance).apply {
                description = descriptionText
            }
            val notificationManager: NotificationManager =
                applicationContext.getSystemService(Context.NOTIFICATION_SERVICE) as NotificationManager
            notificationManager.createNotificationChannel(channel)
        }

        val builder = NotificationCompat.Builder(applicationContext, channelId)
            .setSmallIcon(R.drawable.ic_water)
            .setContentTitle("Upload Berhasil!")
            .setContentText("Data $waktu ($tinggi m) telah tersimpan di server.")
            .setPriority(NotificationCompat.PRIORITY_DEFAULT)
            .setAutoCancel(true)

        if (ActivityCompat.checkSelfPermission(
                applicationContext,
                Manifest.permission.POST_NOTIFICATIONS
            ) == PackageManager.PERMISSION_GRANTED
        ) {
            NotificationManagerCompat.from(applicationContext).notify(notificationId, builder.build())
        }
    }
}