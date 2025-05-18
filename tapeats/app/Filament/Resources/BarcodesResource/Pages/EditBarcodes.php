<?php

namespace App\Filament\Resources\BarcodesResource\Pages;

use App\Filament\Resources\BarcodesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBarcodes extends EditRecord
{
    protected static string $resource = BarcodesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
