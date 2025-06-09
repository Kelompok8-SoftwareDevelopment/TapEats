<?php

use App\Http\Controllers\TransactionController;
use App\Http\Controllers\QRController;
use App\Http\Middleware\CheckTableNumber;
use App\Livewire\Pages\AllFoodPage;
use App\Livewire\Pages\CartPage;
use App\Livewire\Pages\CheckoutPage;
use App\Livewire\Pages\DetailPage;
use App\Livewire\Pages\FavoritePage;
use App\Livewire\Pages\PromoPage;
use App\Livewire\Pages\HomePage;
use App\Livewire\Pages\PaymentFailurePage;
use App\Livewire\Pages\PaymentSuccessPage;
use App\Livewire\Pages\ScanPage;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

Route::get('/', function () {
    if (!session()->has('table_number')) {
        return redirect()->route('product.scan');
    }

    return redirect()->route('home');
});

// =====================
// Webhook 
// =====================
Route::post('/payment/webhook', [TransactionController::class, 'handleWebhook'])->name('payment.webhook');

// =====================
// Public access (tanpa CheckTableNumber)
// =====================
Route::get('/scan', ScanPage::class)->name('product.scan');
Route::post('/store-qr-result', [QRController::class, 'storeResult'])->name('product.scan.store');

// QR Code
Route::get('/{code}', [QRController::class, 'checkCode'])->name('product.scan.code');

// =====================
// Protected with CheckTableNumber middleware
// =====================
Route::middleware(CheckTableNumber::class)->group(function () {
    
    // Halaman utama & makanan
    Route::get('/', HomePage::class)->name('home');
    Route::get('/home', HomePage::class);
    Route::get('/food', AllFoodPage::class)->name('product.index');
    Route::get('/food/favorite', FavoritePage::class)->name('product.favorite');
    Route::get('/food/promo', PromoPage::class)->name('product.promo');
    Route::get('/food/{id}', DetailPage::class)->name('product.detail');

    // Pembayaran
    Route::get('/cart', CartPage::class)->name('payment.cart');
    Route::get('/checkout', CheckoutPage::class)->name('payment.checkout');

    Route::middleware('throttle:10,1')->post('/payment', [TransactionController::class, 'handlePayment'])->name('payment');
    Route::get('/payment', fn () => abort(404));
    Route::get('/payment/status/{id}', [TransactionController::class, 'paymentStatus'])->name('payment.status');
    Route::get('/payment/success', PaymentSuccessPage::class)->name('payment.success');
    Route::get('/payment/failure', PaymentFailurePage::class)->name('payment.failure');
});
