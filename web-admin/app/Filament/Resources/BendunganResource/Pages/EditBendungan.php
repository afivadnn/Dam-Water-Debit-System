<?php

namespace App\Filament\Resources\BendunganResource\Pages;

use App\Filament\Resources\BendunganResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBendungan extends EditRecord
{
    protected static string $resource = BendunganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
