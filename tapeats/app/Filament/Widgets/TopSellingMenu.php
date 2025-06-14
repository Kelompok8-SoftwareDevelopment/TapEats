<?php

namespace App\Filament\Widgets;

use App\Models\TransactionItems;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TopSellingMenu extends BaseWidget
{
    protected static ?string $heading = 'Top Selling Menu';
    public static function canView(): bool
    {
        return Auth::user()->isOwner();
    }
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
                    ->whereHas('transaction', function ($query) {
                        $query->where('payment_status', 'PAID');
                    })
                    ->groupBy('foods_id')
                    ->orderByDesc('total_quantity')
                    ->with('food')
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
