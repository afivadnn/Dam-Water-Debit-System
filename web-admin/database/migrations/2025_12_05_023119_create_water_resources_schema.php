<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. TABEL BENDUNGAN
        Schema::create('tabel_bendungan', function (Blueprint $table) {
            $table->id();
            $table->string('kode_bendungan')->unique();
            $table->string('nama_bendungan');
            $table->string('lokasi');
            $table->decimal('koordinat_latitude', 10, 7);
            $table->decimal('koordinat_longitude', 11, 7);
            $table->text('rumus_debit');
            $table->json('parameter_rumus')->nullable();
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. TABEL USERS (Admin)
        Schema::create('tabel_users', function (Blueprint $table) {
            $table->id();
            $table->string('nama_lengkap');
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('role', ['admin', 'supervisor', 'viewer'])->default('viewer');
            $table->timestamps();
        });

        // 3. TABEL PETUGAS
        Schema::create('tabel_petugas', function (Blueprint $table) {
            $table->id();
            $table->string('nik', 20)->unique();
            $table->string('nama_lengkap');
            $table->string('email')->nullable();
            $table->string('nomor_telepon', 15);
            $table->foreignId('id_bendungan')->constrained('tabel_bendungan')->onDelete('restrict');
            $table->string('password');
            $table->string('foto_profil')->nullable();
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // 4. TABEL PENCATATAN
        Schema::create('tabel_pencatatan_tinggi_air', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_bendungan')->constrained('tabel_bendungan');
            $table->foreignId('id_petugas')->constrained('tabel_petugas');
            $table->date('tanggal_pencatatan');
            
            $table->decimal('tinggi_air_pagi', 8, 2)->nullable();
            $table->dateTime('waktu_input_pagi')->nullable();
            
            $table->decimal('tinggi_air_siang', 8, 2)->nullable();
            $table->dateTime('waktu_input_siang')->nullable();
            
            $table->decimal('tinggi_air_sore', 8, 2)->nullable();
            $table->dateTime('waktu_input_sore')->nullable();
            
            $table->index(['id_bendungan', 'tanggal']); 
            $table->index(['id_bendungan', 'created_at']);
            
            $table->enum('status_pencatatan', ['draft', 'lengkap', 'terlambat', 'anomali'])->default('draft');
            $table->text('catatan_petugas')->nullable();
            $table->timestamps();
        });

        // 5. TABEL HASIL HITUNG
        Schema::create('tabel_perhitungan_debit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pencatatan')->constrained('tabel_pencatatan_tinggi_air')->onDelete('cascade');
            $table->foreignId('id_bendungan')->constrained('tabel_bendungan');
            $table->date('tanggal');
            
            $table->decimal('debit_pagi', 12, 4)->nullable();
            $table->decimal('debit_siang', 12, 4)->nullable();
            $table->decimal('debit_sore', 12, 4)->nullable();
            $table->decimal('debit_rata_rata_harian', 12, 4)->nullable();

            $table->index(['id_bendungan', 'tanggal']); // Index Gabungan
            $table->index(['id_bendungan', 'created_at']);
            
            $table->string('satuan_debit')->default('m³/s');
            $table->text('rumus_digunakan');
            $table->timestamps();
        });

        // 6. LOG AKTIVITAS
        Schema::create('tabel_log_aktivitas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_user')->nullable();
            $table->string('user_type')->nullable();
            $table->string('jenis_aktivitas');
            $table->text('deskripsi_aktivitas');
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tabel_log_aktivitas');
        Schema::dropIfExists('tabel_perhitungan_debit');
        Schema::dropIfExists('tabel_pencatatan_tinggi_air');
        Schema::dropIfExists('tabel_petugas');
        Schema::dropIfExists('tabel_users');
        Schema::dropIfExists('tabel_bendungan');
    }
};  