<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
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
//POST
Route::post('/stock/new_brand', 'StockController@new_brand');
Route::post('/stock/new_article', 'StockController@new_article');
Route::post('/stock/new_color', 'StockController@new_color');
Route::post('/stock/update_items', 'StockController@update_items');