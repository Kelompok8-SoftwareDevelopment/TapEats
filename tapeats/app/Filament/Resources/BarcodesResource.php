<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BarcodesResource\Pages;
use App\Filament\Resources\BarcodesResource\RelationManagers;
use App\Models\Barcodes;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BarcodesResource extends Resource
{
    protected static ?string $model = Barcodes::class;

    protected static ?string $navigationIcon = 'heroicon-o-qr-code';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('table_number')
                    ->required()
                    ->default(fn() => strtoupper(chr(rand(65, 90)) . rand(1000, 9999))),
                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Select::make('users_id')
                    ->required()
                    ->relationship('users', 'name'),
                Forms\Components\TextInput::make('qr_value')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('table_number')
                    ->searchable(),
                // Tables\Columns\ImageColumn::make('image'),
                Tables\Columns\TextColumn::make('qr_value')
                    ->searchable(),
                Tables\Columns\TextColumn::make('users.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('Download QR Code')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($record) {
                        $filePath = storage_path('app/public/' . $record->image);
                        if (file_exists($filePath)) {
                            return response()->download($filePath);
                        }

                        session()->flash('error', 'QR code image not found.');
                    }),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListBarcodes::route('/'),
            'create' => Pages\CreateQr::route('/create'),
            'edit' => Pages\EditBarcodes::route('/{record}/edit'),
        ];
    }
}
