<?php

use App\Http\Controllers\Web\Backend\CategoryController;
use App\Http\Controllers\Web\Backend\DashboardController;
use App\Http\Controllers\Web\Backend\FaqController;
use App\Http\Controllers\Web\Backend\SubCategoryController;
use Illuminate\Support\Facades\Route;


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
