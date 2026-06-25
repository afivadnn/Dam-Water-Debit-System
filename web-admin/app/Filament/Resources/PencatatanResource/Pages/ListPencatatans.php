<?php

namespace App\Filament\Resources\PencatatanResource\Pages;

use App\Filament\Resources\PencatatanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use pxlrbt\FilamentExcel\Actions\Pages\ExportAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use Carbon\Carbon;

class ListPencatatans extends ListRecords
{
    protected static string $resource = PencatatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            ExportAction::make()
                ->label('Download Data Lapangan')
                ->icon('heroicon-o-clipboard-document-list')
                ->color('warning')
                ->exports([
                    ExcelExport::make()
                        ->fromTable()
                        ->withFilename(fn ($resource) => 'Log_Lapangan_' . date('d-m-Y'))
                        ->withColumns([
                            // Info Dasar
                            Column::make('tanggal_pencatatan')
                                ->heading('Tanggal')
                                ->formatStateUsing(fn ($state) => Carbon::parse($state)->format('d/m/Y')), // Format pendek: 08/12/2025

                            Column::make('bendungan.nama_bendungan')
                                ->heading('Lokasi Bendungan'),
                            
                            Column::make('petugas.nama_lengkap')
                                ->heading('Petugas Pemeriksa'),

                            // PAGI
                            Column::make('tinggi_air_pagi')->heading('Tinggi Pagi (m)'),
                            Column::make('waktu_input_pagi')
                                ->heading('Jam Input Pagi')
                                ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('H:i') . ' WIB' : '-'),

                            // SIANG
                            Column::make('tinggi_air_siang')->heading('Tinggi Siang (m)'),
                            Column::make('waktu_input_siang')
                                ->heading('Jam Input Siang')
                                ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('H:i') . ' WIB' : '-'),

                            // SORE
                            Column::make('tinggi_air_sore')->heading('Tinggi Sore (m)'),
                            Column::make('waktu_input_sore')
                                ->heading('Jam Input Sore')
                                ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('H:i') . ' WIB' : '-'),
                        ])
                ]),
        ];
    }
}