<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PerhitunganDebitResource\Pages;
use App\Models\PerhitunganDebit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class PerhitunganDebitResource extends Resource
{
    protected static ?string $model = PerhitunganDebit::class;
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Laporan Debit Air'; // Nama Menu
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?int $sort = 2;

    public static function canCreate(): bool { return false; } // Hasil hitungan otomatis

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('bendungan.nama_bendungan')
                    ->label('Bendungan')
                    ->searchable()
                    ->sortable(),

                // GRUP: DEBIT PER WAKTU
                // Kita gunakan 4 desimal untuk presisi debit
                Tables\Columns\TextColumn::make('debit_pagi')
                    ->label('Q Pagi')
                    ->numeric(4)
                    ->suffix(' m³/s')
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('debit_siang')
                    ->label('Q Siang')
                    ->numeric(4)
                    ->suffix(' m³/s')
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('debit_sore')
                    ->label('Q Sore')
                    ->numeric(4)
                    ->suffix(' m³/s')
                    ->alignEnd(),

                // KOLOM PENTING: RATA-RATA HARIAN
                Tables\Columns\TextColumn::make('debit_rata_rata_harian')
                    ->label('Rata-Rata Harian')
                    ->weight('bold') // Tebalkan biar menonjol
                    ->color('primary')
                    ->numeric(4)
                    ->suffix(' m³/s')
                    ->alignEnd()
                    ->sortable(),
            ])
            ->defaultSort('tanggal', 'desc')
            ->filters([
                SelectFilter::make('id_bendungan')
                    ->relationship('bendungan', 'nama_bendungan')
                    ->label('Filter Bendungan'),
                
                // Copy Filter Tanggal dari Resource sebelah jika perlu
                Filter::make('tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('dari_tanggal'),
                        Forms\Components\DatePicker::make('sampai_tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari_tanggal'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal', '>=', $date),
                            )
                            ->when(
                                $data['sampai_tanggal'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal', '<=', $date),
                            );
                    })
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ExportBulkAction::make()->label('Download Excel (Debit)'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPerhitunganDebits::route('/'),
        ];
    }
}