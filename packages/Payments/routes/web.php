<?php

use Illuminate\Support\Facades\Route;
use CodeGarage\Payments\Presentation\Http\Controllers\PaymentController;

Route::post('/payments/payfast/notify', [PaymentController::class, 'payfastNotify'])->name('payfast.notify');
Route::get('/payments/payfast/return/{reference}', [PaymentController::class, 'payfastReturn'])->name('payfast.return');
Route::get('/payments/payfast/cancel/{reference}', [PaymentController::class, 'payfastCancel'])->name('payfast.cancel');

Route::middleware(['auth'])->prefix('payments')->group(function () {
    Route::get('/', [PaymentController::class, 'index'])->name('index');
    Route::get('/review', [PaymentController::class, 'review'])->name('review');
    Route::get('/checkout/{course:id}', [PaymentController::class, 'checkout'])->name('checkout');
    Route::post('/checkout/{course:id}/portal', [PaymentController::class, 'startPortal'])->name('portal');
    Route::post('/checkout/{course:id}/bank-transfer', [PaymentController::class, 'submitBankTransfer'])->name('bank-transfer');
    Route::post('/collect/{course:id}', [PaymentController::class, 'collectManual'])->name('collect');
    Route::post('/{payment}/mark-paid', [PaymentController::class, 'markPaid'])->name('mark-paid');
    Route::post('/{payment}/reject', [PaymentController::class, 'reject'])->name('reject');
    Route::post('/{payment}/send-reminder', [PaymentController::class, 'sendReminder'])->name('send-reminder');
    Route::get('/{payment}/proof', [PaymentController::class, 'downloadProof'])->name('proof');
});
