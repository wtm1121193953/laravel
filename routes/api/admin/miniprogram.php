<?php
/**
 * supplier 模块路由
 */
use Illuminate\Support\Facades\Route;

Route::post('/miniprogram/add', 'MiniprogramController@add');
Route::post('/miniprogram/edit', 'MiniprogramController@edit');
Route::post('/miniprogram/uploadCert', 'MiniprogramController@uploadCert');
Route::post('/miniprogram/uploadVerifyFile', 'MiniprogramController@uploadVerifyFile');