<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;

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

Route::get('/pdf', 'PdfController@getIndex');

Route::get('/stock', 'StockController@index')->name('stock.home');
Route::get('/stock/movements', function (Request $request) {
    return view('stock.movements');
});
Route::get('/stock/list', function (Request $request) {
    return view('stock.list');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');