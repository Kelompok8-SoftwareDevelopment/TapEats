<?php

use App\Http\Middleware\CheckTableNumber;
use App\Livewire\Pages\AllMenuPage;
use App\Livewire\Pages\ScanPage;

use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

//Route::middleware(CheckTableNumber::class)->group(function(){
    // Semua Makanan | All Food
    Route::get('/food', AllMenuPage::class)->name('product.index');
//});

// Route::controller(QRController::class)->group(function (){
//     Route::post('/store-qr-result', 'storeResult')->name('product.scan.store');
//     // Scanner
//     Route::get('/scan', ScanPage::class)->name('product.scan');
//     Route::get('/{tableNumber}', 'checkCode')->name('product.scan.table');
// });