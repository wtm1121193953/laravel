<?php
/**
 * supplier 模块路由
 */
use Illuminate\Support\Facades\Route;

Route::get('/items', 'ItemController@getList');
Route::post('/item/add', 'ItemController@add');
Route::post('/item/edit', 'ItemController@edit');
Route::post('/item/changeStatus', 'ItemController@changeStatus');
Route::post('/item/del', 'ItemController@del');
Route::post('/item/changeLeftCount', 'ItemController@changeLeftCount');