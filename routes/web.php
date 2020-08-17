<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;

use App\Order;
use App\OrderSucursal;

Auth::routes(['register' => false]);
Route::get('/pdf', 'PdfController@getIndex')->middleware('auth');

Route::get('/', 'HomeController@index')->name('home')->middleware('auth');

Route::get("g", 'TiendaNubeController@index');

/*************************/
/****** SELL ROUTES *****/
/***********************/
Route::get('/sell', 'OrderController@index')->middleware('auth');
Route::get('/order/payment_methods', function (Request $request) {
    return view('order.payment_methods');
})->middleware('auth');



/*************************/
/***** STOCK ROUTES *****/
/***********************/

Route::get('/stock', 'StockController@index')->name('stock.home')->middleware('auth');
Route::get('/stock/movements', function (Request $request) {
    return view('stock.movements');
})->middleware('auth');
Route::get('/stock/list', function (Request $request) {
    return view('stock.list');
})->middleware('auth');
Route::get('/stock/brands', function (Request $request) {
    return view('stock.brands');
})->middleware('auth');
Route::get('/stock/colors', function (Request $request) {
    return view('stock.colors');
})->middleware('auth');
Route::get('/stock/articles', function (Request $request) {
    return view('stock.articles');
})->middleware('auth');



/*************************/
/* CASH REGISTER ROUTES */
/***********************/
Route::get('/cash', function (Request $request) {    
    return view('cash.home');
})->middleware('auth');