<?php
/**
 * Created by PhpStorm.
 * User: xianghua.zeng
 * Date: 2018/6/15
 * Time: 14:45
 */

/**
 * supplier 模块路由
 */
use Illuminate\Support\Facades\Route;

Route::get('/dishes/categories', 'DishesCategoryController@getList');
Route::get('/dishes/categories/all', 'DishesCategoryController@getAllList');
Route::post('/dishes/category/add', 'DishesCategoryController@add');
Route::post('/dishes/category/edit', 'DishesCategoryController@edit');
Route::post('/dishes/category/changeStatus', 'DishesCategoryController@changeStatus');
Route::post('/dishes/category/del', 'DishesCategoryController@del');
Route::post('/dishes/category/saveOrder', 'DishesCategoryController@saveOrder');


Route::get('/dishes/goods', 'DishesGoodsController@getList');
Route::post('/dishes/goods/add', 'DishesGoodsController@add');
Route::post('/dishes/goods/edit', 'DishesGoodsController@edit');
Route::post('/dishes/goods/changeStatus', 'DishesGoodsController@changeStatus');
Route::post('/dishes/goods/del', 'DishesGoodsController@del');
Route::post('/dishes/goods/saveOrder', 'DishesGoodsController@saveOrder');
