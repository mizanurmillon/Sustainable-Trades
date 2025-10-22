<?php

use App\Http\Controllers\Api\Order\OrderController;
use App\Http\Controllers\Api\Order\PaymentController;
use Illuminate\Support\Facades\Route;




Route::group(['middleware' => ['jwt.verify']], function () {

    Route::controller(OrderController::class)->group(function () {
        
    });

    Route::controller(PaymentController::class)->group(function () {
       Route::post('/checkout/{id}', 'checkout');
    });

});

Route::post('/payment-success', [PaymentController::class, 'paymentSuccess'])->name('payment.success');
Route::post('/payment-cancel', [PaymentController::class, 'paymentCancel'])->name('payment.cancel');