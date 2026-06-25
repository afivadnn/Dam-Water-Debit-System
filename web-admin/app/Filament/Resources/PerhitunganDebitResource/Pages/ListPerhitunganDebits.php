<?php

namespace App\Filament\Resources\PerhitunganDebitResource\Pages;

use App\Filament\Resources\PerhitunganDebitResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use pxlrbt\FilamentExcel\Actions\Pages\ExportAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use Carbon\Carbon; // Import Carbon untuk format tanggal

class ListPerhitunganDebits extends ListRecords
{
    protected static string $resource = PerhitunganDebitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            
            ExportAction::make()
                ->label('Download Laporan Debit')
                ->icon('heroicon-o-arrow-down-tray') // Tambah ikon biar keren
                ->color('success')
                ->exports([
                    ExcelExport::make()
                        ->fromTable()
                        ->withFilename(fn ($resource) => 'Laporan_Debit_Air_' . date('d-m-Y'))
                        ->withColumns([
                            // 1. NAMA BENDUNGAN (Penting di paling kiri)
                            Column::make('bendungan.nama_bendungan')
                                ->heading('Nama Bendungan')
                                ->formatStateUsing(fn ($state) => strtoupper($state)), // Huruf Besar Semua

                            // 2. TANGGAL (Format Indonesia: 08 Desember 2025)
                            Column::make('tanggal')
                                ->heading('Tanggal')
                                ->formatStateUsing(fn ($state) => Carbon::parse($state)->translatedFormat('d F Y')),

                            // 3. DATA DEBIT (Kasih Satuan di Judul)
                            Column::make('debit_pagi')
                                ->heading('Debit Pagi (m³/s)')
                                ->formatStateUsing(fn ($state) => $state ?? '-'), // Kalau kosong kasih strip

                            Column::make('debit_siang')
                                ->heading('Debit Siang (m³/s)')
                                ->formatStateUsing(fn ($state) => $state ?? '-'),

                            Column::make('debit_sore')
                                ->heading('Debit Sore (m³/s)')
                                ->formatStateUsing(fn ($state) => $state ?? '-'),

                            // 4. RATA-RATA (Penting)
                            Column::make('debit_rata_rata_harian')
                                ->heading('Rata-Rata Harian (m³/s)')
                                ->formatStateUsing(fn ($state) => $state ? number_format($state, 2) : '-'), // Dibatasi 2 desimal

                            // 5. RUMUS (Buat Info Teknis)
                            Column::make('rumus_digunakan')
                                ->heading('Rumus Teknis'),
                        ])
                ]),
        ];
    }
}