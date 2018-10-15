<?php

/**
 * oper 模块路由
 */
use Illuminate\Support\Facades\Route;

Route::get('/opers', 'OperController@getList');
Route::get('/oper/name_list', 'OperController@getNameList');
Route::post('/oper/add', 'OperController@add');
