<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionItemsResource\Pages\CreateTransactionItems;
use App\Filament\Resources\TransactionItemsResource\Pages\EditTransactionItems;
use App\Filament\Resources\TransactionItemsResource\Pages\ListTransactionItems;

use App\Models\TransactionItems;
use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Route;
use App\Exports\TransactionsExport;
use Maatwebsite\Excel\Facades\Excel;

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
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('external_id')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('checkout_link')
                    ->required()
                    ->maxLength(255),
                Forms\Components\FileUpload::make('barcodes_id')
                    ->label('QR Code')
                    ->image() // Hanya menerima file gambar
                    ->directory('qr_code') // Direktori penyimpanan
                    ->disk('public') // Disk penyimpanan
                    ->default(function ($record) {
                        return $record->barcodes->image ?? null;
                    }),
                Forms\Components\TextInput::make('payment_method')
                    ->required(),
                Forms\Components\TextInput::make('payment_status')
                    ->required(),
                Forms\Components\TextInput::make('subtotal')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('ppn')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('total')
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Transaction Code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('barcodes_id')
                    ->label('Table Number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Customer Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone Number')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Payment Method')
                    ->searchable(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Payment Status')
                    ->badge()
                    ->colors([
                        'success' => fn($state): bool => in_array($state, ['SUCCESS', 'PAID', 'SETTLED']),
                        'warning' => fn($state): bool => $state === 'PENDING',
                        'danger' => fn($state): bool => in_array($state, ['FAILED', 'EXPIRED']),
                    ]),
                Tables\Columns\TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->numeric()
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('ppn')
                    ->label('PPN')
                    ->numeric()
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->numeric()
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Transaction Time')
                    ->dateTime()
                    ->sortable()

            ])
            ->filters([
                // filter berdasarkan pembayaran
                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Payment Method')
                    ->options([
                        'CASH' => 'Cash',
                        'QRIS' => 'QRIS',
                        'EWALLET_DANA' => 'DANA',
                        'EWALLET_OVO' => 'OVO',
                        'EWALLET_LINKAJA' => 'LinkAja',
                        'EWALLET_SHOPEEPAY' => 'ShopeePay',
                        'BANK_BCA' => 'Bank Transfer - BCA',
                        'BANK_BNI' => 'Bank Transfer - BNI',
                        'BANK_MANDIRI' => 'Bank Transfer - Mandiri',
                        'BANK_BRI' => 'Bank Transfer - BRI',
                        'BANK_PERMATA' => 'Bank Transfer - Permata',
                        'VIRTUAL_ACCOUNT' => 'Virtual Account (VA)',
                        'RETAIL_ALFAMART' => 'Alfamart',
                        'RETAIL_INDOMARET' => 'Indomaret',
                        'CARD' => 'Credit/Debit Card',
                    ])
                    ->searchable(),

                // Filter berdasarkan status pembayaran
                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Payment Status')
                    ->options([
                        'PENDING' => 'Pending',
                        'PAID' => 'Paid',
                        'EXPIRED' => 'Expired',
                        'FAILED' => 'Failed',
                    ])
                    ->searchable(),

                // Filter perminggu
                Tables\Filters\Filter::make('This Week')
                    ->label('This Week')
                    ->query(fn($query) => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])),

                // Filter perbulan
                Tables\Filters\Filter::make('This Month')
                    ->label('This Month')
                    ->query(fn($query) => $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])),

                // Custom Range Tanggal
                Tables\Filters\Filter::make('Custom Date')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('From'),
                        Forms\Components\DatePicker::make('until')->label('Until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn($q) => $q->whereDate('created_at', '>=', $data['from']))
                            ->when($data['until'], fn($q) => $q->whereDate('created_at', '<=', $data['until']));
                    }),
            ])
            ->filtersFormColumns(2)

            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('See transaction')
                    ->color('success')
                    ->url(
                        fn(Transaction $record): string => static::getUrl('transaction-items.index', [
                            'parent' => $record->id,
                        ])
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('exportExcel')
                    ->label('Export to Excel')
                    ->action(function ($records) {
                        return Excel::download(new TransactionsExport($records), 'selected-transactions.xlsx');
                    })
                    ->deselectRecordsAfterCompletion(),
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
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),

            'transaction-items.index' => ListTransactionItems::route('/{parent}/transaction'),

        ];
    }
}
