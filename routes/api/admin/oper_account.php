<?php
/**
 * supplier 模块路由
 */
use Illuminate\Support\Facades\Route;

Route::get('/oper_accounts', 'OperAccountController@getList');
Route::get('/oper_accounts/all', 'OperAccountController@getAllList');
Route::post('/oper_account/add', 'OperAccountController@add');
Route::post('/oper_account/edit', 'OperAccountController@edit');
Route::post('/oper_account/changeStatus', 'OperAccountController@changeStatus');
Route::post('/oper_account/del', 'OperAccountController@del');