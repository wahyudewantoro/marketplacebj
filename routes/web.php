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

Route::get('/', function () {
    return view('_welcome');
});



// Auth::routes();

//Route::get('/', 'HomeController@index')->name('home');
//Route::post('/payment', 'HomeController@payment')->name('home.payment');

/* 
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
 */

// route()