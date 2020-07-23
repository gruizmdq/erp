<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;

use App\Order;
use App\OrderSucursal;

Auth::routes(['register' => false]);
Route::get('/pdf', 'PdfController@getIndex')->middleware('auth');

Route::get('/home', 'StockController@index')->name('home')->middleware('auth');


/*************************/
/****** SELL ROUTES *****/
/***********************/
Route::get('/order', function(Request $request) {
    $a = new Order();
    $b = 
    $a = Order::find(1)->orderable; 
    $b = OrderSucursal::find(1)->order;
    
    return $a;
});
Route::get('/sell', function (Request $request) {
    return view('sell.home');
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

