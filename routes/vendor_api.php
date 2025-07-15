<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Vendor\ProductController;
use App\Http\Controllers\Api\Vendor\ShopTaxController;
use App\Http\Controllers\Api\Vendor\DiscountController;
use App\Http\Controllers\Api\Vendor\ShippingController;

Route::group(['middleware' => ['jwt.verify']], function () {

    Route::group(['middleware' => ['vendor']], function () {

       Route::controller(ProductController::class)->group(function () {
            Route::post('/products-store', 'productStore');
            Route::get('/products', 'productList');
            Route::get('/product/{id}', 'productDetails','');
            Route::get('/product/request-approval/{id}', 'productRequestApproval');
            Route::post('/product/update/{id}', 'productUpdate');
            Route::delete('/product/delete/{id}', 'productDelete');
        });

        Route::controller(ShopTaxController::class)->group(function () {
            Route::post('/shop-taxes', 'store');
        });

        Route::controller(DiscountController::class)->group(function () {
            Route::post('/discounts', 'store');
        });

        Route::controller(ShippingController::class)->group(function () {
            Route::post('/flat-rates', 'flatRateStore');
            Route::post('/weight_ranges', 'weightRangeStore');
            Route::delete('/weight_range/{id}', 'weightRangeDelete');

        });

    });

});