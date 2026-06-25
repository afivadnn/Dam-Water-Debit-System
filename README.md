# Sistem Informasi Pencatatan & Pemantauan Debit Air Bendungan 💧

Sistem terintegrasi (*End-to-End*) untuk mendigitalisasi proses pemantauan debit air bendungan. Proyek ini menjembatani aktivitas pencatatan oleh petugas di lapangan secara *real-time* dengan pusat data yang dikelola oleh Administrator.

## 🚀 Fitur Utama

### 💻 Web Admin Dashboard (Laravel)
* **Dynamic Formula Configuration:** Admin dapat mengonfigurasi rumus matematika kustom (misal: `C * (H + B)`) beserta parameter konstantanya secara dinamis untuk setiap bendungan.
* **Manajemen & Penempatan:** Pengaturan titik bendungan dan penempatan petugas spesifik.
* **Automated Reporting:** Generate dan unduh laporan data debit air dalam format Excel.

### 📱 Mobile Application (Kotlin)
* **Task-Based Input:** Input data debit air rutin (Pagi, Siang, Sore).
* **Automated Data Sync:** Data tersinkronisasi otomatis ke server secara *real-time*.
* **Location-Specific Access:** Petugas hanya memiliki akses pada bendungan sesuai penempatan yang diatur oleh admin.

## 🛠 Tech Stack
* **Backend & Web:** PHP, Laravel, MySQL
* **Mobile:** Android Studio, Kotlin
* **API:** RESTful API

## 📂 Struktur Repositori
* `/web-admin` : Source code untuk dashboard admin (Laravel).
* `/mobile-app` : Source code untuk aplikasi Android (Kotlin).

## 📸 Screenshot
<img width="570" height="817" alt="Picture1" src="https://github.com/user-attachments/assets/ac447c50-8ee9-4f77-9a32-b541e5a6793b" />
<img width="1920" height="1080" alt="Screenshot 2026-03-30 082623" src="https://github.com/user-attachments/assets/59d13988-d4c9-4827-a320-e6398f3815b7" />


