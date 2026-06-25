<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Petugas;
use App\Models\Pencatatan;
use App\Models\PerhitunganDebit; // Model hasil hitung
use App\Services\DebitCalculatorService; // Service otak hitung
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class PetugasApiController extends Controller
{
    // 1. ENDPOINT LOGIN
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nik' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Cari petugas by NIK
        $petugas = Petugas::where('nik', $request->nik)->first();

        // Cek Password & Status Aktif
        if (!$petugas || !Hash::check($request->password, $petugas->password)) {
            return response()->json(['message' => 'NIK atau Password salah'], 401);
        }

        if (!$petugas->status_aktif) {
            return response()->json(['message' => 'Akun dinonaktifkan'], 403);
        }

        // Buat Token (Ini kuncinya Android)
        $token = $petugas->createToken('android_app')->plainTextToken;

        return response()->json([
            'message' => 'Login Berhasil',
            'token' => $token,
            'user' => [
                'id' => $petugas->id,
                'nama' => $petugas->nama_lengkap,
                'id_bendungan' => $petugas->id_bendungan,
            ]
        ]);
    }

    // 2. ENDPOINT SYNC DOWN (Ambil Data Bendungan & Rumus)
    public function getProfile(Request $request)
    {
        $petugas = $request->user();
        $petugas->load('bendungan');

        // LOGIKA BARU: Cek status pengisian hari ini di Bendungan tersebut
        // Tidak peduli SIAPA yang input, yang penting ID BENDUNGAN-nya
        $today = now()->format('Y-m-d');
        
        $pencatatan = \App\Models\Pencatatan::where('id_bendungan', $petugas->id_bendungan)
            ->where('tanggal_pencatatan', $today)
            ->first();

        // Cek mana yang sudah terisi (Not Null)
        $statusHariIni = [
            'pagi' => !is_null($pencatatan?->tinggi_air_pagi),
            'siang' => !is_null($pencatatan?->tinggi_air_siang),
            'sore' => !is_null($pencatatan?->tinggi_air_sore),
        ];

        return response()->json([
            'status' => 'success',
            'data' => [
                'petugas' => $petugas,
                'bendungan' => $petugas->bendungan,
                'status_harian' => $statusHariIni // <--- KITA KIRIM INI KE ANDROID
            ]
        ]);
    }
// GANTI METHOD INI SECARA KESELURUHAN
    public function kirimPencatatan(Request $request)
    {
        // 1. Validasi
        $request->validate([
            'tanggal' => 'required|date_format:Y-m-d',
            'waktu_input' => 'required|in:pagi,siang,sore',
            'tinggi_air' => 'required|numeric',
        ]);

        $petugas = $request->user();

        // 2. Tentukan Kolom
        $kolomTinggiAir = 'tinggi_air_' . $request->waktu_input;
        $kolomWaktuInput = 'waktu_input_' . $request->waktu_input;

        $pencatatan = \App\Models\Pencatatan::updateOrCreate(
            [
                'id_bendungan' => $petugas->id_bendungan,
                'tanggal_pencatatan' => $request->tanggal,
            ],
            [
                $kolomTinggiAir => $request->tinggi_air,
                $kolomWaktuInput => now(),
                'id_petugas' => $petugas->id, // Update petugas terakhir
            ]
        );

        // 4. PAKSA OBSERVER MENGHITUNG DEBIT
        // Panggil manual agar perhitungan debit pasti jalan
        (new \App\Observers\PencatatanObserver)->updated($pencatatan);

        // 5. Ambil data debit untuk balikan ke Android
        $debit = \App\Models\PerhitunganDebit::where('id_pencatatan', $pencatatan->id)->first();
        
        $nilaiDebit = 0;
        if ($debit) {
            $kolomDebit = 'debit_' . $request->waktu_input;
            $nilaiDebit = $debit->$kolomDebit;
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil disimpan',
            'hasil_debit' => $nilaiDebit
        ]);
    }
    
    
    public function checkVersion()
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                // Ganti angka ini setiap kali rilis fitur baru yang "Breaking Change"
                'min_version_code' => 1, 
                'latest_version_name' => '1.0',
                'download_url' => 'https://website-dinas.go.id/download-apk' // Opsional
            ]
        ]);
    }
}