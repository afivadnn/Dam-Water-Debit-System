<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PetugasResource\Pages;
use App\Models\Petugas;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

class PetugasResource extends Resource
{
    protected static ?string $model = Petugas::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Data Petugas';
    protected static ?string $modelLabel = 'Petugas';
    protected static ?string $pluralModelLabel = 'Data Petugas';
    protected static ?string $navigationGroup = 'Manajemen User';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Identitas Petugas')
                    ->schema([
                        TextInput::make('nik')
                            ->label('NIK')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->numeric()
                            ->maxLength(20),
                        TextInput::make('nama_lengkap')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('nomor_telepon')
                            ->tel()
                            ->required()
                            ->maxLength(15),
                    ])->columns(2),

                Section::make('Penugasan & Keamanan')
                    ->schema([
                        // Dropdown Relasi ke Bendungan
                        Select::make('id_bendungan')
                            ->label('Ditugaskan di Bendungan')
                            ->relationship('bendungan', 'nama_bendungan')
                            ->searchable()
                            ->preload()
                            ->required(),

                        // Input Password (Otomatis Hash)
                        TextInput::make('password')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state)) // Hanya update jika diisi
                            ->required(fn (string $context): bool => $context === 'create'), // Wajib saat create saja

                        Toggle::make('status_aktif')
                            ->label('Status Aktif')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nik')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama_lengkap')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('bendungan.nama_bendungan') // Menampilkan nama bendungan
                    ->label('Lokasi Tugas')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nomor_telepon'),
                Tables\Columns\IconColumn::make('status_aktif')
                    ->boolean(),
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
            'index' => Pages\ListPetugas::route('/'),
            'create' => Pages\CreatePetugas::route('/create'),
            'edit' => Pages\EditPetugas::route('/{record}/edit'),
        ];
    }
}