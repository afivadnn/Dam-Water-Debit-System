<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BendunganResource\Pages;
use App\Models\Bendungan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\KeyValue;

class BendunganResource extends Resource
{
    protected static ?string $model = Bendungan::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-library'; 
    protected static ?string $navigationLabel = 'Data Bendungan';
    protected static ?string $modelLabel = 'Bendungan';
    protected static ?string $pluralModelLabel = 'Data Bendungan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Dasar')
                    ->schema([
                        TextInput::make('kode_bendungan')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->label('Kode Bendungan'),
                        TextInput::make('nama_bendungan')
                            ->required()
                            ->label('Nama Bendungan'),
                        TextInput::make('lokasi')
                            ->required(),
                        TextInput::make('koordinat_latitude')
                            ->numeric()
                            ->required()
                            ->label('Latitude'),
                        TextInput::make('koordinat_longitude')
                            ->numeric()
                            ->required()
                            ->label('Longitude'),
                    ])->columns(2),

                Section::make('Konfigurasi Rumus Debit')
                    ->schema([
                        Textarea::make('rumus_debit')
                            ->label('Rumus Matematika')
                            ->helperText('Contoh: C * L * (H ** 1.5) | Variabel H = Tinggi Air.')
                            ->required()
                            ->rows(3),
                        
                        KeyValue::make('parameter_rumus')
                            ->label('Parameter Konstanta')
                            ->keyLabel('Variabel (cth: C)')
                            ->valueLabel('Nilai')
                            ->reorderable(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        // 2. DEFINISIKAN KOLOM AGAR DATA MUNCUL DI LIST
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_bendungan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama_bendungan')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'), // Biar tebal
                Tables\Columns\TextColumn::make('lokasi')
                    ->limit(30),
                Tables\Columns\TextColumn::make('rumus_debit')
                    ->limit(20)
                    ->label('Rumus'),
                Tables\Columns\IconColumn::make('status_aktif')
                    ->boolean()
                    ->label('Aktif'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListBendungans::route('/'),
            'create' => Pages\CreateBendungan::route('/create'),
            'edit' => Pages\EditBendungan::route('/{record}/edit'),
        ];
    }
}