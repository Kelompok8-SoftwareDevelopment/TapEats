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

// Public access (tanpa CheckTableNumber)
Route::get('/scan', ScanPage::class)->name('product.scan');
Route::post('/store-qr-result', [QRController::class, 'storeResult'])->name('product.scan.store');

// Protected with CheckTableNumber middleware
Route::middleware(CheckTableNumber::class)->group(function () {

    // Halaman utama & makanan
    Route::get('/home', HomePage::class)->name('home');
    Route::get('/food', AllFoodPage::class)->name('product.index');
    Route::get('/food/favorite', FavoritePage::class)->name('product.favorite');
    Route::get('/food/promo', PromoPage::class)->name('product.promo');
    Route::get('/food/{id}', DetailPage::class)->name('product.detail');

    // Pembayaran
    Route::get('/cart', CartPage::class)->name('payment.cart');
    Route::get('/checkout', CheckoutPage::class)->name('payment.checkout');

    Route::middleware('throttle:10,1')->post('/payment', [TransactionController::class, 'handlePayment'])->name('payment');
    Route::get('/payment', fn() => abort(404));
    Route::get('/payment/status/{id}', [TransactionController::class, 'paymentStatus'])->name('payment.status');
    Route::get('/payment/success', PaymentSuccessPage::class)->name('payment.success');
    Route::get('/payment/failure', PaymentFailurePage::class)->name('payment.failure');
    Route::get('/payment/retry', [TransactionController::class, 'showRetry'])->name('payment.retry');
    Route::post('/payment/confirm', [TransactionController::class, 'retryPayment'])->name('payment.confirm');
    Route::get('/payment/handle', [TransactionController::class, 'handlePayment'])->name('payment.handle');
    Route::get('/payment/status/redirect/{id}', [TransactionController::class, 'handleStatusRedirect'])->name('payment.status.redirect');
});

// Webhook 
Route::post('/payment/webhook', [TransactionController::class, 'handleWebhook'])->name('payment.webhook');


// QR Code via direct link
Route::get('/{code}', [QRController::class, 'checkCode'])->name('product.scan.code');

// Route::controller(QRController::class)->group(function (){
//     Route::post('/store-qr-result', 'storeResult')->name('product.scan.store');
//     // Scanner
//     Route::get('/scan', ScanPage::class)->name('product.scan');
//     Route::get('/{tableNumber}', 'checkCode')->name('product.scan.table');
// });

Route::get('/transactions/{id}/download', [TransactionController::class, 'download'])->name('transactions.download');
