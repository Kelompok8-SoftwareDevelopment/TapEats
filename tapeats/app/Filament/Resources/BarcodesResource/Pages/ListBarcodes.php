<?php

namespace App\Filament\Resources\BarcodesResource\Pages;

use App\Filament\Resources\BarcodesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBarcodes extends ListRecords
{
    protected static string $resource = BarcodesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
