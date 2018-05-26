<?php
/**
 * supplier 模块路由
 */
use Illuminate\Support\Facades\Route;

Route::get('/dishesGoods', 'DishesGoodsController@getList');
Route::get('/dishesGoods/all', 'DishesGoodsController@getAllList');
Route::post('/dishesGoods/add', 'DishesGoodsController@add');
Route::post('/dishesGoods/edit', 'DishesGoodsController@edit');
Route::post('/dishesGoods/changeStatus', 'DishesGoodsController@changeStatus');
Route::post('/dishesGoods/del', 'DishesGoodsController@del');