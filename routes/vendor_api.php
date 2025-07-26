<?php

use App\Http\Controllers\Api\Auth\OnboardingController;
use App\Http\Controllers\Api\Product\ImportExportController;
use App\Http\Controllers\Api\TradeOfferController;
use App\Http\Controllers\Api\Vendor\DiscountController;
use App\Http\Controllers\Api\Vendor\ProductController;
use App\Http\Controllers\Api\Vendor\ShippingController;
use App\Http\Controllers\Api\Vendor\ShopTaxController;
use App\Http\Controllers\Api\Vendor\SpotlightApplicationController;
use Illuminate\Support\Facades\Route;


Route::group(['middleware' => ['jwt.verify']], function () {

    Route::group(['middleware' => ['vendor']], function () {

        Route::controller(ProductController::class)->group(function () {
            Route::post('/products-store', 'productStore');
            Route::get('/products', 'productList');
            Route::get('/product/{id}', 'productDetails', '');
            Route::get('/product/request-approval/{id}', 'productRequestApproval');
            Route::post('/product/update/{id}', 'productUpdate');
            Route::delete('/product/delete/{id}', 'productDelete');
        });

        Route::controller(ImportExportController::class)->group(function () {
            Route::post('/import-products', 'importProducts');
            Route::get('/export-products/{id}', 'exportProducts');
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

        Route::controller(OnboardingController::class)->group(function () {
            Route::get('/paypal/onboard', 'onboard');
            Route::get('/paypal/onboard/success', 'onboardSuccess')->name('paypal.success');
        });

        Route::controller(TradeOfferController::class)->group(function () {
            Route::post('/trade-offer/create', 'store');
            route::get('/trade-offers', 'getTradeOffers');
            Route::get('/trade-offer-approve/{id}', 'approveTradeOffer');
            Route::get('/trade-offer-cancel/{id}', 'cancelTradeOffer');
            Route::get('/trade-count', 'getTradeCount');
            Route::post('/send-trade-counter-offer/{id}', 'sendTradeCounterOffer');
        });
    });
    
    Route::controller(SpotlightApplicationController::class)->group(function () {
        Route::post('/spotlight-applications', 'store');
    });
});
