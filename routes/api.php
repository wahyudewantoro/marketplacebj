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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['basicAuth','logApi'])->group(function () {
    Route::post('inquiry', 'Api\InquiryController@index')->name('inquiry');
    // Route::get('inquirytest', 'Api\InquiryController@indexTest')->name('inquirytest');
    Route::post('payment', 'Api\Paymentcontroller@index')->name('payment');
    Route::post('reversal', 'Api\ReversalController@index')->name('reversal');
});
