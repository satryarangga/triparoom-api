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

Route::get('token', 'AjaxController@getToken');
Route::get('promo', 'AjaxController@getHotelPromo');
Route::get('detailhotel', 'AjaxController@getHotelDetail');
Route::get('order', 'AjaxController@getHotelOrder');
Route::get('addOrder', 'AjaxController@addHotelOrder');
Route::get('checkout', 'AjaxController@checkoutCustomer');
Route::get('airport', 'AjaxController@getAirport');
Route::get('flight', 'AjaxController@getFlight');
Route::get('flight-detail', 'AjaxController@getFlightData');
Route::get('addFlightOrder', 'AjaxController@addFlightOrder');
Route::get('checkout-flight', 'AjaxController@checkoutFlight');
