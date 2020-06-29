<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Sucursal;
use App\ShoeDetail;
use App\ShoeSucursalItem;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('/pdf/generate','PdfController@generatePdf');

Route::get('/get_sucursals', function () {
    return response()->json(Sucursal::get());
});

/*************************/
/*** API STOCK ROUTES ***/
/***********************/
//GET
Route::get('/stock/get_brands', 'StockController@get_brands');
Route::get('/stock/get_colors', 'StockController@get_colors');
Route::get('/stock/get_categories', 'StockController@get_categories');
Route::get('/stock/get_numbers', 'StockController@get_numbers');
Route::get('/stock/get_articles', 'StockController@get_articles');
Route::get('/stock/get_articles/{id}', 'StockController@get_articles_id');
Route::get('/stock/get_detail_item', 'StockController@get_detail_item');
Route::get('/stock/get_list_stock', 'StockController@get_list_stock');
Route::get('/stock/get_movements', 'StockController@get_movements');

//POST
Route::post('/stock/new_brand', 'StockController@new_brand');
Route::post('/stock/new_article', 'StockController@new_article');
Route::post('/stock/new_color', 'StockController@new_color');
Route::post('/stock/update_items', 'StockController@update_items');
Route::post('/stock/delete_items', 'StockController@delete_items');