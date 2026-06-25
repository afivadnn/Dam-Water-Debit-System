<?php

namespace App\Filament\Resources\PerhitunganDebitResource\Pages;

use App\Filament\Resources\PerhitunganDebitResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPerhitunganDebit extends EditRecord
{
    protected static string $resource = PerhitunganDebitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
