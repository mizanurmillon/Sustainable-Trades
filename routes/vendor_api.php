<?php

use App\Http\Controllers\Api\Auth\OnboardingController;
use App\Http\Controllers\Api\Auth\ShopImageAndBannerController;
use App\Http\Controllers\Api\Auth\ShopOwnerController;
use App\Http\Controllers\Api\MembershipController;
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

        Route::controller(ShopOwnerController::class)->group(function () {
            Route::post('/shop/owner-data-update', 'shopOwnerDataUpdate');
        });

        Route::controller(ShopImageAndBannerController::class)->group(function () {
            Route::post('/shop/image-update', 'shopImageUpdate');
            Route::post('/shop/banner-update', 'shopBannerUpdate');
        });

        Route::controller(ProductController::class)->group(function () {
            Route::post('/products-store', 'productStore');
            Route::get('/products', 'productList');
            Route::get('/product/{id}', 'productDetails', '');
            Route::get('/product/request-approval/{id}', 'productRequestApproval');
            Route::post('/product/update/{id}', 'productUpdate');
            Route::delete('/product/delete/{id}', 'productDelete');
            Route::delete('/image-delete/{id}', 'productImageDelete');
        });

        Route::controller(ImportExportController::class)->group(function () {
            Route::post('/import-products', 'importProducts');
            Route::get('/export-products/{id}', 'exportProducts');
        });

        Route::controller(ShopTaxController::class)->group(function () {
            Route::post('/shop-taxes', 'store');
        });

        Route::controller(DiscountController::class)->group(function () {
            Route::get('/discounts', 'index');
            Route::post('/discounts', 'store');
            Route::get('/discount/{id}', 'discount');
            Route::post('/discount-update/{id}', 'update');
            Route::delete('/delete-discount-codes', 'bulkDelete');
            Route::post('/status-discount-codes/{id}', 'status');
        });

        Route::controller(ShippingController::class)->group(function () {
            Route::post('/flat-rates', 'flatRateStore');
            Route::post('/weight_ranges', 'weightRangeStore');
            Route::get('/weight_ranges', 'weightRangeList');
            Route::delete('/weight_range/{id}', 'weightRangeDelete');
        });

        Route::controller(OnboardingController::class)->group(function () {
            Route::post('/paypal/onboard', 'onboard');
           
        });

        Route::controller(TradeOfferController::class)->group(function () {
            Route::post('/trade-offer/create', 'store');
            Route::get('/trade-offers', 'getTradeOffers');
            Route::get('/trade-offer/{id}', 'getTradeOffer');
            Route::get('/trade-offer-approve/{id}', 'approveTradeOffer');
            Route::get('/trade-offer-cancel/{id}', 'cancelTradeOffer');
            Route::get('/trade-count', 'getTradeCount');
            Route::post('/send-trade-counter-offer/{id}', 'sendTradeCounterOffer');
        });
        Route::controller(SpotlightApplicationController::class)->group(function () {
            Route::post('/spotlight-applications', 'store');
        });
    });

    Route::controller(MembershipController::class)->group(function () {
        Route::post('/membership/{id}', 'createMembership');
        Route::post('/membership/upgrade', 'upgradeMembership');
        Route::post('/membership/cancel', 'cancelMembership');
    });
});

Route::get('/trade-shop-product/{id}', [TradeOfferController::class, 'tradeShopProduct']);

Route::get('/paypal/onboard/success', [OnboardingController::class,'onboardSuccess'])->name('paypal.onboard.success');

Route::get('/paypal/onboard/cancel', [OnboardingController::class,'onboardCancel'])->name('paypal.onboard.cancel');


Route::controller(MembershipController::class)->group(function () {
    Route::get('/membership-success', 'success')->name('payments.paypal.success');
    Route::get('/membership-cancel', 'paypalCancel')->name('payments.cancel');
});
