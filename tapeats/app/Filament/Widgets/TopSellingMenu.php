<?php

namespace App\Filament\Widgets;

use App\Models\TransactionItems;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class TopSellingMenu extends BaseWidget
{
    protected static ?string $heading = 'Top Selling Menu';

    public  function getTableRecordKey($record): string
    {
        return (string) $record->foods_id;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                TransactionItems::query()
                    ->select('foods_id', DB::raw('SUM(quantity) as total_quantity'))
                    ->groupBy('foods_id')
                    ->orderByDesc('total_quantity')
                    ->with('food') // pastikan relasi ini ada di model TransactionItems
            )
            ->columns([
                Tables\Columns\TextColumn::make('food.name')
                    ->label('Nama Menu')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('total_quantity')
                    ->label('Total Terjual')
                    ->sortable(),
            ]);
    }
}
