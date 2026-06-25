<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Bendungan; 
use App\Models\Pencatatan; 
use Carbon\Carbon;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Hitung data real
        $totalBendungan = Bendungan::count();
        $laporanHariIni = Pencatatan::whereDate('created_at', Carbon::today())->count();
        
        // Contoh logika sederhana status (bisa disesuaikan logic aslinya)
        $bendunganSiaga = Pencatatan::whereDate('created_at', Carbon::today())
                            ->where('tinggi_air_pagi', '>', 200) // Contoh ambang batas
                            ->count();

        return [
            Stat::make('Total Bendungan', $totalBendungan . ' Lokasi')
                ->description('Terdaftar dalam sistem')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('primary'),

            Stat::make('Laporan Masuk', $laporanHariIni . ' Data')
                ->description('Pencatatan hari ini')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color('success') // Hijau
                ->chart([7, 2, 10, 3, 15, 4, 17]), // Pemanis grafik mini

            Stat::make('Status Siaga', $bendunganSiaga . ' Bendungan')
                ->description('Perlu perhatian khusus')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($bendunganSiaga > 0 ? 'danger' : 'gray'), // Merah jika ada bahaya
        ];
    }
}