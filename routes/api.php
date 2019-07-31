<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => 'api', 'prefix' => 'v1'], function() {
    Route::group(['prefix' => 'payment'], function() {
        Route::post('/initiate', "Api\\Payments\\PaynowController@initExpress");
        Route::post('/poll', "Api\\Payments\\PaynowController@pollTransaction");
    });

    Route::resource('foods', "Api\\FoodItemController");
});
