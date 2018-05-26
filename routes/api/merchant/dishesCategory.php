<?php
/**
 * supplier 模块路由
 */
use Illuminate\Support\Facades\Route;

Route::get('/dishesCategories', 'DishesCategoryController@getList');
Route::get('/dishesCategories/all', 'DishesCategoryController@getAllList');
Route::post('/dishes-category/add', 'DishesCategoryController@add');
Route::post('/dishes-category/edit', 'DishesCategoryController@edit');
Route::post('/dishes-category/changeStatus', 'DishesCategoryController@changeStatus');
Route::post('/dishes-category/del', 'DishesCategoryController@del');
Route::post('/dishes-category/saveOrder', 'DishesCategoryController@saveOrder');