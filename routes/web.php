<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// Route to handle callback from Paynow
Route::any('/callback', "Api\\Payments\\PaynowController@statusUpdate");

// Vue SPA route
Route::get('/{any}', 'PagesController@index')->where('any', '.*');