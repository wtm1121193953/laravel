<?php
/**
 * supplier 模块路由
 */
use Illuminate\Support\Facades\Route;

Route::get('/categories', 'CategoryController@getList');
Route::get('/categories/all', 'CategoryController@getAllList');
Route::post('/category/add', 'CategoryController@add');
Route::post('/category/edit', 'CategoryController@edit');
Route::post('/category/changeStatus', 'CategoryController@changeStatus');
Route::post('/category/del', 'CategoryController@del');