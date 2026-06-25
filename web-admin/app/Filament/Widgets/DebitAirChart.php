<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\PerhitunganDebit;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache; // WAJIB: Import Facade Cache

class DebitAirChart extends ChartWidget
{
    protected static ?string $heading = 'Monitoring Debit Air (7 Hari Terakhir)';
    
    protected static ?int $sort = 1; 

    // REKOMENDASI OPTIMASI: 
    // Ubah interval polling agar sinkron dengan durasi cache.
    // Jika cache 60 detik, polling 5 detik hanya membuang bandwidth.
    protected static ?string $pollingInterval = '30s'; 

    protected function getData(): array
    {
        // GUNAKAN CACHE
        // Key: 'dashboard_debit_chart'
        // Durasi: 60 detik (1 menit)
        return Cache::remember('dashboard_debit_chart', 60, function () {
            
            // --- LOGIKA QUERY BERAT DIPINDAHKAN KE DALAM SINI ---
            
            // 1. Ambil data
            $data = PerhitunganDebit::where('tanggal', '>=', now()->subDays(7))
                ->orderBy('tanggal', 'asc')
                ->get();

            // 2. Formatting Data
            $labels = [];
            $pagi = [];
            $siang = [];
            $sore = [];

            foreach ($data as $row) {
                // Format tanggal: "05 Des"
                $labels[] = Carbon::parse($row->tanggal)->translatedFormat('d M');
                
                // Masukkan data
                $pagi[] = $row->debit_pagi;
                $siang[] = $row->debit_siang;
                $sore[] = $row->debit_sore;
            }

            // 3. Return Struktur Array Chart
            return [
                'datasets' => [
                    [
                        'label' => 'Debit Pagi',
                        'data' => $pagi,
                        'borderColor' => '#3B82F6', 
                        'backgroundColor' => '#3B82F6',
                    ],
                    [
                        'label' => 'Debit Siang',
                        'data' => $siang,
                        'borderColor' => '#22C55E', 
                        'backgroundColor' => '#22C55E',
                    ],
                    [
                        'label' => 'Debit Sore',
                        'data' => $sore,
                        'borderColor' => '#F97316', 
                        'backgroundColor' => '#F97316',
                    ],
                ],
                'labels' => $labels,
            ];
            
            // --- AKHIR LOGIKA CACHE ---
        });
    }

    protected function getType(): string
    {
        return 'line';
    }
}