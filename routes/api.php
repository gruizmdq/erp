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

Route::middleware('auth:api')->get('/ucursals', function () {
    return response()->json(Sucursal::get());
});

/*************************/
/** UTILS STOCK ROUTES **/
/***********************/
//GET
Route::get('/users', 'UserController@get_users');



/*************************/
/*** ORDER STOCK ROUTES */
/***********************/
//GET
Route::middleware('auth:api')->get('/order/payment_methods', 'OrderController@get_payment_methods');
Route::middleware('auth:api')->get('/order/payment_method_cards', 'OrderController@get_payment_method_cards');
Route::middleware('auth:api')->get('/order/payment_method_card_options', 'OrderController@get_payment_method_card_options');

//POST
Route::middleware('auth:api')->post('/order', 'OrderController@new_order');
Route::middleware('auth:api')->post('/order/reset', 'OrderController@new_reset');

/*************************/
/* CASH REGISTER ROUTES */
/***********************/
//GET
Route::middleware('auth:api')->get('/cash/cash_register', 'CashRegisterController@get_cash_register');
Route::middleware('auth:api')->get('/cash/turn/cash', 'CashRegisterController@get_total_turn_cash');

//POST
Route::middleware('auth:api')->post('/cash/cash_register', 'CashRegisterController@new_cash_register');
Route::middleware('auth:api')->post('/cash/turn', 'CashRegisterController@new_turn');
Route::middleware('auth:api')->put('/cash/turn', 'CashRegisterController@close_turn');


/*************************/
/*** API STOCK ROUTES ***/
/***********************/
//GET
Route::middleware('auth:api')->get('/stock/brands', 'StockController@get_brands');
Route::middleware('auth:api')->get('/stock/colors', 'StockController@get_colors');
Route::middleware('auth:api')->get('/stock/categories', 'StockController@get_categories');
Route::middleware('auth:api')->get('/stock/numbers', 'StockController@get_numbers');
Route::middleware('auth:api')->get('/stock/articles', 'StockController@get_articles');
Route::middleware('auth:api')->get('/stock/articles/{id}', 'StockController@get_articles_id');
Route::middleware('auth:api')->get('/stock/detail_item', 'StockController@get_detail_item');
Route::middleware('auth:api')->get('/stock/detail_item_barcode', 'StockController@get_detail_item_barcode');
Route::middleware('auth:api')->get('/stock/list', 'StockController@get_list_stock');
Route::middleware('auth:api')->get('/stock/movements', 'StockController@get_movements');

//POST
//brands
Route::middleware('auth:api')->post('/stock/brands', 'StockController@new_brand');
Route::middleware('auth:api')->delete('/stock/brands', 'StockController@delete_brands');
Route::middleware('auth:api')->put('/stock/brands', 'StockController@edit_brand');
//colors
Route::middleware('auth:api')->post('/stock/colors', 'StockController@new_color');
Route::middleware('auth:api')->delete('/stock/colors', 'StockController@delete_colors');
Route::middleware('auth:api')->put('/stock/colors', 'StockController@edit_color');
//articles
Route::middleware('auth:api')->post('/stock/articles', 'StockController@new_article');
Route::middleware('auth:api')->delete('/stock/articles', 'StockController@delete_items');
Route::middleware('auth:api')->put('/stock/articles', 'StockController@update_items');
//movements
Route::middleware('auth:api')->post('/stock/movements', 'StockController@add_movements');
