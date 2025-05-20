<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionItemsResource\Pages\ListTransactionItems;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    public static function getRecordTitle(?Model $record): string|null|Htmlable
    {
        return $record->name;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('code')->required()->maxLength(255),
            Forms\Components\TextInput::make('name')->required()->maxLength(255),
            Forms\Components\TextInput::make('phone')->tel()->required()->maxLength(255),
            Forms\Components\TextInput::make('external_id')->required()->maxLength(255),
            Forms\Components\TextInput::make('checkout_link')->required()->maxLength(255),
            Forms\Components\FileUpload::make('barcodes_id')
                ->label('QR Code')
                ->image()
                ->directory('qr_code')
                ->disk('public')
                ->default(fn ($record) => $record->barcodes->image ?? null),
            Forms\Components\TextInput::make('payment_method')->required(),
            Forms\Components\TextInput::make('payment_status')->required(),
            Forms\Components\TextInput::make('subtotal')->required()->numeric(),
            Forms\Components\TextInput::make('ppn')->required()->numeric(),
            Forms\Components\TextInput::make('total')->required()->numeric(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->label('Transaction Code')->searchable(),
                Tables\Columns\TextColumn::make('name')->label('Customer Name')->searchable(),
                Tables\Columns\TextColumn::make('phone')->label('Phone Number')->searchable(),
                Tables\Columns\ImageColumn::make('barcodes.image')->label('Barcode'),
                Tables\Columns\TextColumn::make('payment_method')->label('Payment Method')->searchable(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Payment Status')
                    ->badge()
                    ->colors([
                        'success' => fn ($state): bool => in_array($state, ['SUCCESS', 'PAID', 'SETTLED']),
                        'warning' => fn ($state): bool => $state === 'PENDING',
                        'danger' => fn ($state): bool => in_array($state, ['FAILED', 'EXPIRED']),
                    ]),
                Tables\Columns\TextColumn::make('subtotal')->label('Subtotal')->numeric()->money('IDR'),
                Tables\Columns\TextColumn::make('ppn')->label('PPN')->numeric()->money('IDR'),
                Tables\Columns\TextColumn::make('total')->label('Total')->numeric()->money('IDR'),
                Tables\Columns\TextColumn::make('created_at')->label('Created At')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')->label('Updated At')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Action::make('View Transaction')
                    ->color('success')
                    ->url(fn (Transaction $record): string => static::getUrl('view', ['record' => $record])),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
            'view' => Pages\ViewTransaction::route('/{record}/details'),
        ];
    }
}
