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

Route::middleware(['basicAuth', 'logApi'])->group(function () {

    // routing bank jatim
    Route::post('inquiry', 'Api\InquiryController@index')->name('inquiry');
    Route::post('payment', 'Api\Paymentcontroller@index')->name('payment');
    Route::post('reversal', 'Api\ReversalController@index')->name('reversal');


    // routing kantor Pos
    Route::post('inquiry-pbb', 'Api\InquiryOtherController@index')->name('inquiry.pbb');
    Route::post('payment-pbb', 'Api\PaymentOtherController@index')->name('payment.pbb');
    Route::post('reversal-pbb', 'Api\ReversalOtherController@index')->name('reversal.pbb');

});
