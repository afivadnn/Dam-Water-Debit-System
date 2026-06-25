<?php

namespace App\Filament\Resources\PencatatanResource\Pages;

use App\Filament\Resources\PencatatanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPencatatan extends EditRecord
{
    protected static string $resource = PencatatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
