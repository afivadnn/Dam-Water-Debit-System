<?php

namespace App\Services;

use App\Models\Bendungan;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Illuminate\Support\Facades\Log;

class DebitCalculatorService
{
    protected $expressionLanguage;

    public function __construct()
    {
        // Inisialisasi library parser matematika
        $this->expressionLanguage = new ExpressionLanguage();
    }

    /**
     * Menghitung debit air berdasarkan rumus dinamis.
     * * @param Bendungan $bendungan Model bendungan yang berisi rumus & parameter
     * @param float $tinggiAir Tinggi muka air (H) dalam meter
     * @return float Hasil debit (m3/s)
     */
    public function calculate(Bendungan $bendungan, float $tinggiAir): float
    {
        // 1. Validasi awal: Jika rumus kosong, return 0
        if (empty($bendungan->rumus_debit)) {
            return 0.0;
        }

        // 2. Siapkan Variabel
        // Ambil parameter konstanta dari database (contoh: C, L, a, b)
        // Jika null, gunakan array kosong
        $params = $bendungan->parameter_rumus ?? [];
        
        // Masukkan variabel dinamis 'H' (Tinggi Air) ke dalam array parameter
        // PENTING: Di rumus admin nanti WAJIB pakai variabel 'H'
        $params['H'] = $tinggiAir; 

        try {
            // 3. Eksekusi Perhitungan
            // evaluate() akan memproses string matematika dengan aman
            $result = $this->expressionLanguage->evaluate($bendungan->rumus_debit, $params);

            // 4. Return hasil (dibulatkan 4 angka di belakang koma)
            return round((float) $result, 4);

        } catch (\Exception $e) {
            // 5. Error Handling
            // Jika rumus error (misal dibagi nol atau sintaks salah), catat di log
            Log::error("Calculation Error on Bendungan ID {$bendungan->id}: " . $e->getMessage(), [
                'rumus' => $bendungan->rumus_debit,
                'params' => $params
            ]);

            // Return 0 agar sistem tidak crash total
            return 0.0;
        }
    }

    /**
     * Method tambahan untuk memvalidasi rumus saat Admin menginput data.
     * Gunakan ini nanti di Filament Resource untuk validasi form.
     */
    public function validateFormula(string $rumus, array $params): bool
    {
        // Tambahkan dummy H untuk testing validasi
        $params['H'] = 1.0; 
        
        try {
            $this->expressionLanguage->evaluate($rumus, $params);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}