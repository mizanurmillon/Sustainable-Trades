<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\Cart\CartController;
use App\Http\Controllers\Api\Shop\ShopController;
use App\Http\Controllers\Api\TopVendorController;
use App\Http\Controllers\Api\TutorialsController;
use App\Http\Controllers\Api\AllProductController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\FollowShopController;
use App\Http\Controllers\Api\MyFavoriteController;
use App\Http\Controllers\Api\NewsletterController;
use App\Http\Controllers\Api\OurMissionController;
use App\Http\Controllers\Api\SocialAuthController;
use App\Http\Controllers\Api\SocialLinkController;
use App\Http\Controllers\Api\DynamicPageController;
use App\Http\Controllers\Api\SitesettingController;
use App\Http\Controllers\Api\UserSettingController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\ShopOwnerController;
use App\Http\Controllers\Api\GetNotificationController;
use App\Http\Controllers\Api\Product\ProductController;
use App\Http\Controllers\Api\SubscriptionPlanController;
use App\Http\Controllers\Api\Vendor\SpotlightApplicationController;




/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


//Social Login
Route::post('/social-login', [SocialAuthController::class, 'socialLogin']);

//Register API
Route::controller(RegisterController::class)->prefix('users/register')->group(function () {
    // User Register
    Route::post('/', 'userRegister');

    // Verify OTP
    Route::post('/otp-verify', 'otpVerify');

    // Resend OTP
    Route::post('/otp-resend', 'otpResend');
});

//Shop Owner Register API
Route::controller(ShopOwnerController::class)->prefix('shop/owners')->group(function () {
    Route::post('/', 'shopOwnerRegister');
});

//Login API
Route::controller(LoginController::class)->prefix('users/login')->group(function () {

    // User Login
    Route::post('/', 'userLogin');

    // Verify Email
    Route::post('/email-verify', 'emailVerify');

    // Resend OTP
    Route::post('/otp-resend', 'otpResend');

    // Verify OTP
    Route::post('/otp-verify', 'otpVerify');

    //Reset Password
    Route::post('/reset-password', 'resetPassword');
});

Route::controller(SitesettingController::class)->group(function () {
    Route::get('/site-settings', 'siteSettings');
});

//Dynamic Page
Route::controller(DynamicPageController::class)->group(function () {
    Route::get('/dynamic-pages', 'dynamicPages');
    Route::get('/dynamic-pages/single/{slug}', 'single');
});

//Social Links
Route::controller(SocialLinkController::class)->group(function () {
    Route::get('/social-links', 'socialLinks');
});

//FAQ APIs
Route::controller(FaqController::class)->group(function () {
    Route::get('/faq/all', 'FaqAll');
});

Route::group(['middleware' => ['jwt.verify']], function () {

    Route::controller(UserController::class)->prefix('users')->group(function () {
        Route::get('/data', 'userData');
        Route::post('/data/update', 'userUpdate');
        Route::post('/password/change', 'passwordChange');
        Route::post('/logout', 'logoutUser');
        Route::delete('/delete', 'deleteUser');
    });

    Route::controller(UserSettingController::class)->group(function () {
        Route::post('/push-notification/setting', 'pushNotificationSetting');
        Route::post('/cookies-setting', 'cookiesSetting');
    });

    Route::controller(GetNotificationController::class)->prefix('notifications')->group(function () {
        Route::get('/', 'getNotifications');
    });

    Route::group(['middleware' => ['customer']], function () {

        Route::controller(CartController::class)->group(function () {
            Route::post('/add-to-cart/{id}', 'addToCart');
            route::get('/cart', 'getCart');
            route::post('/cart/update/{id}', 'updateCart');
            route::delete('/cart/item/remove/{id}', 'deleteCartItem');
            Route::delete('/cart/empty', 'emptyCart');
            Route::delete('/cart/remove/{id}', 'deleteCart');
        });
    });

    Route::controller(MyFavoriteController::class)->group(function () {
            Route::get('/my-favorites', 'myFavorites');
            Route::post('/add-favorites/{id}', 'addFavorite',);
        });

    Route::controller(FollowShopController::class)->group(function () {
        Route::get('/follow-shops', 'followShops');
        Route::post('/follow-shop/{id}', 'followShop');
    });
});



Route::group(['middleware' => ['guest']], function () {
    //Shop Owner APIs
    Route::controller(ShopController::class)->group(function () {
        Route::get('/shops', 'allShops');
        Route::get('/nearby-product','nearbyProduct');
        Route::get('/shops/featured', 'featuredShops');
        Route::get('/shop/{id}', 'shopDetails');
        Route::get('/shop/products/{id}', 'shopProducts');
        Route::get('/shop/products/featured/{id}', 'shopFeaturedProducts');
    });

    //Category wise products
    Route::controller(ProductController::class)->group(function () {
        Route::get('/category/wise/products', 'allProducts');
        Route::get('/product-details/{id}', 'singleProduct');
    });

    Route::controller(CategoryController::class)->group(function () {
        Route::get('/categories', 'categories');
        Route::get('/category/{id}', 'singleCategory');
        Route::get('/category-and-subcategories', 'categoryAndSubCategories');
        Route::get('/sub-categories', 'subCategories');
    });

    Route::controller(AllProductController::class)->group(function () {
        Route::get('/all-products', 'allProducts');
        Route::get('/is-featured-product', 'isFeaturedProduct');
    });

    Route::controller(TopVendorController::class)->group(function () {
        Route::get('/top-vendors', 'topVendors');
    });
});

Route::controller(NewsletterController::class)->group(function () {
    Route::post('/newsletter/subscribe', 'subscribe');
});

Route::controller(OurMissionController::class)->group(function () {
    Route::get('/our-mission', 'ourMission');
});

Route::controller(SubscriptionPlanController::class)->group(function () {
    Route::get('/subscriptions', 'subscriptions');
});

Route::controller(BannerController::class)->group(function () {
    Route::get('/banners', 'banners');
    Route::get('/how-it-works', 'howItWorks');
});

Route::controller(TutorialsController::class)->group(function () {
    Route::get('/tutorials', 'tutorials');
});

Route::controller(ContactController::class)->group(function () {
    Route::get('/contact', 'contact');
    Route::get('/terms-and-conditions', 'termsAndConditions');
    Route::get('/infringement-report', 'InfringementReport');
});

Route::controller(SpotlightApplicationController::class)->group(function () {
    Route::get('/spotlight-applications', 'index');
});