<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;

use App\Order;
use App\OrderSucursal;

Auth::routes(['register' => false]);
Route::get('/pdf', 'PdfController@getIndex')->middleware('auth');

Route::get('/', 'HomeController@index')->name('home')->middleware('auth');
Route::get('/home', 'HomeController@index')->name('home')->middleware('auth');

/*************************/
/***** ORDERS ROUTES ****/
/***********************/

Route::get('/order', function (Request $request) {
    return view('order.home');
})->middleware('auth');

Route::get('/order/payment_methods', function (Request $request) {
    return view('order.payment_methods');
})->middleware('auth');

Route::get('/order/{id}', function (Request $request) {
    return view('order.detail');
})->middleware('auth');

/*************************/
/** MARKETPLACE ROUTES **/
/***********************/
Route::get('/marketplace', function (Request $request) {
    return view('marketplace.home');
})->name('marketplace')->middleware('auth');
Route::get('/marketplace/orders', function (Request $request) {
    return view('marketplace.orders');
})->middleware('auth');
Route::get('marketplace/order/{id}', function (Request $request) {
    return view('marketplace.detail');
})->middleware('auth');

/*************************/
/****** SELL ROUTES *****/
/***********************/
Route::get('/sell', 'OrderController@index')->middleware('auth');

/*************************/
/***** STOCK ROUTES *****/
/***********************/

Route::get('/stock', function (Request $request) {
    return view('stock.home');
})->middleware('auth');
Route::get('/stock/movements', function (Request $request) {
    return view('stock.movements');
})->middleware('auth');
Route::get('/stock/movements/{id}', function (Request $request) {
    return view('stock.movements_detail');
})->middleware('auth');
Route::get('/stock/movements/print/{id}', 'PdfController@printMovement')->middleware('auth');
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
Route::get('/stock/label', function (Request $request) {
    return view('stock.label');
})->middleware('auth');
Route::get('/stock/reset', function (Request $request) {
    return view('stock.reset');
})->middleware('auth');
Route::get('/stock/reset/processed', 'PdfController@getStockResetProcessed')->middleware('auth');
Route::get('/stock/reset/unprocessed', 'PdfController@getStockResetUnProcessed')->middleware('auth');


/*************************/
/* CASH REGISTER ROUTES */
/***********************/
Route::get('/cash', function (Request $request) {    
    return view('cash.home');
})->name('cash')->middleware('auth');

/*************************/
/* CREDIT NOTES ROUTES */
/***********************/
Route::get('/creditNote', function (Request $request) {    
    return view('creditNote.home');
})->name('creditNote')->middleware('auth');