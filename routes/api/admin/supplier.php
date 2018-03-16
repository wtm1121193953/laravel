<?php
/**
 * supplier 模块路由
 */
use Illuminate\Support\Facades\Route;

Route::get('/suppliers', 'SupplierController@getList');
Route::get('/suppliers/all', 'SupplierController@getAllList');
Route::post('/supplier/add', 'SupplierController@add');
Route::post('/supplier/edit', 'SupplierController@edit');
Route::post('/supplier/changeStatus', 'SupplierController@changeStatus');
Route::post('/supplier/del', 'SupplierController@del');