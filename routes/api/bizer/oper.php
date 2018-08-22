<?php
/**
 * supplier 模块路由
 */
use Illuminate\Support\Facades\Route;

Route::get('/opers', 'OperController@getList');
Route::post('/oper/add', 'OperController@add');