<?php

namespace App\Filament\Resources\BarcodesResource\Pages;

use App\Filament\Resources\BarcodesResource;
use Filament\Forms\Form;
use Filament\Forms;
use Filament\Resources\Pages\Page;
use App\Models\Barcodes;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CreateQr extends Page
{
    protected static string $resource = BarcodesResource::class;
    protected static string $view = 'filament.resources.barcode-resource.pages.create-qr';

    public $table_number;

    public function mount(): void
    {
        $this->form->fill();

        $usedNumbers = Barcodes::pluck('table_number')
            ->map(function ($number) {
                return (int) preg_replace('/[^\d]/', '', $number);
            })
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        $nextNumber = 1;

        foreach ($usedNumbers as $used) {
            if ($used != $nextNumber) {
                break;
            }
            $nextNumber++;
        }

        $this->table_number = 'T' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('table_number')
                    ->required()
                    ->default(fn() => $this->table_number)
                    ->disabled(),
            ]);
    }

    public function save(): void
    {
        // Validasi apakah table_number sudah ada
        if (Barcodes::where('table_number', $this->table_number)->exists()) {
            Notification::make()
                ->title('Duplicate Table Number')
                ->danger()
                ->body("Table number {$this->table_number} already exists.")
                ->send();
            return;
        }

        $host = $_SERVER['HTTP_HOST'] . '/' . $this->table_number;

        $svgContent = QrCode::margin(1)->size(200)->generate($host);
        $svgFilePath = 'qr_codes/' . $this->table_number . '.svg';
        Storage::disk('public')->put($svgFilePath, $svgContent);

        Barcodes::create([
            'table_number' => $this->table_number,
            'users_id' => Auth::user()->id,
            'image' => $svgFilePath,
            'qr_value' => $host
        ]);

        Notification::make()
            ->title('QR Code Created')
            ->success()
            ->icon('heroicon-o-check-circle')
            ->send();

        $this->redirect(url('admin/barcodes'));
    }
}