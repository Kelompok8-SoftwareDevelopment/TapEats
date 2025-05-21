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
        $this->table_number = strtoupper(chr(rand(65, 90)) . rand(1000, 9999));
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
        $host = $_SERVER['HTTP_HOST'] . '/' . $this->table_number;

        // Generate Qr Code (SVG IMAGE)
        $svgContent = QrCode::margin(1)->size(200)->generate($host);

        // File path buat nyimpen SVG
        $svgFilePath = 'qr_codes/' . $this->table_number . '.svg';

        // Save Gambarnya ke storage
        Storage::disk('public')->put($svgFilePath, $svgContent);

        // Save ke tabel barcode
        Barcodes::create([
            'table_number' => $this->table_number,
            'users_id' => Auth::user()->id,
            'image' => $svgFilePath,
            'qr_value' => $host
        ]);

        // Notifikasi 
        Notification::make()
            ->title('QR Code Created')
            ->success()
            ->icon('heroicon-o-check-circle')
            ->send();

        //Redirect ke barcode list
        $this->redirect(url('admin/barcodes'));
    }
}
