<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Sucursal;
use App\ShoeDetail;
use App\ShoeSucursalItem;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

//TIENDANUBE
Route::post('/tiendanube/order/created', 'TiendaNubeController@order_created');
Route::post('/tiendanube/order/fullfilled', 'TiendaNubeController@order_fulfilled');
Route::post('/tiendanube/order/cancelled', 'TiendaNubeController@order_cancelled');
Route::post('/tiendanube/products/update', 'TiendaNubeController@update_stock_tiendanube');
Route::post('/tiendanube/products/map', 'TiendaNubeController@map_tiendanube_products');
Route::post('/tiendanube/borrar', 'TiendaNubeController@borrar');

Route::middleware('auth:api')->post('/pdf/generate','PdfController@generatePdf');

Route::middleware('auth:api')->get('/sucursals', function () {
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
Route::middleware('auth:api')->get('/order', 'OrderController@get_orders');
Route::middleware('auth:api')->get('/order/detail/{id}', 'OrderController@get_order');
Route::middleware('auth:api')->get('/order/payment_methods', 'OrderController@get_payment_methods');
Route::middleware('auth:api')->get('/order/payment_method_cards', 'OrderController@get_payment_method_cards');
Route::middleware('auth:api')->get('/order/payment_method_card_options', 'OrderController@get_payment_method_card_options');
Route::middleware('auth:api')->get('/order/marketplace/orders', 'OrderMarketplaceController@get_orders');
Route::middleware('auth:api')->get('/order/marketplace/detail/{id}', 'OrderMarketplaceController@get_order');

//POST
Route::middleware('auth:api')->post('/order', 'OrderController@new_order');
Route::middleware('auth:api')->post('/order/reset', 'OrderController@new_reset');
Route::middleware('auth:api')->post('/order/marketplace', 'OrderMarketplaceController@new_order');
Route::middleware('auth:api')->post('/order/marketplace/change', 'OrderMarketplaceController@new_item_change');
Route::middleware('auth:api')->delete('/order/marketplace', 'OrderMarketplaceController@delete_orders');

/*************************/
/* CASH REGISTER ROUTES */
/***********************/
//GET
Route::middleware('auth:api')->get('/cash/cash_register', 'CashRegisterController@get_cash_register');
Route::middleware('auth:api')->get('/cash/turn/cash', 'CashRegisterController@get_total_turn_cash');

//POST
Route::middleware('auth:api')->post('/cash/cash_register', 'CashRegisterController@new_cash_register');
Route::middleware('auth:api')->put('/cash/cash_register', 'CashRegisterController@close_cash_register');
Route::middleware('auth:api')->post('/cash/turn', 'CashRegisterController@new_turn');
Route::middleware('auth:api')->post('/cash/movement', 'CashRegisterController@new_movement');
Route::middleware('auth:api')->put('/cash/turn', 'CashRegisterController@close_turn');


/*************************/
/*** API STOCK ROUTES ***/
/***********************/
//GET
Route::middleware('auth:api')->get('/stock/brands', 'StockController@get_brands');
Route::middleware('auth:api')->get('/stock/colors', 'StockController@get_colors');
Route::middleware('auth:api')->get('/stock/categories', 'StockController@get_categories');
Route::middleware('auth:api')->get('/stock/numbers', 'StockController@get_numbers');
Route::middleware('auth:api')->get('/stock/article/items', 'StockController@get_article_items');
Route::middleware('auth:api')->get('/stock/articles', 'StockController@get_articles');
Route::middleware('auth:api')->get('/stock/articles/{id}', 'StockController@get_articles_id');
Route::middleware('auth:api')->get('/stock/single_detail_item', 'StockController@get_single_detail_item');
Route::middleware('auth:api')->get('/stock/detail_item', 'StockController@get_detail_item');
Route::middleware('auth:api')->get('/stock/detail_item_barcode', 'StockController@get_detail_item_barcode');
Route::middleware('auth:api')->get('/stock/list', 'StockController@get_list_stock');
Route::middleware('auth:api')->get('/stock/movements', 'StockMovementController@get_movements');
Route::middleware('auth:api')->get('/stock/movements/{id}', 'StockMovementController@get_movement');
Route::middleware('auth:api')->get('/stock/reset/stockInfo', 'StockResetController@get_stock_info');
Route::middleware('auth:api')->get('/stock/reset/shoeDetail', 'StockResetController@get_shoe_detail');
Route::middleware('auth:api')->get('/stock/reset/preview', 'StockResetController@get_preview');

//POST
Route::middleware('auth:api')->put('/stock/article/items', 'StockController@update_article_items');

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
Route::middleware('auth:api')->post('/stock/movements', 'StockMovementController@add_movements');
//reset
Route::middleware('auth:api')->put('/stock/reset', 'StockResetController@update_stock');
Route::middleware('auth:api')->post('/stock/reset/all', 'StockResetController@reset_all_stock');

