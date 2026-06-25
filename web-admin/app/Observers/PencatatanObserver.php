<?php

namespace App\Observers;

use App\Models\Pencatatan;
use App\Models\PerhitunganDebit; // Wajib import Model Debit
use App\Models\Bendungan;        // Wajib import Model Bendungan
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // Tambahan untuk mencatat error di storage/logs/laravel.log

class PencatatanObserver
{
    /**
     * Handle the Pencatatan "created" event.
     */
    public function created(Pencatatan $pencatatan): void
    {
        $this->logActivity('create', 'Input Data Baru', $pencatatan);
        
        // Langsung hitung debit saat data pertama kali masuk (baik dari Android atau Web)
        $this->recalculateDebit($pencatatan);
    }

    /**
     * Handle the Pencatatan "updated" event.
     */
   public function updated(Pencatatan $pencatatan): void
    {
        // 1. Catat Log (Tetap pakai isDirty biar log gak penuh sampah)
        if ($pencatatan->isDirty(['tinggi_air_pagi', 'tinggi_air_siang', 'tinggi_air_sore'])) {
            $this->logActivity('update', 'Koreksi Data Tinggi Air', $pencatatan);
        }

        // 2. HITUNG DEBIT (JALANKAN SELALU)
        // Hapus if($isDirty) di sini agar debit selalu dihitung ulang setiap kali ada update dari Controller
        $this->recalculateDebit($pencatatan);
    }

    /**
     * Handle the Pencatatan "deleted" event.
     */
    public function deleted(Pencatatan $pencatatan): void
    {
        $this->logActivity('delete', 'Penghapusan Data', $pencatatan);
        
        // Hapus juga data perhitungan debit terkait agar database bersih
        PerhitunganDebit::where('id_bendungan', $pencatatan->id_bendungan)
            ->where('tanggal', $pencatatan->tanggal_pencatatan)
            ->delete();
    }

    /**
     * -------------------------------------------------------------------
     * CORE LOGIC: MENGHITUNG ULANG DEBIT OTOMATIS
     * -------------------------------------------------------------------
     */
   private function recalculateDebit(Pencatatan $pencatatan)
    {
        // 1. Ambil Data Bendungan
        $bendungan = Bendungan::find($pencatatan->id_bendungan);
        
        if (!$bendungan || empty($bendungan->rumus_debit)) return;

        // 2. Fungsi Helper Hitung (VERSI JSON PARSER)
        $hitung = function ($tinggiAir, $rumus) use ($bendungan) {
            if ($tinggiAir === null || $tinggiAir === '') return null;
            
            try {
                // A. Sanitasi Input Tinggi Air
                $tinggiAir = str_replace(',', '.', $tinggiAir);

                // B. BACA PARAMETER DARI JSON (KUNCI PERBAIKANNYA DI SINI)
                // Kolom 'parameter_rumus' isinya string JSON: {"B": "2", "C": "10"}
                // Kita ubah jadi Array PHP.
                $params = [];
                if (!empty($bendungan->parameter_rumus)) {
                    // Cek apakah sudah otomatis jadi array (lewat $casts di model) atau masih string
                    if (is_array($bendungan->parameter_rumus)) {
                        $params = $bendungan->parameter_rumus;
                    } else {
                        $params = json_decode($bendungan->parameter_rumus, true);
                    }
                }

                // C. Ganti Variabel Rumus dengan Angka dari JSON
                // Loop semua parameter yang ada (misal B=2, C=10)
                if (is_array($params)) {
                    foreach ($params as $key => $value) {
                        // $key = "B", $value = "2"
                        // Ganti huruf B di rumus dengan angka 2
                        $rumus = str_replace($key, $value, $rumus);
                    }
                }

                // D. Ganti Variabel H dengan Tinggi Air
                $rumus = str_replace(['H', 'h'], $tinggiAir, $rumus);

                // E. Perbaikan Syntax Matematika
                $rumus = str_replace('^', '**', $rumus); // Pangkat
                $rumus = str_replace('x', '*', $rumus);  // Kali

                // F. EVALUASI AKHIR
                // Cek apakah masih ada huruf yang tertinggal? (Indikator parameter kurang lengkap)
                if (preg_match('/[a-zA-Z]/', $rumus)) {
                     // Jika masih ada huruf (selain e dari eksponen), berarti gagal replace
                     \Illuminate\Support\Facades\Log::error("Gagal Hitung {$bendungan->nama_bendungan}: Parameter tidak lengkap. Sisa rumus: $rumus");
                     return 0;
                }

                return eval("return $rumus;"); 

            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("Gagal Hitung {$bendungan->nama_bendungan}: " . $e->getMessage());
                return 0;
            }
        };

        // 3. Eksekusi Hitungan
        $debitPagi = $hitung($pencatatan->tinggi_air_pagi, $bendungan->rumus_debit);
        $debitSiang = $hitung($pencatatan->tinggi_air_siang, $bendungan->rumus_debit);
        $debitSore = $hitung($pencatatan->tinggi_air_sore, $bendungan->rumus_debit);

        // 4. Hitung Rata-rata
        $listDebit = array_filter([$debitPagi, $debitSiang, $debitSore], fn($x) => !is_null($x) && $x !== '' && $x !== 0);
        $rataRata = count($listDebit) > 0 ? array_sum($listDebit) / count($listDebit) : 0;

        // 5. Simpan Hasil
        PerhitunganDebit::updateOrCreate(
            [
                'id_bendungan' => $pencatatan->id_bendungan,
                'tanggal' => $pencatatan->tanggal_pencatatan,
            ],
            [
                'id_pencatatan' => $pencatatan->id,
                'debit_pagi' => $debitPagi,
                'debit_siang' => $debitSiang,
                'debit_sore' => $debitSore,
                'debit_rata_rata_harian' => $rataRata,
                'rumus_digunakan' => $bendungan->rumus_debit,
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Fungsi Helper untuk Simpan ke Log Aktivitas
     */
    private function logActivity($jenis, $deskripsi, $data)
    {
        $userId = null;
        $userType = 'system';

        // Deteksi User Login
        if (Auth::check()) {
            $user = Auth::user();
            $userId = $user->id;
            // Cek instance model untuk menentukan tipe user (Admin vs Petugas Lapangan)
            $userType = $user instanceof \App\Models\Petugas ? 'petugas' : 'admin';
        }

        // Simpan Log ke Database
        DB::table('tabel_log_aktivitas')->insert([
            'id_user' => $userId,
            'user_type' => $userType,
            'jenis_aktivitas' => $jenis,
            'deskripsi_aktivitas' => "$deskripsi - Tgl: {$data->tanggal_pencatatan->format('d-m-Y')} (ID: {$data->id})",
            'ip_address' => request()->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}