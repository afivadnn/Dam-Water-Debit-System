<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PetugasApiController;

// Route Public (Bisa diakses tanpa login)
Route::post('/login', [PetugasApiController::class, 'login']);
Route::get('/version', [PetugasApiController::class, 'checkVersion']);
// Route Protected (Harus ada Token/Login)
Route::middleware('auth:sanctum')->group(function () {
    
    // Ambil data profil & bendungan
    Route::get('/me', [PetugasApiController::class, 'getProfile']);
    
    // Kirim pencatatan
    Route::post('/pencatatan', [PetugasApiController::class, 'kirimPencatatan']);
    
});