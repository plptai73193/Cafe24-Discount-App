<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Middleware\Cafe24Auth;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});



Route::middleware(Cafe24Auth::class)->prefix('v1')->namespace("Api")->group(function() {
    /* mall endpoints */
    Route::prefix('mall')->group(function() {
        //Endpoint: api/v1/mall/store
        Route::post('store', 'MallController@store')->name('mall.store');

        //Endpoint: api/v1/mall/cafe24api
        Route::post('cafe24api', 'MallController@CAFE24API')->name('mall.store');




        //Endpoint: api/v1/mall/discountprice
        Route::post('discountprice', 'ProductController@getDiscountPrice')->name('mall.discountprice');
    });
});