<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Backend\FaqController;
use App\Http\Controllers\Web\Backend\ShopController;
use App\Http\Controllers\Web\Backend\ListingController;
use App\Http\Controllers\Web\Backend\CategoryController;
use App\Http\Controllers\Web\Backend\DashboardController;
use App\Http\Controllers\Web\Backend\TutorialsController;
use App\Http\Controllers\Web\Backend\OurMissoinController;
use App\Http\Controllers\Web\Backend\ProMembersController;
use App\Http\Controllers\Web\Backend\SubCategoryController;
use App\Http\Controllers\Web\Backend\BasicMembersController;
use App\Http\Controllers\Web\Backend\SubscriptionPlanController;
use App\Http\Controllers\Web\Backend\SustainableShoppersController;
use App\Http\Controllers\Web\Backend\SpotlightApplicationController;



Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

//FAQ Routes
Route::controller(FaqController::class)->group(function () {
    Route::get('/faqs', 'index')->name('admin.faqs.index');
    Route::get('/faqs/create', 'create')->name('admin.faqs.create');
    Route::post('/faqs/store', 'store')->name('admin.faqs.store');
    Route::get('/faqs/edit/{id}', 'edit')->name('admin.faqs.edit');
    Route::post('/faqs/update/{id}', 'update')->name('admin.faqs.update');
    Route::post('/faqs/status/{id}', 'status')->name('admin.faqs.status');
    Route::post('/faqs/destroy/{id}', 'destroy')->name('admin.faqs.destroy');
});

// Route for the admin categories index
Route::controller(CategoryController::class)->group(function () {
    Route::get('/categories', 'index')->name('admin.categories.index');
    Route::get('/categories/create', 'create')->name('admin.categories.create');
    Route::post('/categories/store', 'store')->name('admin.categories.store');
    Route::get('/categories/edit/{id}', 'edit')->name('admin.categories.edit');
    Route::post('/categories/update/{id}', 'update')->name('admin.categories.update');
    Route::post('/categories/status/{id}', 'status')->name('admin.categories.status');
    Route::post('/categories/destroy/{id}', 'destroy')->name('admin.categories.destroy');
});

// Route for the admin sub-categories index
Route::controller(SubCategoryController::class)->group(function () {
    Route::get('/sub-categories', 'index')->name('admin.sub_categories.index');
    Route::get('/sub-categories/create', 'create')->name('admin.sub_categories.create');
    Route::post('/sub-categories/store', 'store')->name('admin.sub_categories.store');
    Route::get('/sub-categories/edit/{id}', 'edit')->name('admin.sub_categories.edit');
    Route::post('/sub-categories/update/{id}', 'update')->name('admin.sub_categories.update');
    Route::post('/sub-categories/status/{id}', 'status')->name('admin.sub_categories.status');
    Route::post('/sub-categories/destroy/{id}', 'destroy')->name('admin.sub_categories.destroy');
});

Route::controller(TutorialsController::class)->group(function () {
    Route::get('/tutorials', 'index')->name('admin.tutorials.index');
    Route::get('/tutorials/create', 'create')->name('admin.tutorials.create');
    Route::post('/tutorials/store', 'store')->name('admin.tutorials.store');
    Route::get('/tutorials/edit/{id}', 'edit')->name('admin.tutorials.edit');
    Route::post('/tutorials/update/{id}', 'update')->name('admin.tutorials.update');
    Route::post('/tutorials/status/{id}', 'status')->name('admin.tutorials.status');
    Route::post('/tutorials/destroy/{id}', 'destroy')->name('admin.tutorials.destroy');
});

//Route for the listing requests

Route::controller(ListingController::class)->group(function () {
    Route::get('/listings', 'index')->name('admin.listing_requests.index');
    Route::get('/listings/{id}', 'show')->name('admin.product.show');
    Route::post('/admin/products/{id}/approve', 'approve')->name('admin.product.approve');
    Route::post('/admin/products/{id}/reject', 'reject')->name('admin.product.reject');
});

//Route for the members spotlight
Route::controller(SpotlightApplicationController::class)->group(function () {
    Route::get('/member-spotlight', 'index')->name('admin.members_spotlight.index');
    Route::get('/member-spotlight/{id}', 'show')->name('admin.members_spotlight.show');
    Route::post('/admin/application/{id}/approve', 'approve')->name('admin.application.approve');
    Route::post('/admin/application/{id}/pending', 'pending')->name('admin.application.pending');
    Route::delete('/admin/application/{id}', 'destroy')->name('admin.application.destroy');
});

//Route for the shops
Route::controller(ShopController::class)->group(function () {
    Route::get('/shops', 'index')->name('admin.shops.index');
    Route::post('/shops/{id}/featured', 'featured')->name('admin.shops.featured');
});


//subscription plan routes
Route::controller(SubscriptionPlanController::class)->group(function () {
    Route::get('/subscription-plans', 'index')->name('admin.subscription.index');
    Route::get('/subscription-plans/create', 'create')->name('admin.subscription.create');
    Route::post('/subscription-plans/store', 'store')->name('admin.subscription.store');
    Route::get('/subscription-plans/edit/{id}', 'edit')->name('admin.subscription.edit');
    Route::post('/subscription-plans/update/{id}', 'update')->name('admin.subscription.update');
    Route::post('/subscription-plans/destroy/{id}', 'destroy')->name('admin.subscription.destroy');

    Route::get('/subscription-plans/benefit/delete/{id}', 'deleteBenefit')->name('admin.subscription.benefit.delete');
    Route::get('/subscription-plans/benefit/edit/{id}', 'editBenefit')->name('admin.subscription.benefit.edit');
    Route::post('/subscription-plans/benefit/update/{id}', 'updateBenefit')->name('admin.subscription.benefit.update');
});

//Our mission route
Route::controller(OurMissoinController::class)->group(function () {
    Route::get('/our-mission', 'index')->name('admin.our_missions.index');
    Route::get('/our-mission/create', 'create')->name('admin.our_missions.create');
    Route::post('/our-mission/store', 'store')->name('admin.our_missions.store');
    Route::get('/our-mission/edit/{id}', 'edit')->name('admin.our_missions.edit');
    Route::post('/our-mission/update/{id}', 'update')->name('admin.our_missions.update');
    Route::delete('/our-mission/destroy/{id}', 'destroy')->name('admin.our_missions.destroy');
    Route::post('/our-mission/status/{id}', 'status')->name('admin.our_missions.status');
});

//Pro Members route
Route::controller(ProMembersController::class)->group(function () {
    Route::get('/pro-members', 'index')->name('admin.pro_members.index');
});

//Basic_members route
Route::controller(BasicMembersController::class)->group(function () {
    Route::get('/basic-members', 'index')->name('admin.basic_members.index');
});

//sustainable_shoppers route
Route::controller(SustainableShoppersController::class)->group(function () {
    Route::get('/sustainable-shoppers', 'index')->name('admin.sustainable_shoppers.index');
    Route::post('/sustainable-shoppers/status/{id}', 'status')->name('admin.sustainable_shoppers.status');
    Route::delete('/sustainable-shoppers/destroy/{id}', 'destroy')->name('admin.sustainable_shoppers.destroy');
});
