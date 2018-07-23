<?php
/**
 * supplier 模块路由
 */
use Illuminate\Support\Facades\Route;

Route::post('/oper_account/add', 'OperAccountController@add');
Route::post('/oper_account/edit', 'OperAccountController@edit');