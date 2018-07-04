<?php
/**
 * supplier 模块路由
 */
use Illuminate\Support\Facades\Route;

Route::get('/goods', 'GoodsController@getList');
Route::get('/goods/all', 'GoodsController@getAllList');
Route::get('/goods/detail', 'GoodsController@detail');
Route::post('/goods/add', 'GoodsController@add');
Route::post('/goods/edit', 'GoodsController@edit');
Route::post('/goods/changeStatus', 'GoodsController@changeStatus');
Route::post('/goods/del', 'GoodsController@del');
Route::post('/goods/saveOrder', 'GoodsController@saveOrder');