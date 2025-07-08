<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Vendor\ProductController;

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

    });

});