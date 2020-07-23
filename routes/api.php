<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Sucursal;
use App\ShoeDetail;
use App\ShoeSucursalItem;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::middleware('auth:api')->post('/pdf/generate','PdfController@generatePdf');

Route::middleware('auth:api')->get('/get_sucursals', function () {
    return response()->json(Sucursal::get());
});

/*************************/
/** UTILS STOCK ROUTES **/
/***********************/
//GET
Route::get('/get_users', 'UserController@get_users');

/*************************/
/*** ORDER STOCK ROUTES */
/***********************/
Route::middleware('auth:api')->get('/order/get_payment_methods', 'OrderController@get_payment_methods');

/*************************/
/*** API STOCK ROUTES ***/
/***********************/
//GET
Route::middleware('auth:api')->get('/stock/get_brands', 'StockController@get_brands');
Route::middleware('auth:api')->get('/stock/get_colors', 'StockController@get_colors');
Route::middleware('auth:api')->get('/stock/get_categories', 'StockController@get_categories');
Route::middleware('auth:api')->get('/stock/get_numbers', 'StockController@get_numbers');
Route::middleware('auth:api')->get('/stock/get_articles', 'StockController@get_articles');
Route::middleware('auth:api')->get('/stock/get_articles/{id}', 'StockController@get_articles_id');
Route::middleware('auth:api')->get('/stock/get_detail_item', 'StockController@get_detail_item');
Route::middleware('auth:api')->get('/stock/get_detail_item_barcode', 'StockController@get_detail_item_barcode');
Route::middleware('auth:api')->get('/stock/get_list_stock', 'StockController@get_list_stock');
Route::middleware('auth:api')->get('/stock/get_movements', 'StockController@get_movements');

//POST
Route::middleware('auth:api')->post('/stock/new_brand', 'StockController@new_brand');
Route::middleware('auth:api')->post('/stock/new_article', 'StockController@new_article');
Route::middleware('auth:api')->post('/stock/new_color', 'StockController@new_color');
Route::middleware('auth:api')->post('/stock/delete_colors', 'StockController@delete_colors');
Route::middleware('auth:api')->post('/stock/edit_color', 'StockController@edit_color');
Route::middleware('auth:api')->post('/stock/delete_items', 'StockController@delete_items');
Route::middleware('auth:api')->post('/stock/update_items', 'StockController@update_items');
Route::middleware('auth:api')->post('/stock/add_movements', 'StockController@add_movements');
