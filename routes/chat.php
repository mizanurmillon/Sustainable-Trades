<?php 

use App\Http\Controllers\Api\Chat\GetConversationController;
use App\Http\Controllers\Api\Chat\GetMessageController;
use App\Http\Controllers\Api\Chat\SendMessageController;
use Illuminate\Support\Facades\Route;





Route::group(['middleware' => ['jwt.verify']], function () {

    Route::post('/message/send', SendMessageController::class);

    Route::get('/message', GetMessageController::class);

    Route::get('/conversation', GetConversationController::class);

});