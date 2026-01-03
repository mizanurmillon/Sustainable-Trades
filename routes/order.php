<?php

use App\Http\Controllers\Api\Order\MyOrderController;
use App\Http\Controllers\Api\Order\OrderController;
use App\Http\Controllers\Api\Order\PaymentController;
use Illuminate\Support\Facades\Route;





Route::group(['middleware' => ['jwt.verify']], function () {

    Route::group(['middleware' => ['vendor']], function () {

        Route::controller(OrderController::class)->group(function () {
            Route::get('/orders', 'index');
            Route::get('/order/{id}', 'show');
            Route::post('/order-status-update/{id}', 'updateStatus');
            Route::post('/order-note/{id}', 'addNote');
        });
    });

    Route::group(['middleware' => ['customer']], function () {
        Route::controller(MyOrderController::class)->group(function () {
            Route::get('/my-orders', 'index');
            Route::get('/my-order/{id}', 'show');
        });
    });

    Route::controller(MyOrderController::class)->group(function () {
        Route::get('/my-order/{id}/history', 'orderHistory');
        Route::post('/invoice-generate/{id}', 'generateInvoice');
    });


    Route::controller(PaymentController::class)->group(function () {
        Route::post('/checkout/{id}', 'checkout');
    });
});

Route::post('/paypal/capture', [PaymentController::class, 'paymentSuccess']);
Route::get('/payment-cancel/{order_id}', [PaymentController::class, 'paymentCancel'])->name('payment.cancel');
