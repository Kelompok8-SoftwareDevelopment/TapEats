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

// Halaman scan & simpan hasil scan (tanpa middleware)
Route::get('/scan', ScanPage::class)->name('product.scan');
Route::post('/store-qr-result', [QRController::class, 'storeResult'])->name('product.scan.store');

// ✅ Semua route di bawah hanya bisa diakses jika sudah scan barcode (session "table_number")
Route::middleware(CheckTableNumber::class)->group(function () {
    Route::get('/', HomePage::class)->name('home');
    Route::get('/food', AllFoodPage::class)->name('product.index');
    Route::get('/food/favorite', FavoritePage::class)->name('product.favorite');
    Route::get('/food/promo', PromoPage::class)->name('product.promo');
    Route::get('/food/{id}', DetailPage::class)->name('product.detail');

    Route::get('/cart', CartPage::class)->name('payment.cart');
    Route::get('/checkout', CheckoutPage::class)->name('payment.checkout');

    Route::middleware('throttle:10,1')->post('/payment', [TransactionController::class, 'handlePayment'])->name('payment');
    Route::get('/payment', fn () => abort(404));
    Route::get('/payment/status/{id}', [TransactionController::class, 'paymentStatus'])->name('payment.status');
    Route::get('/payment/success', PaymentSuccessPage::class)->name('payment.success');
    Route::get('/payment/failure', PaymentFailurePage::class)->name('payment.failure');
});

// Webhook (tanpa middleware)
Route::post('/payment/webhook', [TransactionController::class, 'handleWebhook'])->name('payment.webhook');

// Optional: QR route dari barcode langsung
Route::get('/{code}', [QRController::class, 'checkCode'])->name('product.scan.code');
