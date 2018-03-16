<?php
/**
 * supplier 模块路由
 */
use Illuminate\Support\Facades\Route;

Route::get('/demos', 'DemoController@getList');
Route::get('/demos/all', 'DemoController@getAllList');
Route::post('/demo/add', 'DemoController@add');
Route::post('/demo/edit', 'DemoController@edit');
Route::post('/demo/changeStatus', 'DemoController@changeStatus');
Route::post('/demo/del', 'DemoController@del');