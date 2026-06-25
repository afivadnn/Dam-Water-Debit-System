<?php

namespace App\Filament\Resources\BendunganResource\Pages;

use App\Filament\Resources\BendunganResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBendungans extends ListRecords
{
    protected static string $resource = BendunganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
