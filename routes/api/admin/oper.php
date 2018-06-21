<?php
/**
 * supplier 模块路由
 */
use Illuminate\Support\Facades\Route;

Route::get('/opers', 'OperController@getList');
Route::get('/opers/all', 'OperController@getAllList');
Route::get('/oper/detail', 'OperController@detail');
Route::post('/oper/add', 'OperController@add');
Route::post('/oper/edit', 'OperController@edit');
Route::post('/oper/changeStatus', 'OperController@changeStatus');
Route::post('/oper/setPayToPlatformStatus', 'OperController@setPayToPlatformStatus');