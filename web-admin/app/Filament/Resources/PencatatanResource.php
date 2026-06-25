<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PencatatanResource\Pages;
use App\Models\Pencatatan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class PencatatanResource extends Resource
{
    protected static ?string $model = Pencatatan::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Laporan Tinggi Muka Air'; 
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?int $sort = 1;

    public static function canCreate(): bool { return false; } 

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Dasar')
                    ->description('Data lokasi dan waktu tidak dapat diubah untuk menjaga integritas data.')
                    ->schema([
                        Forms\Components\DatePicker::make('tanggal_pencatatan')
                            ->label('Tanggal')
                            ->native(false)
                            ->displayFormat('d F Y')
                            ->readonly(), 
                        Forms\Components\Select::make('id_bendungan')
                            ->relationship('bendungan', 'nama_bendungan')
                            ->label('Lokasi Bendungan')
                            ->disabled(), 

                        Forms\Components\Select::make('id_petugas')
                            ->relationship('petugas', 'nama_lengkap')
                            ->label('Petugas Input')
                            ->disabled(),
                    ])->columns(3),

                Forms\Components\Section::make('Koreksi Data Tinggi Air')
                    ->description('Ubah angka di bawah ini HANYA jika terjadi kesalahan input. Debit akan dihitung ulang otomatis setelah disimpan.')
                    ->schema([
                        Forms\Components\TextInput::make('tinggi_air_pagi')
                            ->label('Pagi (07.00)')
                            ->numeric()
                            ->suffix('meter'),

                        Forms\Components\TextInput::make('tinggi_air_siang')
                            ->label('Siang (12.00)')
                            ->numeric()
                            ->suffix('meter'),

                        Forms\Components\TextInput::make('tinggi_air_sore')
                            ->label('Sore (17.00)')
                            ->numeric()
                            ->suffix('meter'),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // 1. INFO DASAR (Sticky di kiri kalau di HP)
                Tables\Columns\TextColumn::make('tanggal_pencatatan')
                    ->label('Tanggal')
                    ->date('d M Y') // Format: 08 Des 2025
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('bendungan.nama_bendungan')
                    ->label('Lokasi Bendungan')
                    ->sortable()
                    ->searchable()
                    ->description(fn (Pencatatan $record): string => $record->petugas->nama_lengkap ?? '-'),

                // 2. DATA TINGGI AIR (Dibuat Berdampingan agar mudah dibaca)
                Tables\Columns\TextColumn::make('tinggi_air_pagi')
                    ->label('Pagi (07.00)')
                    ->suffix(' m')
                    ->numeric(2) // 2 desimal
                    ->alignCenter()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('tinggi_air_siang')
                    ->label('Siang (12.00)')
                    ->suffix(' m')
                    ->numeric(2)
                    ->alignCenter()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('tinggi_air_sore')
                    ->label('Sore (17.00)')
                    ->suffix(' m')
                    ->numeric(2)
                    ->alignCenter()
                    ->placeholder('-'),
                
                // Indikator Kelengkapan Data
                Tables\Columns\IconColumn::make('status_pencatatan')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->getStateUsing(fn ($record) => 
                        !is_null($record->tinggi_air_pagi) && 
                        !is_null($record->tinggi_air_siang) && 
                        !is_null($record->tinggi_air_sore)
                    ),
            ])
            ->defaultSort('tanggal_pencatatan', 'desc')  
            ->filters([
                // Filter Bendungan
                SelectFilter::make('id_bendungan')
                    ->relationship('bendungan', 'nama_bendungan')
                    ->label('Pilih Bendungan'),

                // Filter Rentang Tanggal (Bisa set 1 Tahun)
                Filter::make('tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('dari_tanggal'),
                        Forms\Components\DatePicker::make('sampai_tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari_tanggal'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal_pencatatan', '>=', $date),
                            )
                            ->when(
                                $data['sampai_tanggal'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal_pencatatan', '<=', $date),
                            );
                    })
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Koreksi')
                    ->icon('heroicon-o-pencil-square'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ExportBulkAction::make()->label('Download Excel (Tinggi Air)'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPencatatans::route('/'),
            'edit' => Pages\EditPencatatan::route('/{record}/edit'),
        ];
    }
}